<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

// Importar comandos personalizados
use App\Console\Commands\ProcesarEventosAsistencia;
use App\Console\Commands\ProcesarAsistenciaDocente;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Procesar eventos de estudiantes
        $schedule->command('asistencia:procesar-eventos')
            ->withoutOverlapping()
            ->everyMinute();

        // Procesar eventos de docentes
        $schedule->command('asistencia:procesar-docentes')
            ->withoutOverlapping()
            ->everyMinute();

        // Recordatorio dinámico de temas y próximos cursos (cada hora)
        $schedule->command('notification:remind-teachers')
            ->hourly();

        // Agenda Diaria (Hoy) - 07:00 AM
        $schedule->command('asistencia:notificar-agenda')
            ->dailyAt('07:00');

        // Agenda para Mañana (Noche anterior) - 08:00 PM
        $schedule->command('asistencia:notificar-agenda --tomorrow')
            ->dailyAt('20:00');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
