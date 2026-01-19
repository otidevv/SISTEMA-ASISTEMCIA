<?php

namespace App\Exports;

use App\Models\Inscripcion;
use App\Models\RegistroAsistencia;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AsistenciasPorCicloExport implements WithMultipleSheets
{
    protected $cicloId;

    public function __construct($cicloId)
    {
        $this->cicloId = $cicloId;
    }

    public function sheets(): array
    {
        $sheets = [];
        
        // Obtener todas las inscripciones procesadas
        $inscripciones = $this->obtenerInscripcionesProcesadas();
        $ciclo = \App\Models\Ciclo::find($this->cicloId);
        
        // Hoja 1: Reporte General
        $sheets[] = new AsistenciasPorCicloGeneralSheet($inscripciones, $ciclo);
        
        // Determinar el examen vigente (actual o próximo)
        $hoy = Carbon::now();
        $examenVigente = null;
        
        if ($ciclo->fecha_primer_examen && Carbon::parse($ciclo->fecha_primer_examen)->isFuture()) {
            $examenVigente = 'primer_examen';
        } elseif ($ciclo->fecha_segundo_examen && Carbon::parse($ciclo->fecha_segundo_examen)->isFuture()) {
            $examenVigente = 'segundo_examen';
        } elseif ($ciclo->fecha_tercer_examen && Carbon::parse($ciclo->fecha_tercer_examen)->isFuture()) {
            $examenVigente = 'tercer_examen';
        } else {
            // Si todos los exámenes pasaron, usar el último
            if ($ciclo->fecha_tercer_examen) {
                $examenVigente = 'tercer_examen';
            } elseif ($ciclo->fecha_segundo_examen) {
                $examenVigente = 'segundo_examen';
            } else {
                $examenVigente = 'primer_examen';
            }
        }
        
        // Filtrar estudiantes inhabilitados en el examen vigente
        $inhabilitados = $inscripciones->filter(function($inscripcion) use ($examenVigente) {
            if (!$examenVigente) return false;
            return strpos($inscripcion[$examenVigente]['condicion'], 'Inhabilitado') !== false;
        });
        
        // Si hay inhabilitados, crear hojas por aula y turno
        if ($inhabilitados->isNotEmpty()) {
            // Agrupar por aula y turno
            $gruposPorAulaTurno = $inhabilitados->groupBy(function($inscripcion) {
                return $inscripcion['aula'] . '|' . $inscripcion['turno'];
            });
            
            // Crear una hoja por cada combinación de aula-turno
            foreach ($gruposPorAulaTurno as $key => $estudiantes) {
                list($aula, $turno) = explode('|', $key);
                $sheets[] = new InhabilitadosPorAulaTurnoSheet($estudiantes, $ciclo, $aula, $turno);
            }
        }
        
        return $sheets;
    }
    
    private function obtenerInscripcionesProcesadas()
    {
        return Inscripcion::with(['estudiante', 'aula', 'ciclo', 'carrera', 'turno'])
            ->where('ciclo_id', $this->cicloId)
            ->where('estado_inscripcion', 'activo')
            ->get()
            ->map(function ($inscripcion) {
                $estudiante = $inscripcion->estudiante;
                $ciclo = $inscripcion->ciclo;

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

                if (!$primerRegistro) {
                    $data['primer_examen'] = $this->getExamenVacio();
                    $data['segundo_examen'] = $this->getExamenVacio();
                    $data['tercer_examen'] = $this->getExamenVacio();
                    $data['total_ciclo'] = $this->getExamenVacio();
                    return $data;
                }

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
                    $inicioSegundo = $this->getSiguienteDiaHabil($ciclo->fecha_primer_examen, $ciclo);
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
                    $inicioTercero = $this->getSiguienteDiaHabil($ciclo->fecha_segundo_examen, $ciclo);
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
    }

    private function calcularAsistenciaExamen($numeroDocumento, $fechaInicio, $fechaExamen, $ciclo)
    {
        $hoy = Carbon::now();
        $fechaInicioCarbon = Carbon::parse($fechaInicio)->startOfDay();
        $fechaExamenCarbon = Carbon::parse($fechaExamen)->endOfDay();
        $fechaFinCalculo = $hoy < $fechaExamenCarbon ? $hoy->endOfDay() : $fechaExamenCarbon;

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

        $diasHabilesTotales = $this->contarDiasHabiles(
            $fechaInicioCarbon->format('Y-m-d'),
            $fechaExamenCarbon->format('Y-m-d'),
            $ciclo
        );

        $diasHabilesTranscurridos = $this->contarDiasHabiles(
            $fechaInicioCarbon->format('Y-m-d'),
            $fechaFinCalculo->format('Y-m-d'),
            $ciclo
        );

        $registros = RegistroAsistencia::where('nro_documento', $numeroDocumento)
            ->whereBetween('fecha_registro', [$fechaInicioCarbon, $fechaFinCalculo])
            ->select(DB::raw('DATE(fecha_registro) as fecha'))
            ->distinct()
            ->get()
            ->pluck('fecha');

        $diasConAsistencia = 0;
        foreach ($registros as $fecha) {
            $fechaCarbon = Carbon::parse($fecha);
            if ($ciclo->esDiaHabil($fechaCarbon)) {
                $diasConAsistencia++;
            }
        }

        $diasFalta = max(0, $diasHabilesTranscurridos - $diasConAsistencia);
        $porcentajeAsistencia = $diasHabilesTotales > 0 ?
            round(($diasConAsistencia / $diasHabilesTotales) * 100, 2) : 0;
        $porcentajeFalta = $diasHabilesTotales > 0 ?
            round(($diasFalta / $diasHabilesTotales) * 100, 2) : 0;

        $limiteAmonestacion = ceil($diasHabilesTotales * ($ciclo->porcentaje_amonestacion / 100));
        $limiteInhabilitacion = ceil($diasHabilesTotales * ($ciclo->porcentaje_inhabilitacion / 100));

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

    private function contarDiasHabiles($fechaInicio, $fechaFin, $ciclo)
    {
        $inicio = Carbon::parse($fechaInicio)->startOfDay();
        $fin = Carbon::parse($fechaFin)->startOfDay();
        $dias = 0;

        while ($inicio <= $fin) {
            if ($ciclo->esDiaHabil($inicio)) {
                $dias++;
            }
            $inicio->addDay();
        }

        return $dias;
    }

    private function getSiguienteDiaHabil($fecha, $ciclo = null)
    {
        $dia = Carbon::parse($fecha)->addDay();
        
        // Si no hay ciclo, usar lógica por defecto (lunes a viernes)
        if (!$ciclo) {
            while (!$dia->isWeekday()) {
                $dia->addDay();
            }
        } else {
            // Usar lógica del ciclo
            while (!$ciclo->esDiaHabil($dia)) {
                $dia->addDay();
            }
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
}

// HOJA 1: Reporte General (tu vista actual)
class AsistenciasPorCicloGeneralSheet implements FromView, WithTitle, WithStyles
{
    protected $inscripciones;
    protected $ciclo;
    
    public function __construct($inscripciones, $ciclo)
    {
        $this->inscripciones = $inscripciones;
        $this->ciclo = $ciclo;
    }
    
    public function view(): View
    {
        return view('exports.asistencias-por-ciclo', [
            'inscripciones' => $this->inscripciones,
            'ciclo' => $this->ciclo,
            'fecha_generacion' => Carbon::now()->format('d/m/Y H:i:s')
        ]);
    }
    
    public function title(): string
    {
        return 'Reporte General';
    }
    
    public function styles(Worksheet $sheet)
    {
        $ultimaFila = $sheet->getHighestRow();
        $sheet->getStyle('I4:I' . $ultimaFila)->getAlignment()->setWrapText(false);
        $sheet->getStyle('N4:N' . $ultimaFila)->getAlignment()->setWrapText(false);
        $sheet->getStyle('S4:S' . $ultimaFila)->getAlignment()->setWrapText(false);
        return [];
    }
}

// HOJAS ADICIONALES: Inhabilitados por Aula y Turno (SIN VISTA BLADE)
class InhabilitadosPorAulaTurnoSheet implements FromView, WithTitle, WithStyles
{
    protected $estudiantes;
    protected $ciclo;
    protected $aula;
    protected $turno;
    
    public function __construct($estudiantes, $ciclo, $aula, $turno)
    {
        $this->estudiantes = $estudiantes;
        $this->ciclo = $ciclo;
        $this->aula = $aula;
        $this->turno = $turno;
    }
    
    public function view(): View
    {
        // Generar HTML directamente sin archivo blade separado
        $html = $this->generarHtmlInhabilitados();
        
        return view('exports.asistencias-por-ciclo', [
            'inscripciones' => $this->estudiantes,
            'ciclo' => $this->ciclo,
            'fecha_generacion' => Carbon::now()->format('d/m/Y H:i:s'),
            // Variables adicionales para personalizar el encabezado
            'es_reporte_inhabilitados' => true,
            'aula_filtro' => $this->aula,
            'turno_filtro' => $this->turno
        ]);
    }
    
    private function generarHtmlInhabilitados()
    {
        // Este método está aquí por si necesitas personalización futura
        return '';
    }
    
    public function title(): string
    {
        // Limitar a 31 caracteres (límite de Excel)
        $title = 'Inhab-' . substr($this->aula, 0, 10) . '-' . substr($this->turno, 0, 10);
        return substr($title, 0, 31);
    }
    
    public function styles(Worksheet $sheet)
    {
        $ultimaFila = $sheet->getHighestRow();
        
        // Encabezados en negrita y con fondo rojo para inhabilitados
        $sheet->getStyle('A1:Y1')->getFont()->setBold(true);
        $sheet->getStyle('A1:Y1')->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('c62828');
        $sheet->getStyle('A1:Y1')->getFont()->getColor()->setRGB('FFFFFF');
            
        // Centrar texto
        $sheet->getStyle('A1:Y' . $ultimaFila)->getAlignment()
            ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            
        return [];
    }
}