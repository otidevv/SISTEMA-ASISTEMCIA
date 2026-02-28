<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\AsistenciaDocenteMail;
use App\Models\User;
use App\Models\HorarioDocente;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class MailNotificationController extends Controller
{
    /**
     * Disparar el envío de correo de asistencia para docentes
     */
    public function notificarAsistenciaDocente(Request $request)
    {
        try {
            $docenteId = $request->input('docente_id');
            $tipoAsistencia = $request->input('tipo_asistencia'); // entrada/salida
            $fechaHora = $request->input('fecha_hora');
            
            $docente = User::find($docenteId);
            if (!$docente || !$docente->email) {
                return response()->json(['success' => false, 'message' => 'Docente no encontrado o sin correo'], 404);
            }

            $carbonFecha = Carbon::parse($fechaHora);
            $diaSemanaNombre = $this->getDiaNombre($carbonFecha->dayOfWeek);
            
            $data = [
                'nombre_docente' => $docente->nombre . ' ' . $docente->apellido_paterno,
                'fecha' => $carbonFecha->format('d/m/Y'),
                'hora' => $carbonFecha->format('H:i:s'),
                'estado' => $tipoAsistencia,
            ];

            // Buscar horario asignado
            $horario = HorarioDocente::where('docente_id', $docenteId)
                ->where('dia_semana', $diaSemanaNombre)
                ->where(function ($q) use ($carbonFecha) {
                    $q->whereTime('hora_inicio', '<=', $carbonFecha->format('H:i:s'))
                      ->whereTime('hora_fin', '>=', $carbonFecha->format('H:i:s'));
                })
                ->orWhere(function ($q) use ($carbonFecha) {
                    // Tolerancia 15 min antes
                    $q->where('docente_id', $docenteId)
                      ->where('dia_semana', $this->getDiaNombre($carbonFecha->dayOfWeek))
                      ->whereTime('hora_inicio', '>=', $carbonFecha->copy()->subMinutes(15)->format('H:i:s'))
                      ->whereTime('hora_inicio', '<=', $carbonFecha->format('H:i:s'));
                })
                ->with(['curso', 'aula', 'ciclo'])
                ->first();

            if ($horario) {
                $data['curso'] = $horario->curso ? $horario->curso->nombre : 'N/A';
                $data['horario_programado'] = $horario->hora_inicio . ' - ' . $horario->hora_fin;
                $data['aula'] = $horario->aula ? $horario->aula->nombre : 'N/A';

                $horaInicioProg = Carbon::parse($carbonFecha->toDateString() . ' ' . $horario->hora_inicio);
                $horaFinProg = Carbon::parse($carbonFecha->toDateString() . ' ' . $horario->hora_fin);

                // --- LÓGICA DE TARDANZA (Sincronizada con AsistenciaDocenteController) ---
                if ($tipoAsistencia === 'entrada') {
                    $tardinessThreshold = $horaInicioProg->copy()->addMinutes(5); // TOLERANCIA_TARDE_MINUTOS = 5
                    
                    if ($carbonFecha->greaterThan($tardinessThreshold)) {
                        $data['minutos_tardanza'] = $carbonFecha->diffInMinutes($horaInicioProg);
                    }
                }

                // --- LÓGICA DE RECESOS Y HORAS (Solo para salidas) ---
                if ($tipoAsistencia === 'salida') {
                    // Buscar la entrada de hoy para este horario para calcular horas netas
                    $entrada = \App\Models\AsistenciaDocente::where('horario_id', $horario->id)
                        ->where('docente_id', $docenteId)
                        ->whereDate('fecha_hora', $carbonFecha->toDateString())
                        ->where('estado', 'entrada')
                        ->first();

                    if ($entrada) {
                        $entradaCarbon = Carbon::parse($entrada->fecha_hora);
                        $salidaCarbon = $carbonFecha;

                        // Inicio efectivo (respetando tolerancia)
                        $tardinessThreshold = $horaInicioProg->copy()->addMinutes(5);
                        $effectiveStartTime = $entradaCarbon->lte($tardinessThreshold) ? $horaInicioProg : $entradaCarbon;
                        
                        // Fin efectivo (máximo hasta el fin programado)
                        $finEfectivo = $salidaCarbon->min($horaFinProg);

                        if ($finEfectivo > $effectiveStartTime) {
                            $duracionBruta = $effectiveStartTime->diffInMinutes($finEfectivo);
                            
                            // Restar recesos del ciclo
                            $ciclo = $horario->ciclo;
                            $minutosReceso = 0;

                            foreach (['manana', 'tarde'] as $turno) {
                                $inicioField = "receso_{$turno}_inicio";
                                $finField = "receso_{$turno}_fin";
                                
                                if ($ciclo && $ciclo->$inicioField && $ciclo->$finField) {
                                    $recInicio = $carbonFecha->copy()->setTimeFromTimeString($ciclo->$inicioField);
                                    $recFin = $carbonFecha->copy()->setTimeFromTimeString($ciclo->$finField);
                                    
                                    if ($effectiveStartTime < $recFin && $finEfectivo > $recInicio) {
                                        $supInicio = $effectiveStartTime->max($recInicio);
                                        $supFin = $finEfectivo->min($recFin);
                                        if ($supFin > $supInicio) {
                                            $minutosReceso += $supInicio->diffInMinutes($supFin);
                                        }
                                    }
                                }
                            }

                            $minutosNetos = max(0, $duracionBruta - $minutosReceso);
                            $horasDictadas = $minutosNetos / 60;
                            
                            $h = floor($minutosNetos / 60);
                            $m = floor($minutosNetos % 60);
                            $data['duracion_neta'] = sprintf('%02d:%02d', $h, $m);
                            $data['horas_dictadas'] = round($horasDictadas, 2);
                        }
                    }
                }
            }

            Mail::to($docente->email)->send(new AsistenciaDocenteMail($data));

            return response()->json([
                'success' => true,
                'message' => 'Correo enviado correctamente a ' . $docente->email
            ]);

        } catch (\Exception $e) {
            Log::error('Error en notificarAsistenciaDocente: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al enviar correo: ' . $e->getMessage()
            ], 500);
        }
    }

    private function getDiaNombre($dayOfWeek)
    {
        $dias = [
            0 => 'Domingo',
            1 => 'Lunes',
            2 => 'Martes',
            3 => 'Miércoles',
            4 => 'Jueves',
            5 => 'Viernes',
            6 => 'Sábado'
        ];
        return $dias[$dayOfWeek];
    }
}
