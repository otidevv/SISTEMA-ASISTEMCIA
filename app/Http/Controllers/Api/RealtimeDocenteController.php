<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController;
use Illuminate\Http\Request;
use App\Models\AsistenciaDocente;
use App\Models\HorarioDocente;
use App\Models\User;
use App\Models\Ciclo;
use App\Services\FcmService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class RealtimeDocenteController extends BaseController
{
    protected $fcmService;

    public function __construct(FcmService $fcmService)
    {
        $this->fcmService = $fcmService;
    }

    /**
     * Obtener el estado de monitoreo de docentes en tiempo real para el día de hoy
     */
    public function getRealtimeStatus(Request $request)
    {
        try {
            $hoy = Carbon::today();
            $hoyString = $hoy->toDateString();
            $ahora = Carbon::now();
            $horaActualString = $ahora->format('H:i:s');

            // 1. Obtener ciclo activo
            $cicloActivo = Ciclo::where('es_activo', true)
                ->orderBy('programa_id', 'asc') // Priorizar CEPRE
                ->first();

            if (!$cicloActivo) {
                return $this->sendError('No hay un ciclo académico activo programado.');
            }

            // 2. Obtener horarios del día actual para el ciclo activo
            $diaSemana = $cicloActivo->getDiaHorarioParaFecha($hoy);
            if (!$diaSemana) {
                $diaSemana = $hoy->locale('es')->dayName;
            }

            $horariosHoy = HorarioDocente::with(['docente', 'curso', 'aula', 'ciclo'])
                ->where('ciclo_id', $cicloActivo->id)
                ->where('dia_semana', $diaSemana)
                ->get()
                ->sortBy('hora_inicio');

            // 3. Obtener todas las asistencias de docentes registradas hoy
            $asistenciasHoy = AsistenciaDocente::whereDate('fecha_hora', $hoyString)
                ->get()
                ->groupBy('horario_id');

            $dictandoAhora = [];
            $ausentesRetrasados = [];
            $proximasSesiones = [];
            $finalizados = [];

            foreach ($horariosHoy as $horario) {
                $horaInicio = Carbon::parse($hoyString . ' ' . $horario->hora_inicio);
                $horaFin = Carbon::parse($hoyString . ' ' . $horario->hora_fin);

                // Buscar marcaciones de entrada y salida para este horario
                $asistenciaHorario = $asistenciasHoy->get($horario->id, collect());
                $asistenciaEntrada = $asistenciaHorario->firstWhere('estado', 'entrada');
                $asistenciaSalida = $asistenciaHorario->firstWhere('estado', 'salida');

                // Determinar estado
                $estado = 'pendiente';
                $estadoTexto = 'Pendiente';
                $tiempoDetalle = null;

                if ($ahora->between($horaInicio, $horaFin)) {
                    // La clase debería estar dictándose ahora
                    if ($asistenciaEntrada) {
                        $estado = 'dictando';
                        $estadoTexto = 'Dictando Ahora';
                        $minutosTranscurridos = $ahora->diffInMinutes($horaInicio);
                        $tiempoDetalle = "Hace {$minutosTranscurridos} min";
                        
                        $dictandoAhora[] = $this->formatRealtimeItem($horario, $asistenciaEntrada, $asistenciaSalida, $estado, $estadoTexto, $tiempoDetalle);
                    } else {
                        $estado = 'ausente';
                        $estadoTexto = 'Ausente / Retrasado';
                        $minutosRetraso = $ahora->diffInMinutes($horaInicio);
                        $tiempoDetalle = "Retraso de {$minutosRetraso} min";

                        $ausentesRetrasados[] = $this->formatRealtimeItem($horario, null, null, $estado, $estadoTexto, $tiempoDetalle);
                    }
                } elseif ($ahora->lessThan($horaInicio)) {
                    // La clase es más tarde
                    $estado = 'pendiente';
                    $estadoTexto = 'Próximo bloque';
                    $minutosParaIniciar = $horaInicio->diffInMinutes($ahora);
                    $tiempoDetalle = "Inicia en {$minutosParaIniciar} min";

                    $proximasSesiones[] = $this->formatRealtimeItem($horario, null, null, $estado, $estadoTexto, $tiempoDetalle);
                } else {
                    // La clase ya finalizó
                    $estado = 'finalizado';
                    if ($asistenciaEntrada && $asistenciaSalida) {
                        $estadoTexto = $asistenciaSalida->tema_desarrollado ? 'Completo' : 'Tema Pendiente';
                    } elseif ($asistenciaEntrada) {
                        $estadoTexto = 'Sin Salida Registrada';
                    } else {
                        $estadoTexto = 'Falta Injustificada';
                    }
                    $tiempoDetalle = "Terminó a las " . Carbon::parse($horario->hora_fin)->format('H:i');

                    $finalizados[] = $this->formatRealtimeItem($horario, $asistenciaEntrada, $asistenciaSalida, $estado, $estadoTexto, $tiempoDetalle);
                }
            }

            $data = [
                'ciclo_activo' => $cicloActivo->nombre,
                'fecha' => $hoyString,
                'hora_actual' => $horaActualString,
                'conteos' => [
                    'dictando' => count($dictandoAhora),
                    'ausentes' => count($ausentesRetrasados),
                    'pendientes' => count($proximasSesiones),
                    'finalizados' => count($finalizados),
                    'total_programado' => $horariosHoy->count()
                ],
                'dictando' => $dictandoAhora,
                'ausentes' => $ausentesRetrasados,
                'pendientes' => $proximasSesiones,
                'finalizados' => $finalizados,
            ];

            return $this->sendResponse($data, 'Monitoreo en tiempo real obtenido.');
        } catch (\Exception $e) {
            Log::error('RealtimeDocenteController@getRealtimeStatus error: ' . $e->getMessage());
            return $this->sendError('Error al recuperar estado en tiempo real: ' . $e->getMessage());
        }
    }

    /**
     * Dar formato a un elemento de monitoreo para la respuesta JSON
     */
    private function formatRealtimeItem($horario, $entrada, $salida, $estado, $estadoTexto, $tiempoDetalle)
    {
        return [
            'horario_id' => $horario->id,
            'docente_id' => $horario->docente_id,
            'docente_nombre' => $horario->docente ? trim("{$horario->docente->nombre} {$horario->docente->apellido_paterno} {$horario->docente->apellido_materno}") : 'Sin Docente',
            'docente_telefono' => $horario->docente?->telefono,
            'docente_foto' => $horario->docente?->foto_perfil_url, // si existe
            'curso' => $horario->curso?->nombre ?? 'Sin Curso',
            'aula' => $horario->aula?->nombre ?? 'Sin Aula',
            'hora_inicio' => Carbon::parse($horario->hora_inicio)->format('H:i'),
            'hora_fin' => Carbon::parse($horario->hora_fin)->format('H:i'),
            'hora_entrada' => $entrada ? Carbon::parse($entrada->fecha_hora)->format('H:i') : null,
            'hora_salida' => $salida ? Carbon::parse($salida->fecha_hora)->format('H:i') : null,
            'estado' => $estado,
            'estado_texto' => $estadoTexto,
            'tiempo_detalle' => $tiempoDetalle,
            'tema_desarrollado' => $salida?->tema_desarrollado,
        ];
    }

    /**
     * Enviar alerta manual al docente para que registre su ingreso
     */
    public function enviarAlertaDocente(Request $request)
    {
        $request->validate([
            'docente_id' => 'required|exists:users,id',
            'horario_id' => 'required|exists:horarios_docentes,id',
        ]);

        try {
            $docente = User::find($request->docente_id);
            $horario = HorarioDocente::with(['curso', 'aula'])->find($request->horario_id);

            if (!$docente->fcm_token) {
                return $this->sendError('El docente no tiene configurado un dispositivo para notificaciones push.');
            }

            $title = "⚠️ Registro de Asistencia Requerido";
            $body = "Hola {$docente->nombre}, tu clase de {$horario->curso?->nombre} en el aula {$horario->aula?->nombre} ya inició. Por favor registra tu ingreso.";
            
            $data = [
                'type' => 'attendance_alert',
                'horario_id' => (string)$horario->id,
            ];

            $res = $this->fcmService->sendNotification($docente->fcm_token, $title, $body, $data);

            if ($res) {
                return $this->sendResponse(null, 'Alerta push enviada al docente exitosamente.');
            }

            return $this->sendError('No se pudo enviar la alerta push al docente.');
        } catch (\Exception $e) {
            return $this->sendError('Error al enviar alerta: ' . $e->getMessage());
        }
    }

    /**
     * Enviar notificación manual a todos los administradores (usado para pruebas o avisos manuales)
     */
    public function enviarAlertaPushAdmin(Request $request)
    {
        $request->validate([
            'title' => 'required|string',
            'body' => 'required|string',
        ]);

        try {
            $admins = User::whereHas('roles', function ($q) {
                $q->where('nombre', 'admin');
            })->whereNotNull('fcm_token')->get();

            $enviadas = 0;
            foreach ($admins as $admin) {
                $res = $this->fcmService->sendNotification(
                    $admin->fcm_token,
                    $request->title,
                    $request->body,
                    ['type' => 'admin_general']
                );
                if ($res) $enviadas++;
            }

            return $this->sendResponse(['enviadas' => $enviadas], "Alerta enviada a {$enviadas} administradores.");
        } catch (\Exception $e) {
            return $this->sendError('Error al enviar alerta a administradores: ' . $e->getMessage());
        }
    }
}
