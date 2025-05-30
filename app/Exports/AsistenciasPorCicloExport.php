<?php

namespace App\Exports;

use App\Models\Inscripcion;
use App\Models\RegistroAsistencia;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithStyles; // AGREGAR ESTA LÍNEA
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet; // AGREGAR ESTA LÍNEA
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AsistenciasPorCicloExport implements FromView, WithStyles // AGREGAR WithStyles AQUÍ
{
    protected $cicloId;

    public function __construct($cicloId)
    {
        $this->cicloId = $cicloId;
    }

    public function view(): View
    {
        $inscripciones = Inscripcion::with(['estudiante', 'aula', 'ciclo', 'carrera', 'turno'])
            ->where('ciclo_id', $this->cicloId)
            ->where('estado_inscripcion', 'activo')
            ->get()
            ->map(function ($inscripcion) {
                $estudiante = $inscripcion->estudiante;
                $ciclo = $inscripcion->ciclo;

                // Obtener el primer registro de asistencia
                $primerRegistro = RegistroAsistencia::where('nro_documento', $estudiante->numero_documento)
                    ->whereBetween('fecha_registro', [$ciclo->fecha_inicio, $ciclo->fecha_fin])
                    ->orderBy('fecha_registro')
                    ->first();

                $data = [
                    'codigo_inscripcion' => $inscripcion->codigo_inscripcion,
                    'nombre_completo' => $estudiante->nombre . ' ' . $estudiante->apellido_paterno . ' ' . $estudiante->apellido_materno,
                    'documento' => $estudiante->numero_documento,
                    'carrera' => $inscripcion->carrera->nombre,
                    'aula' => $inscripcion->aula->codigo . ' - ' . $inscripcion->aula->nombre,
                    'turno' => $inscripcion->turno->nombre,
                    'celular' => $estudiante->telefono ?? 'No registrado',
                    'primer_registro' => $primerRegistro ? Carbon::parse($primerRegistro->fecha_registro)->format('d/m/Y') : 'Sin registro'
                ];

                // Si no hay primer registro, todos los exámenes están sin datos
                if (!$primerRegistro) {
                    $data['primer_examen'] = $this->getExamenVacio();
                    $data['segundo_examen'] = $this->getExamenVacio();
                    $data['tercer_examen'] = $this->getExamenVacio();
                    $data['total_ciclo'] = $this->getExamenVacio();
                    return $data;
                }

                // Calcular asistencia para cada examen
                // Primer Examen
                if ($ciclo->fecha_primer_examen) {
                    $data['primer_examen'] = $this->calcularAsistenciaExamen(
                        $estudiante->numero_documento,
                        $primerRegistro->fecha_registro,
                        $ciclo->fecha_primer_examen,
                        $ciclo
                    );
                } else {
                    $data['primer_examen'] = $this->getExamenVacio();
                }

                // Segundo Examen
                if ($ciclo->fecha_segundo_examen) {
                    $inicioSegundo = $this->getSiguienteDiaHabil($ciclo->fecha_primer_examen);
                    $data['segundo_examen'] = $this->calcularAsistenciaExamen(
                        $estudiante->numero_documento,
                        $inicioSegundo,
                        $ciclo->fecha_segundo_examen,
                        $ciclo
                    );
                } else {
                    $data['segundo_examen'] = $this->getExamenVacio();
                }

                // Tercer Examen
                if ($ciclo->fecha_tercer_examen && $ciclo->fecha_segundo_examen) {
                    $inicioTercero = $this->getSiguienteDiaHabil($ciclo->fecha_segundo_examen);
                    $data['tercer_examen'] = $this->calcularAsistenciaExamen(
                        $estudiante->numero_documento,
                        $inicioTercero,
                        $ciclo->fecha_tercer_examen,
                        $ciclo
                    );
                } else {
                    $data['tercer_examen'] = $this->getExamenVacio();
                }

                // Total del ciclo
                $data['total_ciclo'] = $this->calcularAsistenciaExamen(
                    $estudiante->numero_documento,
                    $primerRegistro->fecha_registro,
                    min(Carbon::now(), Carbon::parse($ciclo->fecha_fin)),
                    $ciclo
                );

                return $data;
            });

        // Obtener información del ciclo para el encabezado
        $ciclo = \App\Models\Ciclo::find($this->cicloId);

        return view('exports.asistencias-por-ciclo', [
            'inscripciones' => $inscripciones,
            'ciclo' => $ciclo,
            'fecha_generacion' => Carbon::now()->format('d/m/Y H:i:s')
        ]);
    }

    private function calcularAsistenciaExamen($numeroDocumento, $fechaInicio, $fechaExamen, $ciclo)
    {
        $hoy = Carbon::now();
        // IMPORTANTE: Usar startOfDay para la fecha de inicio
        $fechaInicioCarbon = Carbon::parse($fechaInicio)->startOfDay();
        $fechaExamenCarbon = Carbon::parse($fechaExamen)->endOfDay();

        // Si el examen aún no ha llegado, calcular hasta el final del día de hoy
        $fechaFinCalculo = $hoy < $fechaExamenCarbon ? $hoy->endOfDay() : $fechaExamenCarbon;

        // Si la fecha de inicio es futura, no calcular aún
        if ($fechaInicioCarbon > $hoy) {
            return [
                'dias_habiles' => 0,
                'dias_asistidos' => 0,
                'dias_falta' => 0,
                'porcentaje_asistencia' => 0,
                'porcentaje_falta' => 0,
                'condicion' => 'Pendiente',
                'puede_rendir' => '-'
            ];
        }

        // Calcular días hábiles - IMPORTANTE: usar endOfDay para incluir el día completo
        $diasHabilesTotales = $this->contarDiasHabiles(
            $fechaInicioCarbon->format('Y-m-d'),
            $fechaExamenCarbon->format('Y-m-d')
        );

        $diasHabilesTranscurridos = $this->contarDiasHabiles(
            $fechaInicioCarbon->format('Y-m-d'),
            $fechaFinCalculo->format('Y-m-d')
        );

        // DEBUG: Ver exactamente qué rango estamos usando
        \Log::info("DEBUG Rango de búsqueda", [
            'documento' => $numeroDocumento,
            'fecha_inicio_original' => $fechaInicio,
            'fecha_inicio_carbon' => $fechaInicioCarbon->format('Y-m-d H:i:s'),
            'fecha_fin_calculo' => $fechaFinCalculo->format('Y-m-d H:i:s'),
            'dias_habiles_transcurridos' => $diasHabilesTranscurridos
        ]);

        // Obtener días con asistencia - usar DATE para agrupar por día
        $registrosQuery = RegistroAsistencia::where('nro_documento', $numeroDocumento)
            ->whereBetween('fecha_registro', [
                $fechaInicioCarbon,
                $fechaFinCalculo
            ])
            ->select(DB::raw('DATE(fecha_registro) as fecha'))
            ->distinct();

        // DEBUG: Ver la consulta SQL exacta
        \Log::info("SQL Query", [
            'sql' => $registrosQuery->toSql(),
            'bindings' => $registrosQuery->getBindings()
        ]);

        $registros = $registrosQuery->get()->pluck('fecha');

        // Contar asistencias en días hábiles
        $diasConAsistencia = 0;
        $fechasContadas = [];

        foreach ($registros as $fecha) {
            $fechaCarbon = Carbon::parse($fecha);

            // Solo contar días hábiles (lunes a viernes)
            if ($fechaCarbon->isWeekday()) {
                $diasConAsistencia++;
                $fechasContadas[] = $fecha . ' (' . $fechaCarbon->format('l') . ')';
            }
        }

        // DEBUG: Mostrar todas las fechas encontradas
        \Log::info("DEBUG Asistencias encontradas", [
            'total_registros' => $registros->count(),
            'dias_habiles_con_asistencia' => $diasConAsistencia,
            'fechas_con_asistencia' => $fechasContadas
        ]);

        // Calcular faltas
        $diasFalta = max(0, $diasHabilesTranscurridos - $diasConAsistencia);

        // Calcular porcentajes sobre el total de días del período
        $porcentajeAsistencia = $diasHabilesTotales > 0 ?
            round(($diasConAsistencia / $diasHabilesTotales) * 100, 2) : 0;

        $porcentajeFalta = $diasHabilesTotales > 0 ?
            round(($diasFalta / $diasHabilesTotales) * 100, 2) : 0;

        // Calcular límites
        $limiteAmonestacion = ceil($diasHabilesTotales * ($ciclo->porcentaje_amonestacion / 100));
        $limiteInhabilitacion = ceil($diasHabilesTotales * ($ciclo->porcentaje_inhabilitacion / 100));

        // Determinar condición
        $condicion = 'Regular';
        $puedeRendir = 'SÍ';

        if ($diasFalta >= $limiteInhabilitacion) {
            $condicion = 'Inhabilitado';
            $puedeRendir = 'NO';
        } elseif ($diasFalta >= $limiteAmonestacion) {
            $condicion = 'Amonestado';
        }

        $resultado = [
            'dias_habiles' => $diasHabilesTotales,
            'dias_asistidos' => $diasConAsistencia,
            'dias_falta' => $diasFalta,
            'porcentaje_asistencia' => $porcentajeAsistencia,
            'porcentaje_falta' => $porcentajeFalta,
            'condicion' => $condicion,
            'puede_rendir' => $puedeRendir
        ];

        if ($diasHabilesTranscurridos < $diasHabilesTotales) {
            $resultado['dias_habiles_transcurridos'] = $diasHabilesTranscurridos;
            $resultado['es_proyeccion'] = true;
        }

        return $resultado;
    }

    // También asegúrate de que el método contarDiasHabiles sea inclusivo
    private function contarDiasHabiles($fechaInicio, $fechaFin)
    {
        $inicio = Carbon::parse($fechaInicio)->startOfDay();
        $fin = Carbon::parse($fechaFin)->startOfDay();
        $dias = 0;

        // IMPORTANTE: usar <= para incluir ambos días (inicio y fin)
        while ($inicio <= $fin) {
            if ($inicio->isWeekday()) {
                $dias++;
            }
            $inicio->addDay();
        }

        return $dias;
    }

    // También debes actualizar el método calcularAsistenciaExamenPdf con los mismos cambios
    private function calcularAsistenciaExamenPdf($numeroDocumento, $fechaInicio, $fechaExamen, $ciclo)
    {
        // Usar exactamente la misma lógica que calcularAsistenciaExamen
        return $this->calcularAsistenciaExamen($numeroDocumento, $fechaInicio, $fechaExamen, $ciclo);
    }



    private function getSiguienteDiaHabil($fecha)
    {
        $dia = Carbon::parse($fecha)->addDay();

        while (!$dia->isWeekday()) {
            $dia->addDay();
        }

        return $dia;
    }

    private function getExamenVacio()
    {
        return [
            'dias_habiles' => 0,
            'dias_asistidos' => 0,
            'dias_falta' => 0,
            'porcentaje_asistencia' => 0,
            'porcentaje_falta' => 0,
            'condicion' => 'Sin datos',
            'puede_rendir' => '-'
        ];
    }

    // AGREGAR ESTE MÉTODO AL FINAL DE LA CLASE
    public function styles(Worksheet $sheet)
    {
        // Asumiendo que los datos empiezan en la fila 4
        $ultimaFila = $sheet->getHighestRow();

        // Desactivar wrap text para las columnas de % Asist.
        $sheet->getStyle('I4:I' . $ultimaFila)->getAlignment()->setWrapText(false);
        $sheet->getStyle('N4:N' . $ultimaFila)->getAlignment()->setWrapText(false);
        $sheet->getStyle('S4:S' . $ultimaFila)->getAlignment()->setWrapText(false);

        return [];
    }
}
