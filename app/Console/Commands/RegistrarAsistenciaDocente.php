<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\HorarioDocente;
use App\Models\AsistenciaDocente;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RegistrarAsistenciaDocente extends Command
{
    protected $signature = 'asistencia:registrar-docentes';
    protected $description = 'Registra asistencia de docentes a partir de registros del dispositivo (huella)';

    public function handle()
    {
        $now = Carbon::now();
        $fecha = $now->toDateString();
        $horaActual = $now->format('H:i:s');
        $dia = $now->locale('es')->isoFormat('dddd');

        $registros = DB::table('registros_asistencia')
            ->whereDate('fecha_hora', $fecha)
            ->get();

        $this->info("Procesando registros de asistencia del $fecha...");

        foreach ($registros as $registro) {
            $docente = User::where('nro_documento', $registro->nro_documento)
                ->whereHas('roles', fn($q) => $q->where('nombre', 'profesor'))
                ->first();

            if (!$docente) {
                $this->warn("No se encontró docente con DNI: {$registro->nro_documento}");
                continue;
            }

            $horaRegistro = Carbon::parse($registro->fecha_hora)->format('H:i:s');

            $horario = HorarioDocente::where('docente_id', $docente->id)
                ->where('dia_semana', ucfirst($dia))
                ->whereTime('hora_inicio', '<=', $horaRegistro)
                ->whereTime('hora_fin', '>=', $horaRegistro)
                ->first();

            if (!$horario) {
                $this->warn("El registro de {$docente->nombre_completo} no coincide con ningún horario.");
                continue;
            }

            $yaRegistrado = AsistenciaDocente::where('docente_id', $docente->id)
                ->whereDate('fecha', $fecha)
                ->whereTime('hora', $horaRegistro)
                ->exists();

            if (!$yaRegistrado) {
                AsistenciaDocente::create([
                    'docente_id' => $docente->id,
                    'horario_docente_id' => $horario->id,
                    'fecha' => $fecha,
                    'hora' => $horaRegistro,
                    'tipo_verificacion' => $registro->tipo_verificacion,
                    'estado' => $registro->estado,
                ]);

                $this->info("✔ Asistencia registrada: {$docente->nombre_completo} a las {$horaRegistro}");
            } else {
                $this->line("↪ Ya estaba registrada la asistencia de {$docente->nombre_completo} a las {$horaRegistro}");
            }
        }

        $this->info("✅ Registro de asistencia completado.");
    }
}
