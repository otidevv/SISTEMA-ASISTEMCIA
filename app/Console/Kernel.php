<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

// Importar comandos personalizados
use App\Console\Commands\ProcesarEventosAsistencia;
use App\Console\Commands\RegistrarAsistenciaDocente;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Procesar eventos cada minuto (ejemplo previo)
        $schedule->command('asistencia:procesar-eventos')
            ->withoutOverlapping()
            ->everyMinute();

        // Puedes activar esto si deseas que el registro de asistencia docente sea automático
        // $schedule->command('asistencia:registrar-docentes')
        //     ->withoutOverlapping()
        //     ->everyFiveMinutes();
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
