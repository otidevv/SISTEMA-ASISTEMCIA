<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Console\Scheduling\Schedule;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->statefulApi();
        $middleware->validateCsrfTokens(except: [
            'api/*',
        ]);
    })
    ->withSchedule(function (Schedule $schedule) {
        // Procesar eventos de asistencia de estudiantes (cada minuto)
        $schedule->command('asistencia:procesar-eventos')
            ->withoutOverlapping()
            ->everyMinute();

        // Procesar eventos de asistencia de docentes (cada minuto)
        $schedule->command('asistencia:procesar-docentes')
            ->withoutOverlapping()
            ->everyMinute();

        // Recordatorio de temas pendientes y clases próximas (cada hora)
        $schedule->command('notification:remind-teachers')
            ->hourly();

        // Agenda diaria para docentes - 07:00 AM
        $schedule->command('asistencia:notificar-agenda')
            ->dailyAt('07:00');

        // Agenda del día siguiente - 08:00 PM
        $schedule->command('asistencia:notificar-agenda --tomorrow')
            ->dailyAt('20:00');
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
