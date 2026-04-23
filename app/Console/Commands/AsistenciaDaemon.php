<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class AsistenciaDaemon extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'asistencia:daemon {--sleep=2}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Procesa eventos de asistencia en un bucle de alta frecuencia para tiempo real instantáneo';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("Iniciando Daemon de Asistencia (Real-Time)...");
        $sleep = $this->option('sleep');

        while (true) {
            // Llamar al procesador de eventos existente
            Artisan::call('asistencia:procesar-eventos');
            
            // Opcionalmente procesar docentes si es necesario
            // Artisan::call('asistencia:procesar-docentes');

            if ($sleep > 0) {
                sleep($sleep);
            }
        }
    }
}
