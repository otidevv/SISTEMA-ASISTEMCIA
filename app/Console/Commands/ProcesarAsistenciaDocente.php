<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\AsistenciaEvento;
use App\Models\AsistenciaDocente;
use App\Models\HorarioDocente;
use App\Models\RegistroAsistencia;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ProcesarAsistenciaDocente extends Command
{
    protected $signature = 'asistencia:procesar-docentes';
    protected $description = 'Procesar eventos de asistencia docente desde registros_asistencia';

    public function handle()
    {
        Carbon::setLocale('es'); // Asegura que los días estén en español

        $eventos = AsistenciaEvento::where('procesado', false)
            ->orderBy('id', 'asc')
            ->take(50)
            ->get();

        $this->info("Procesando {$eventos->count()} eventos...");

        foreach ($eventos as $evento) {
            try {
                $registro = RegistroAsistencia::with('usuario')->find($evento->registros_asistencia_id);

                if (!$registro || !$registro->usuario) {
                    $this->warn("Registro ID {$evento->registros_asistencia_id} no encontrado.");
                    $evento->update(['procesado' => true]);
                    continue;
                }

                $usuario = $registro->usuario;

                if (!$usuario->hasRole('profesor')) {
                    $this->info("Usuario {$usuario->nombre} no es docente, se ignora.");
                    $evento->update(['procesado' => true]);
                    continue;
                }

                $fechaHora = Carbon::parse($registro->fecha_hora);
                $diaSemana = strtolower($fechaHora->translatedFormat('l'));

                $this->procesarMarcacion($usuario, $registro, $fechaHora, $diaSemana);

            } catch (\Exception $e) {
                $this->error("Error procesando evento ID {$evento->id}: {$e->getMessage()}");
                Log::error("Error en asistencia docente", [
                    'evento_id' => $evento->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        }

        return Command::SUCCESS;
    }

    private function procesarMarcacion(User $usuario, RegistroAsistencia $registro, Carbon $fechaHora, string $diaSemana)
    {
        $diaOriginal = $diaSemana;
        
        // NUEVO: Si es sábado, obtener día equivalente según rotación del ciclo
        if ($diaSemana === 'sábado') {
            $cicloActivo = \App\Models\Ciclo::where('es_activo', true)->first();
            
            if (!$cicloActivo) {
                $this->warn("No hay ciclo activo. No se puede procesar sábado.");
                $registro->evento()->update(['procesado' => true]);
                return;
            }
            
            $diaSemana = $cicloActivo->getDiaHorarioParaFecha($fechaHora);
            $semana = $cicloActivo->getNumeroSemana($fechaHora);
            
            $this->info("Sábado detectado (Semana {$semana}). Usando horario de: " . ucfirst($diaSemana));
        }
        
        $horariosDelDia = HorarioDocente::where('docente_id', $usuario->id)
            ->where('dia_semana', $diaSemana)
            ->orderBy('hora_inicio', 'asc')
            ->get();

        if ($horariosDelDia->isEmpty()) {
            $this->warn("No se encontró ningún horario para el docente {$usuario->id} el {$diaSemana}. Se ignora la marcación.");
            $registro->evento()->update(['procesado' => true]);
            return;
        }


        // Caso especial: si la marcación es EXACTAMENTE en el límite de dos clases consecutivas
        $horariosEnLimite = $horariosDelDia->filter(function ($h) use ($fechaHora) {
            return Carbon::parse($h->hora_fin)->format('H:i:s') === $fechaHora->format('H:i:s') || Carbon::parse($h->hora_inicio)->format('H:i:s') === $fechaHora->format('H:i:s');
        });

        if ($horariosEnLimite->count() === 2) {
            $h1 = $horariosEnLimite->values()->get(0);
            $h2 = $horariosEnLimite->values()->get(1);
            if (Carbon::parse($h1->hora_fin)->eq(Carbon::parse($h2->hora_inicio))) {
                $this->info("Límite exacto de horario consecutivo detectado.");
                $this->crearAsistencia($usuario, $h1, $registro, 'salida');
                $this->crearAsistencia($usuario, $h2, $registro, 'entrada');
                $registro->evento()->update(['procesado' => true]);
                return;
            }
        }

        // Lógica general: encontrar el horario más cercano
        $candidatos = [];
        foreach ($horariosDelDia as $horario) {
            $diffInicio = abs($fechaHora->diffInMinutes(Carbon::parse($horario->hora_inicio)));
            $diffFin = abs($fechaHora->diffInMinutes(Carbon::parse($horario->hora_fin)));
            $candidatos[] = [
                'horario' => $horario,
                'min_diff' => min($diffInicio, $diffFin)
            ];
        }

        // Ordenar por la diferencia mínima
        usort($candidatos, fn($a, $b) => $a['min_diff'] <=> $b['min_diff']);
        
        $mejorCandidato = $candidatos[0];
        $menorDiferencia = $mejorCandidato['min_diff'];

        // Filtrar todos los candidatos que comparten la menor diferencia (casos de empate)
        $empates = array_filter($candidatos, fn($c) => $c['min_diff'] === $menorDiferencia);

        $horarioFinal = null;
        if (count($empates) > 1) {
            $this->info("Empate detectado. Resolviendo por contexto...");
            // Lógica para resolver empates: verificar si ya hay una entrada para el primer horario
            $primerHorarioDelEmpate = $empates[0]['horario'];
            $entradaExiste = AsistenciaDocente::where('docente_id', $usuario->id)
                ->where('horario_id', $primerHorarioDelEmpate->id)
                ->whereDate('fecha_hora', $fechaHora->toDateString())
                ->where('estado', 'entrada')
                ->exists();

            if ($entradaExiste) {
                // Si ya entró a la primera clase, esta marcación es probablemente su salida
                $horarioFinal = $primerHorarioDelEmpate;
            } else {
                // Si no ha entrado a la primera, esta debe ser la entrada de la segunda
                $horarioFinal = $empates[1]['horario'];
            }
        } else {
            $horarioFinal = $mejorCandidato['horario'];
        }

        // Procesar solo si la marcación está dentro de una ventana de tolerancia (ej. 60 minutos)
        if ($menorDiferencia <= 60) {
            $diffInicio = abs($fechaHora->diffInMinutes(Carbon::parse($horarioFinal->hora_inicio)));
            $diffFin = abs($fechaHora->diffInMinutes(Carbon::parse($horarioFinal->hora_fin)));
            $estado = $diffInicio <= $diffFin ? 'entrada' : 'salida';

            $this->crearAsistencia($usuario, $horarioFinal, $registro, $estado);
        } else {
            $this->warn("Marcación a las {$fechaHora->format('H:i')} ignorada por estar fuera de la ventana de tolerancia.");
        }

        $registro->evento()->update(['procesado' => true]);
    }

    private function crearAsistencia(User $usuario, HorarioDocente $horario, RegistroAsistencia $registro, string $estado): void
    {
        $fechaHora = Carbon::parse($registro->fecha_hora);

        $yaExiste = AsistenciaDocente::where('docente_id', $usuario->id)
            ->where('horario_id', $horario->id)
            ->where('estado', $estado)
            ->whereDate('fecha_hora', $fechaHora->toDateString())
            ->exists();

        if ($yaExiste) {
            $this->info("Asistencia ({$estado}) ya registrada para docente {$usuario->id} en horario {$horario->id} en esta fecha.");
            return;
        }

        $asistencia = AsistenciaDocente::create([
            'docente_id' => $usuario->id,
            'horario_id' => $horario->id,
            'fecha_hora' => $fechaHora,
            'estado' => $estado,
            'tipo_verificacion' => $registro->tipo_verificacion,
            'terminal_id' => $registro->terminal_id,
            'codigo_trabajo' => $registro->codigo_trabajo,
            'tema_desarrollado' => null,
            'curso_id' => $horario->curso_id,
            'aula_id' => $horario->aula_id,
            'turno' => $horario->turno,
        ]);

        // Si es salida, verificar si el tema ya fue registrado en la entrada
        if ($estado === 'salida') {
            $temaRegistrado = AsistenciaDocente::where('docente_id', $usuario->id)
                ->where('horario_id', $horario->id)
                ->where('estado', 'entrada')
                ->whereDate('fecha_hora', $fechaHora->toDateString())
                ->whereNotNull('tema_desarrollado')
                ->exists();

            if (!$temaRegistrado) {
                $usuario->notify(new \App\Notifications\SesionPendienteTemaNotification($horario));
                $this->info("Notificación de tema pendiente enviada al docente {$usuario->id}.");
            }
        }

        $this->info("Asistencia ({$estado}) creada para docente {$usuario->id} en horario {$horario->id}.");
    }
}
