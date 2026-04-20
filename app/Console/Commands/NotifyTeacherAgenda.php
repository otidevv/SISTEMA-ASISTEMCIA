<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Ciclo;
use App\Models\HorarioDocente;
use App\Notifications\TeacherAgenda;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class NotifyTeacherAgenda extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'asistencia:notificar-agenda {--tomorrow : Enviar la agenda del día siguiente}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Enviar resumen de clases a docentes (Hoy o Mañana)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $isTomorrow = $this->option('tomorrow');
        $targetDate = $isTomorrow ? Carbon::tomorrow() : Carbon::today();
        $this->info("Procesando agenda para: " . $targetDate->toDateString());

        // 1. Ciclo Activo
        $cicloActivo = Ciclo::where('es_activo', true)->first();
        if (!$cicloActivo) {
            $this->error("No hay ciclo activo.");
            return Command::FAILURE;
        }

        // 2. Determinar día de horario según rotación
        $diaHorario = $cicloActivo->getDiaHorarioParaFecha($targetDate);
        if (!$diaHorario) {
            $this->info("No hay actividades programadas para este día.");
            return Command::SUCCESS;
        }

        // 3. Obtener todos los horarios para ese día
        $horarios = HorarioDocente::with(['docente', 'curso'])
            ->where('ciclo_id', $cicloActivo->id)
            ->where('dia_semana', $diaHorario)
            ->orderBy('hora_inicio')
            ->get();

        // 4. Agrupar por docente
        $agrupadoPorDocente = $horarios->groupBy('docente_id');
        $this->info("Notificando a " . $agrupadoPorDocente->count() . " docentes.");

        foreach ($agrupadoPorDocente as $docenteId => $clasesDocente) {
            $docente = $clasesDocente->first()->docente;
            
            if ($docente && $docente->fcm_token) {
                // Preparar lista legible
                $clasesData = $clasesDocente->map(function($h) {
                    return [
                        'curso' => $h->curso->nombre ?? 'Sin nombre',
                        'hora' => Carbon::parse($h->hora_inicio)->format('H:i'),
                        'aula' => $h->aula->nombre ?? 'N/A'
                    ];
                })->toArray();

                try {
                    $docente->notify(new TeacherAgenda($clasesData, $isTomorrow));
                    $this->info("Agenda enviada a: {$docente->nombre}");
                } catch (\Exception $e) {
                    Log::error("Error enviando agenda a docente {$docenteId}: " . $e->getMessage());
                }
            }
        }

        return Command::SUCCESS;
    }
}
