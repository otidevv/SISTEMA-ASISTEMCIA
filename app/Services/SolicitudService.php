<?php

namespace App\Services;

use App\Models\Solicitud;
use App\Models\SolicitudTipo;
use App\Models\SolicitudHistorial;
use App\Models\SolicitudDerivacion;
use App\Models\SolicitudInasistencia;
use App\Models\User;
use App\Notifications\SolicitudNotification;
use Illuminate\Support\Facades\DB;

/**
 * Orquesta el ciclo de vida de una solicitud (Mesa de Partes):
 * crear → V°B° del Director → derivar a persona/rol → atender → entregar.
 * Escribe la bitácora y dispara notificaciones (push + campanita) en cada paso.
 */
class SolicitudService
{
    /**
     * Crea una solicitud. $opts puede incluir: ciclo_id, term_name, canal,
     * serial_voucher, pago_validado, monto, fecha_pago, fechas (justificación).
     */
    public function crear(SolicitudTipo $tipo, User $solicitante, array $datos, array $opts = []): Solicitud
    {
        return DB::transaction(function () use ($tipo, $solicitante, $datos, $opts) {
            // Solo avanza al flujo si el pago está VALIDADO (antifraude); un voucher sin validar queda pendiente.
            $tienePago = !empty($opts['pago_validado']);
            $estadoInicial = ($tipo->requiere_pago && !$tienePago)
                ? Solicitud::ESTADO_PENDIENTE_PAGO
                : Solicitud::ESTADO_ENVIADA;

            $solicitud = Solicitud::create([
                'codigo' => 'TMP-' . uniqid(),
                'user_id' => $solicitante->id,
                'numero_documento' => $solicitante->numero_documento,
                'solicitud_tipo_id' => $tipo->id,
                'ciclo_id' => $opts['ciclo_id'] ?? null,
                'term_name' => $opts['term_name'] ?? null,
                'estado' => $estadoInicial,
                'datos' => $datos,
                'serial_voucher' => $opts['serial_voucher'] ?? null,
                'pago_validado' => $opts['pago_validado'] ?? false,
                'monto' => $opts['monto'] ?? null,
                'fecha_pago' => $opts['fecha_pago'] ?? null,
                'canal' => $opts['canal'] ?? 'web',
            ]);

            // Código correlativo definitivo basado en el id
            $solicitud->update(['codigo' => $this->generarCodigo($solicitud)]);

            // Fechas a justificar (solo justificación de inasistencias)
            if (!empty($opts['fechas']) && is_array($opts['fechas'])) {
                foreach ($opts['fechas'] as $fecha) {
                    SolicitudInasistencia::create([
                        'solicitud_id' => $solicitud->id,
                        'numero_documento' => $solicitante->numero_documento,
                        'fecha' => $fecha,
                        'ciclo_id' => $opts['ciclo_id'] ?? null,
                        'justificada' => false,
                    ]);
                }
            }

            $this->historial($solicitud, null, $estadoInicial, 'Solicitud registrada', $solicitante->id);

            // Si ya entró al flujo (no requiere pago o ya pagó), avisar al Director
            if ($estadoInicial === Solicitud::ESTADO_ENVIADA) {
                $this->notificarDirectores($solicitud);
            }

            return $solicitud->fresh();
        });
    }

    /** Registra/valida el pago y, si estaba pendiente de pago, la envía al flujo. */
    public function registrarPago(Solicitud $solicitud, string $serial, ?float $monto = null, ?string $fechaPago = null): Solicitud
    {
        $solicitud->update([
            'serial_voucher' => $serial,
            'pago_validado' => true,
            'monto' => $monto,
            'fecha_pago' => $fechaPago,
        ]);

        if ($solicitud->estado === Solicitud::ESTADO_PENDIENTE_PAGO) {
            $this->transicion($solicitud, Solicitud::ESTADO_ENVIADA, 'Pago validado', null);
            $this->notificarDirectores($solicitud);
        }

        return $solicitud->fresh();
    }

