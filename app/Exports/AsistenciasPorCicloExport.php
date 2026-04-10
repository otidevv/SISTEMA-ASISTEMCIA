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
        $ciclo = \App\Models\Ciclo::find($this->cicloId);
        $hoy = Carbon::now();
        
        // 1. Obtener todas las inscripciones con sus relaciones
        $inscripciones = Inscripcion::with(['estudiante', 'aula', 'ciclo', 'carrera', 'turno'])
            ->where('ciclo_id', $this->cicloId)
            ->where('estado_inscripcion', 'activo')
            ->get();

        if ($inscripciones->isEmpty()) {
            return collect();
        }

        // 2. Obtener TODOS los números de documento de los estudiantes
        $documentos = $inscripciones->pluck('estudiante.numero_documento')->unique()->toArray();

        // 3. Obtener TODAS las asistencias del ciclo para TODOS los estudiantes de una sola vez
        // Agrupamos por documento y fecha para procesar en memoria
        $todasAsistencias = RegistroAsistencia::whereIn('nro_documento', $documentos)
            ->whereBetween('fecha_registro', [$ciclo->fecha_inicio, $ciclo->fecha_fin])
            ->select('nro_documento', DB::raw('DATE(fecha_registro) as fecha'))
            ->distinct()
            ->get()
            ->groupBy('nro_documento');

        // 4. Pre-calcular días hábiles para cada período del ciclo una sola vez
        $periodos = [
            'total' => ['inicio' => $ciclo->fecha_inicio, 'fin' => min($hoy, Carbon::parse($ciclo->fecha_fin))],
            'p1' => ['inicio' => $ciclo->fecha_inicio, 'fin' => $ciclo->fecha_primer_examen],
        ];

        if ($ciclo->fecha_segundo_examen) {
            $periodos['p2'] = ['inicio' => $this->getSiguienteDiaHabil($ciclo->fecha_primer_examen, $ciclo), 'fin' => $ciclo->fecha_segundo_examen];
        }
        if ($ciclo->fecha_tercer_examen) {
            $periodos['p3'] = ['inicio' => $this->getSiguienteDiaHabil($ciclo->fecha_segundo_examen, $ciclo), 'fin' => $ciclo->fecha_tercer_examen];
        }

        $diasHabilesPorPeriodo = [];
        foreach ($periodos as $key => $p) {
            if ($p['inicio'] && $p['fin']) {
                $diasHabilesPorPeriodo[$key] = [
                    'totales' => $this->contarDiasHabiles($p['inicio'], $p['fin'], $ciclo),
                    'transcurridos' => $this->contarDiasHabiles($p['inicio'], min($hoy, Carbon::parse($p['fin'])), $ciclo)
                ];
            }
        }

        // 5. Mapear inscripciones procesando todo en memoria
        return $inscripciones->map(function ($inscripcion) use ($ciclo, $todasAsistencias, $diasHabilesPorPeriodo, $hoy) {
            $estudiante = $inscripcion->estudiante;
            $documento = $estudiante->numero_documento;
            
            // Obtener fechas de asistencia del estudiante (de la colección cargada en memoria)
            $registrosEstudiante = $todasAsistencias->get($documento, collect())->pluck('fecha')->toArray();
            
            // Buscar primer registro en memoria
            $primerRegistroStr = !empty($registrosEstudiante) ? min($registrosEstudiante) : null;
            $primerRegistro = $primerRegistroStr ? Carbon::parse($primerRegistroStr) : null;

            $data = [
                'codigo_inscripcion' => $inscripcion->codigo_inscripcion,
                'nombre_completo' => $estudiante->nombre . ' ' . $estudiante->apellido_paterno . ' ' . $estudiante->apellido_materno,
                'documento' => $documento,
                'carrera' => $inscripcion->carrera->nombre,
                'aula' => $inscripcion->aula->codigo . ' - ' . $inscripcion->aula->nombre,
                'turno' => $inscripcion->turno->nombre,
                'celular' => $estudiante->telefono ?? 'No registrado',
                'primer_registro' => $primerRegistro ? $primerRegistro->format('d/m/Y') : 'Sin registro'
            ];

            if (!$primerRegistro) {
                $vacio = $this->getExamenVacio();
                $data['primer_examen'] = $vacio;
                $data['segundo_examen'] = $vacio;
                $data['tercer_examen'] = $vacio;
                $data['total_ciclo'] = $vacio;
                return $data;
            }

            // Procesar cada examen usando los datos pre-calculados y registros en memoria
            $examenesMapping = [
                'primer_examen' => 'p1',
                'segundo_examen' => 'p2',
                'tercer_examen' => 'p3',
                'total_ciclo' => 'total'
            ];

            foreach ($examenesMapping as $key => $periodoKey) {
                if (isset($diasHabilesPorPeriodo[$periodoKey])) {
                    $inicioPeriodo = Carbon::parse($periodos[$periodoKey]['inicio']);
                    $finCalculo = min($hoy, Carbon::parse($periodos[$periodoKey]['fin']));
                    
                    // Contar asistencias dentro del rango del período (en memoria)
                    $asistenciasEnPeriodo = 0;
                    foreach ($registrosEstudiante as $f) {
                        $fC = Carbon::parse($f);
                        if ($fC->between($inicioPeriodo, $finCalculo) && $ciclo->esDiaHabil($fC)) {
                            $asistenciasEnPeriodo++;
                        }
                    }

                    $data[$key] = $this->calcularEstadisticasMemoria(
                        $asistenciasEnPeriodo,
                        $diasHabilesPorPeriodo[$periodoKey]['totales'],
                        $diasHabilesPorPeriodo[$periodoKey]['transcurridos'],
                        $ciclo,
                        $inicioPeriodo
                    );
                } else {
                    $data[$key] = $this->getExamenVacio();
                }
            }

            return $data;
        });
    }

    private function calcularEstadisticasMemoria($asistencias, $diasHabilesTotales, $diasHabilesTranscurridos, $ciclo, $fechaInicio)
    {
        if ($fechaInicio > Carbon::now()) {
            return $this->getExamenVacio();
        }

        $diasFalta = max(0, $diasHabilesTranscurridos - $asistencias);
        $porcentajeAsistencia = $diasHabilesTotales > 0 ? round(($asistencias / $diasHabilesTotales) * 100, 2) : 0;
        $porcentajeFalta = $diasHabilesTotales > 0 ? round(($diasFalta / $diasHabilesTotales) * 100, 2) : 0;

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
            'dias_asistidos' => $asistencias,
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
        if (!$fecha) return null;
        $dia = Carbon::parse($fecha)->addDay();
        
        if (!$ciclo) {
            while (!$dia->isWeekday()) {
                $dia->addDay();
            }
        } else {
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