<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Postulacion;
use App\Models\Ciclo;

class CambiarEstadoPostulaciones extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'postulaciones:cambiar-estado 
                            {--de=aprobado : Estado actual de las postulaciones}
                            {--a=pendiente : Nuevo estado para las postulaciones}
                            {--ciclo=actual : ID del ciclo o "actual" para el ciclo activo}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cambiar el estado de postulaciones en lote';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $estadoActual = $this->option('de');
        $nuevoEstado = $this->option('a');
        $cicloOption = $this->option('ciclo');

        // Obtener ciclo
        if ($cicloOption === 'actual') {
            $ciclo = Ciclo::where('activo', true)->first();
            if (!$ciclo) {
                $this->error('No se encontró un ciclo activo.');
                return 1;
            }
        } else {
            $ciclo = Ciclo::find($cicloOption);
            if (!$ciclo) {
                $this->error("No se encontró el ciclo con ID: {$cicloOption}");
                return 1;
            }
        }

        $this->info("Ciclo seleccionado: {$ciclo->nombre} (ID: {$ciclo->id})");
        $this->info("Cambiando estado de '{$estadoActual}' a '{$nuevoEstado}'...");

        // Contar postulaciones a actualizar
        $count = Postulacion::where('ciclo_id', $ciclo->id)
            ->where('estado', $estadoActual)
            ->count();

        if ($count === 0) {
            $this->warn("No se encontraron postulaciones con estado '{$estadoActual}' en el ciclo '{$ciclo->nombre}'.");
            return 0;
        }

        $this->info("Se encontraron {$count} postulaciones para actualizar.");

        if (!$this->confirm('¿Desea continuar?')) {
            $this->info('Operación cancelada.');
            return 0;
        }

        // Actualizar postulaciones
        $updated = Postulacion::where('ciclo_id', $ciclo->id)
            ->where('estado', $estadoActual)
            ->update(['estado' => $nuevoEstado]);

        $this->info("✅ Se actualizaron {$updated} postulaciones exitosamente.");
        $this->info("Estado cambiado de '{$estadoActual}' a '{$nuevoEstado}'.");

        return 0;
    }
}