    /**
     * Valida un voucher contra la API de pagos: que exista para el DNI, corresponda al
     * código del trámite (TUSNE) y esté pagado (status 2). Resiliente: si la API no responde,
     * devuelve ok=false con mensaje (no lanza excepción).
     */
    public function validarVoucher(string $dni, string $serial, ?string $codigoTusne = null): array
    {
        try {
            $vouchers = app(\App\Services\PaymentValidationService::class)->validateVoucher($dni, $serial);
        } catch (\Throwable $e) {
            return ['ok' => false, 'mensaje' => 'No se pudo conectar con el sistema de pagos.', 'monto' => null, 'fecha' => null];
        }

        if (empty($vouchers)) {
            return ['ok' => false, 'mensaje' => 'No se encontraron pagos para el DNI ' . $dni . '.', 'monto' => null, 'fecha' => null];
        }

        foreach ($vouchers as $v) {
            $vSerial = $v['serial_voucher'] ?? $v['serial'] ?? null;
            if ($vSerial !== $serial) {
                continue;
            }
            foreach (($v['items'] ?? []) as $it) {
                $desc = $it['descripcion'] ?? $it['description'] ?? '';
                $status = (int) ($it['status'] ?? 0);
                if ($codigoTusne && !str_contains($desc, (string) $codigoTusne)) {
                    continue;
                }
                if ($status === 2) {
                    return [
                        'ok' => true,
                        'mensaje' => 'Pago validado.',
                        'monto' => (float) ($it['total'] ?? $v['monto_total'] ?? 0),
                        'fecha' => $it['paymentDate'] ?? $v['fecha'] ?? null,
                    ];
                }
            }
            return ['ok' => false, 'mensaje' => 'El voucher no corresponde a este trámite o no está pagado.', 'monto' => null, 'fecha' => null];
        }

        return ['ok' => false, 'mensaje' => 'No se encontró el voucher ' . $serial . ' para ese DNI.', 'monto' => null, 'fecha' => null];
    }

    /**
     * V°B° del Director + derivación a una persona y/o rol.
     * $deriv: ['user_destino_id', 'rol_destino_id', 'accion', 'observacion'].
     */
    public function darVistoBueno(Solicitud $solicitud, User $director, array $deriv): Solicitud
    {
        return DB::transaction(function () use ($solicitud, $director, $deriv) {
            $anterior = $solicitud->estado;

            $solicitud->update([
                'vb_director_por' => $director->id,
                'vb_director_at' => now(),
                'estado' => Solicitud::ESTADO_DERIVADA,
                'user_actual_id' => $deriv['user_destino_id'] ?? null,
                'rol_actual_id' => $deriv['rol_destino_id'] ?? null,
            ]);

            SolicitudDerivacion::create([
                'solicitud_id' => $solicitud->id,
                'de_user_id' => $director->id,
                'rol_destino_id' => $deriv['rol_destino_id'] ?? null,
                'user_destino_id' => $deriv['user_destino_id'] ?? null,
                'accion' => $deriv['accion'] ?? 'atencion',
                'observacion' => $deriv['observacion'] ?? null,
            ]);

            $this->historial($solicitud, $anterior, Solicitud::ESTADO_DERIVADA, 'V°B° del Director y derivación', $director->id);

            $this->notificarDerivado($solicitud);
            $this->notificarSolicitante($solicitud, 'Tu trámite tiene Visto Bueno',
                'El Director dio el V°B° y tu trámite fue derivado para atención.');

            return $solicitud->fresh();
        });
    }

    /** Re-derivar a otra persona/rol (sin volver a pedir V°B°). */
    public function derivar(Solicitud $solicitud, User $quien, array $deriv): Solicitud
    {
        return DB::transaction(function () use ($solicitud, $quien, $deriv) {
            $solicitud->update([
                'estado' => Solicitud::ESTADO_DERIVADA,
                'user_actual_id' => $deriv['user_destino_id'] ?? null,
                'rol_actual_id' => $deriv['rol_destino_id'] ?? null,
            ]);

            SolicitudDerivacion::create([
                'solicitud_id' => $solicitud->id,
                'de_user_id' => $quien->id,
                'rol_destino_id' => $deriv['rol_destino_id'] ?? null,
                'user_destino_id' => $deriv['user_destino_id'] ?? null,
                'accion' => $deriv['accion'] ?? 'atencion',
                'observacion' => $deriv['observacion'] ?? null,
            ]);

            $this->historial($solicitud, Solicitud::ESTADO_DERIVADA, Solicitud::ESTADO_DERIVADA, 'Derivación', $quien->id);
            $this->notificarDerivado($solicitud);

            return $solicitud->fresh();
        });
    }

    /** Observar (devolver para corrección). */
    public function observar(Solicitud $solicitud, User $quien, string $observacion): Solicitud
    {
        $this->transicion($solicitud, Solicitud::ESTADO_OBSERVADA, $observacion, $quien->id);
        $solicitud->update(['observacion' => $observacion]);
        $this->notificarSolicitante($solicitud, 'Tu trámite tiene observaciones',
            'Revisa las observaciones y corrige para continuar: ' . $observacion);
        return $solicitud->fresh();
    }

    /** Rechazar. */
    public function rechazar(Solicitud $solicitud, User $quien, string $motivo): Solicitud
    {
        $this->transicion($solicitud, Solicitud::ESTADO_RECHAZADA, $motivo, $quien->id);
        $solicitud->update(['observacion' => $motivo]);
        $this->notificarSolicitante($solicitud, 'Tu trámite fue rechazado', $motivo);
        return $solicitud->fresh();
    }

