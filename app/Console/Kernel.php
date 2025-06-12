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