    /** Atender (marcar resuelto). Para justificaciones, regulariza las inasistencias. */
    public function atender(Solicitud $solicitud, User $quien, ?string $comentario = null): Solicitud
    {
        return DB::transaction(function () use ($solicitud, $quien, $comentario) {
            $anterior = $solicitud->estado;

            $solicitud->update([
                'estado' => Solicitud::ESTADO_ATENDIDA,
                'atendido_por' => $quien->id,
                'fecha_atencion' => now(),
            ]);

            // Marcar como atendida la derivación vigente
            SolicitudDerivacion::where('solicitud_id', $solicitud->id)
                ->where('atendida', false)
                ->update(['atendida' => true]);

            // Auto-regularización: si es justificación, marcar las fechas como justificadas
            if (optional($solicitud->tipo)->esJustificacion()) {
                $this->regularizarInasistencias($solicitud);
            }

            $this->historial($solicitud, $anterior, Solicitud::ESTADO_ATENDIDA, $comentario ?? 'Atendida', $quien->id);
            $this->notificarSolicitante($solicitud, 'Tu trámite fue atendido',
                'Tu trámite ha sido atendido. Revisa el resultado en tu expediente.');

            return $solicitud->fresh();
        });
    }

    /** Marca las inasistencias de la solicitud como justificadas (regularización automática). */
    public function regularizarInasistencias(Solicitud $solicitud): void
    {
        SolicitudInasistencia::where('solicitud_id', $solicitud->id)
            ->update(['justificada' => true]);
    }

    // ─────────────────────────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────────────────────────

    private function transicion(Solicitud $solicitud, string $nuevoEstado, ?string $comentario, ?int $userId): void
    {
        $anterior = $solicitud->estado;
        $solicitud->update(['estado' => $nuevoEstado]);
        $this->historial($solicitud, $anterior, $nuevoEstado, $comentario, $userId);
    }

    private function historial(Solicitud $solicitud, ?string $anterior, string $nuevo, ?string $comentario, ?int $userId): void
    {
        SolicitudHistorial::create([
            'solicitud_id' => $solicitud->id,
            'estado_anterior' => $anterior,
            'estado_nuevo' => $nuevo,
            'comentario' => $comentario,
            'user_id' => $userId,
        ]);
    }

    private function generarCodigo(Solicitud $solicitud): string
    {
        return 'SOL-' . now()->year . '-' . str_pad((string) $solicitud->id, 6, '0', STR_PAD_LEFT);
    }

    /** Notifica a quienes pueden dar V°B° (permiso solicitudes.approve). */
    private function notificarDirectores(Solicitud $solicitud): void
    {
        $this->usuariosConPermiso('solicitudes.approve')->each(function (User $u) use ($solicitud) {
            $this->enviar($u, new SolicitudNotification(
                'Nueva solicitud para V°B°',
                "Trámite {$solicitud->codigo} espera tu Visto Bueno.",
                $solicitud
            ));
        });
    }

    /** Notifica a la persona y/o rol al que se derivó. */
    private function notificarDerivado(Solicitud $solicitud): void
    {
        $destinatarios = collect();

        if ($solicitud->user_actual_id) {
            $destinatarios = $destinatarios->merge(User::where('id', $solicitud->user_actual_id)->get());
        } elseif ($solicitud->rol_actual_id) {
            $destinatarios = $destinatarios->merge($this->usuariosPorRol($solicitud->rol_actual_id));
        }

        $destinatarios->unique('id')->each(function (User $u) use ($solicitud) {
            $this->enviar($u, new SolicitudNotification(
                'Trámite derivado a ti',
                "Se te derivó el trámite {$solicitud->codigo} para atención.",
                $solicitud
            ));
        });
    }

    private function notificarSolicitante(Solicitud $solicitud, string $titulo, string $mensaje): void
    {
        $solicitante = $solicitud->estudiante;
        if ($solicitante) {
            $this->enviar($solicitante, new SolicitudNotification($titulo, $mensaje, $solicitud));
        }
    }

    /** Envía una notificación de forma resiliente: si el push (FCM) falla, no rompe el flujo. */
    private function enviar($notifiable, SolicitudNotification $notification): void
    {
        try {
            $notifiable->notify($notification);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Notificación de solicitud falló: ' . $e->getMessage());
        }
    }

    private function usuariosConPermiso(string $codigo)
    {
        $userIds = DB::table('user_roles as ur')
            ->join('role_permissions as rp', 'rp.rol_id', '=', 'ur.rol_id')
            ->join('permissions as p', 'p.id', '=', 'rp.permiso_id')
            ->where('p.codigo', $codigo)
            ->pluck('ur.usuario_id')
            ->unique();

        return User::whereIn('id', $userIds)->get();
    }

    private function usuariosPorRol(int $rolId)
    {
        $userIds = DB::table('user_roles')->where('rol_id', $rolId)->pluck('usuario_id');
        return User::whereIn('id', $userIds)->get();
    }
}
