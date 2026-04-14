<?php

namespace App\Http\Controllers;

use App\Models\Inscripcion;
use App\Models\Carrera;
use App\Models\Ciclo;
use App\Models\Postulacion;
use App\Models\RegistroAsistencia;
use App\Models\Aula;
use App\Models\User;
use App\Models\Carnet;
use App\Models\Curso;
use App\Models\CentroEducativo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Helpers\AsistenciaHelper;

class ReportesEstadisticosController extends Controller
{
    public function index(Request $request)
    {
        $this->authorizePermission('reportes.estadisticos.ver');

        $ciclo_id = $request->input('ciclo_id', 'global');
        $ciclos = Ciclo::orderBy('fecha_inicio', 'desc')->get();
        
        $selectedCiclo = null;
        if ($ciclo_id !== 'global') {
            $selectedCiclo = Ciclo::find($ciclo_id);
        } else {
            $selectedCiclo = Ciclo::where('es_activo', true)->first() ?? $ciclos->first();
        }

        $isReforzamiento = $ciclo_id !== 'global' && $selectedCiclo && $selectedCiclo->programa_id == 2;
        $inscripcionesTable = $isReforzamiento ? 'inscripciones_reforzamiento' : 'inscripciones';
        $statusActivo = $isReforzamiento ? 'validado' : 'activo';

        $carrerasStats = $this->getCarrerasStats($ciclo_id);
        $aulasStats = $this->getAulasStats($ciclo_id, $isReforzamiento);
        $documentosStats = $this->getDocumentosStats($ciclo_id);
        $docentesStats = $this->getDocentesStats($ciclo_id);
        $inhabilitadosStats = $this->getInhabilitadosStats($ciclo_id);
        $asistenciaStats = $this->getAsistenciaStats($selectedCiclo);
        $finanzasStats = $this->getFinanzasStats($ciclo_id);
        $procedenciaStats = $this->getProcedenciaStats($ciclo_id);

        if ($isReforzamiento) {
            $tipoInscripcionStats = DB::table($inscripcionesTable)
                ->select('grado as tipo_inscripcion', DB::raw('count(*) as total'))
                ->where('ciclo_id', $ciclo_id)
                ->where('estado_inscripcion', 'validado')
                ->whereNotNull('grado')
                ->groupBy('grado')->get();
        } else {
            $tipoInscripcionStats = DB::table($inscripcionesTable)
                ->select('tipo_inscripcion', DB::raw('count(*) as total'))
                ->when($ciclo_id !== 'global', fn($q) => $q->where('ciclo_id', $ciclo_id))
                ->whereNotNull('tipo_inscripcion')
                ->groupBy('tipo_inscripcion')->get();
        }

        if ($isReforzamiento) {
            $turnosStats = DB::table($inscripcionesTable)
                ->select('turno', DB::raw('count(*) as total'))
                ->where('ciclo_id', $ciclo_id)
                ->groupBy('turno')->get()
                ->map(fn($t) => (object)['turno' => $t->turno, 'total' => $t->total]);
        } else {
            $turnosStats = DB::table('inscripciones as i')
                ->join('turnos as t', 'i.turno_id', '=', 't.id')
                ->select('t.nombre as turno', DB::raw('count(*) as total'))
                ->when($ciclo_id !== 'global', fn($q) => $q->where('i.ciclo_id', $ciclo_id))
                ->groupBy('t.id', 't.nombre')->get();
        }

        $edades = DB::table($inscripcionesTable . ' as i')
            ->join('users as u', 'i.estudiante_id', '=', 'u.id')
            ->select('u.fecha_nacimiento')
            ->when($ciclo_id !== 'global', fn($q) => $q->where('i.ciclo_id', $ciclo_id))
            ->whereNotNull('u.fecha_nacimiento')
            ->get()
            ->groupBy(fn($i) => Carbon::parse($i->fecha_nacimiento)->age)
            ->map->count()->sortKeys();

        return view('reportes.estadisticos.index', compact(
            'carrerasStats', 'aulasStats', 'documentosStats', 'docentesStats', 
            'inhabilitadosStats', 'asistenciaStats', 'finanzasStats', 'procedenciaStats',
            'tipoInscripcionStats', 'turnosStats', 'edades', 'ciclos', 'ciclo_id', 'isReforzamiento'
        ));
    }

    private function getCarrerasStats($ciclo_id)
    {
        $ciclo = null;
        if ($ciclo_id && $ciclo_id !== 'global') {
            $ciclo = Ciclo::find($ciclo_id);
        }

        if ($ciclo && $ciclo->programa_id == 2) {
            return collect([]); // Reforzamiento no tiene carreras
        }

        $query = Carrera::select('id', 'nombre');
        $query->withCount(['postulaciones' => function($q) use ($ciclo_id) {
            if ($ciclo_id && $ciclo_id !== 'global') $q->where('ciclo_id', $ciclo_id);
        }]);
        return $query->orderBy('postulaciones_count', 'desc')->get();
    }

    private function getAulasStats($ciclo_id, $isReforzamiento = false)
    {
        $table = $isReforzamiento ? 'inscripciones_reforzamiento' : 'inscripciones';
        
        return DB::table($table . ' as i')
            ->join('aulas as a', 'i.aula_id', '=', 'a.id')
            ->select('a.nombre as aula', DB::raw('count(*) as total'))
            ->when($ciclo_id !== 'global', fn($q) => $q->where('i.ciclo_id', $ciclo_id))
            ->groupBy('a.id', 'a.nombre')
            ->orderBy('total', 'desc')
            ->get();
    }

    private function getDocumentosStats($ciclo_id)
    {
        $carnets = DB::table('carnets')
            ->select('entregado', DB::raw('count(*) as total'))
            ->when($ciclo_id !== 'global', fn($q) => $q->where('ciclo_id', $ciclo_id))
            ->groupBy('entregado')
            ->get();
        return ['carnets' => $carnets];
    }

    private function getDocentesStats($ciclo_id)
    {
        $docentes = DB::table('horarios_docentes')
            ->when($ciclo_id !== 'global', fn($q) => $q->where('ciclo_id', $ciclo_id))
            ->distinct('docente_id')
            ->count('docente_id');
        $cursos = DB::table('horarios_docentes')
            ->when($ciclo_id !== 'global', fn($q) => $q->where('ciclo_id', $ciclo_id))
            ->distinct('curso_id')
            ->count('curso_id');
        return ['docentes' => $docentes, 'cursos' => $cursos];
    }

    private function getInhabilitadosStats($ciclo_id)
    {
        if ($ciclo_id === 'global') {
            $statsGlobal = ['regulares' => 0, 'amonestados' => 0, 'inhabilitados' => 0, 'total_activos' => 0, 'total_desercion' => 0];
            
            // Ahora procesamos TODOS los ciclos (ativos y no activos) de forma inteligente
            // Usamos caché para que solo tarde la primera vez
            $ciclosConDatos = Ciclo::has('inscripciones')->orHas('inscripcionesReforzamiento')->get();

            foreach ($ciclosConDatos as $c) {
                $h = $this->procesarEstadisticasBatch($c);
                $statsGlobal['regulares'] += $h['regulares'];
                $statsGlobal['amonestados'] += $h['amonestados'];
                $statsGlobal['inhabilitados'] += $h['inhabilitados'];
                $statsGlobal['total_activos'] += $h['total_estudiantes'];
            }
            $statsGlobal['total_desercion'] = $statsGlobal['inhabilitados'];
            
            // Top 5 carreras global (esto ya es rápido por SQL)
            $statsGlobal['por_carrera'] = Carrera::withCount(['inscripciones'])
                ->orderBy('inscripciones_count', 'desc')->take(5)->get()
                ->map(fn($c) => ['nombre' => $c->nombre, 'total' => $c->inscripciones_count]);
            
            return $statsGlobal;
        }

        $ciclo = Ciclo::find($ciclo_id);
        if (!$ciclo) return $this->emptyInhabilitadosStats();

        $h = $this->procesarEstadisticasBatch($ciclo);
        
        $isReforzamiento = $ciclo->programa_id == 2;
        $relation = $isReforzamiento ? 'inscripcionesReforzamiento' : 'inscripciones';

        return [
            'regulares' => $h['regulares'],
            'amonestados' => $h['amonestados'],
            'inhabilitados' => $h['inhabilitados'],
            'total_activos' => $h['total_estudiantes'],
            'total_desercion' => $h['inhabilitados'],
            'por_carrera' => $isReforzamiento ? collect([]) : Carrera::withCount(['inscripciones as total' => function($q) use ($ciclo_id) {
                $q->where('ciclo_id', $ciclo_id);
            }])->orderBy('total', 'desc')->take(5)->get()->map(fn($c) => ['nombre' => $c->nombre, 'total' => $c->total])
        ];
    }

    private function procesarEstadisticasBatch($ciclo)
    {
        // Caché dinámico: Si el ciclo terminó, el caché es para siempre. Si es activo, dura 5 minutos.
        $cacheKey = 'stats_batch_ciclo_' . $ciclo->id;
        $isFin = Carbon::parse($ciclo->fecha_fin)->isPast();
        
        if (!$ciclo->es_activo && $isFin) {
            return \Illuminate\Support\Facades\Cache::rememberForever($cacheKey, function() use ($ciclo) {
                return $this->calcularEstadisticasRaw($ciclo);
            });
        }

        return \Illuminate\Support\Facades\Cache::remember($cacheKey, 300, function() use ($ciclo) {
            return $this->calcularEstadisticasRaw($ciclo);
        });
    }

    private function calcularEstadisticasRaw($ciclo)
    {
        $periodoInicio = Carbon::parse($ciclo->fecha_inicio)->startOfDay();
        $periodoFin = Carbon::parse($ciclo->fecha_fin)->endOfDay();
        $hoy = Carbon::now();
        $fechaCalculoAsistencia = $hoy < $periodoFin ? $hoy : $periodoFin;

        $isReforzamiento = $ciclo->programa_id == 2;
        $table = $isReforzamiento ? 'inscripciones_reforzamiento' : 'inscripciones';
        $statusActivo = $isReforzamiento ? ['validado'] : ['activo', 'amonestado', 'registrado'];

        $inscritos = DB::table($table . ' as i')
            ->join('users as u', 'i.estudiante_id', '=', 'u.id')
            ->where('i.ciclo_id', $ciclo->id)
            ->whereIn('i.estado_inscripcion', $statusActivo)
            ->select('u.numero_documento')
            ->get();

        $total = $inscritos->count();
        if ($total == 0) return ['regulares' => 0, 'amonestados' => 0, 'inhabilitados' => 0, 'total_estudiantes' => 0];

        $documentos = $inscritos->pluck('numero_documento')->filter()->toArray();

        $asistenciasCount = DB::table('registros_asistencia')
            ->whereIn('nro_documento', $documentos)
            ->whereBetween('fecha_registro', [$periodoInicio, $fechaCalculoAsistencia])
            ->select('nro_documento', DB::raw('COUNT(DISTINCT DATE(fecha_registro)) as total'))
            ->groupBy('nro_documento')
            ->get()
            ->pluck('total', 'nro_documento')
            ->toArray();

        $primerasAsistencias = DB::table('registros_asistencia')
            ->whereIn('nro_documento', $documentos)
            ->where('fecha_registro', '>=', $periodoInicio)
            ->select('nro_documento', DB::raw('MIN(fecha_registro) as fecha'))
            ->groupBy('nro_documento')
            ->get()
            ->pluck('fecha', 'nro_documento')
            ->toArray();

        $diasHabilesCiclo = AsistenciaHelper::contarDiasHabiles($periodoInicio, $periodoFin, $ciclo);
        $limiteAmonestacion = ceil($diasHabilesCiclo * (($ciclo->porcentaje_amonestacion ?? 20) / 100));
        $limiteInhabilitacion = ceil($diasHabilesCiclo * (($ciclo->porcentaje_inhabilitacion ?? 30) / 100));

        $reg = 0; $amo = 0; $inh = 0;
        $cacheDiasHabiles = [];
        
        // Pre-calcular para el periodo base
        $diasBase = AsistenciaHelper::contarDiasHabiles($periodoInicio, $fechaCalculoAsistencia, $ciclo);
        $cacheDiasHabiles[$periodoInicio->format('Y-m-d')] = $diasBase;

        foreach ($inscritos as $ins) {
            $doc = $ins->numero_documento;
            if (!$doc) { $reg++; continue; }
            
            $diasAsistidos = $asistenciasCount[$doc] ?? 0;
            $inicioRealRaw = $periodoInicio;
            
            if (isset($primerasAsistencias[$doc])) {
                $f1 = Carbon::parse($primerasAsistencias[$doc])->startOfDay();
                if ($f1->gt($periodoInicio)) $inicioRealRaw = $f1;
            }
            
            $key = $inicioRealRaw->format('Y-m-d');
            if (!isset($cacheDiasHabiles[$key])) {
                $cacheDiasHabiles[$key] = AsistenciaHelper::contarDiasHabiles($inicioRealRaw, $fechaCalculoAsistencia, $ciclo);
            }
            
            $diasHabilesTranscurridos = $cacheDiasHabiles[$key];
            $faltas = max(0, $diasHabilesTranscurridos - $diasAsistidos);
            
            if ($faltas >= $limiteInhabilitacion) { 
                $inh++; 
            } elseif ($faltas >= $limiteAmonestacion) { 
                $amo++; 
            } else { 
                $reg++; 
            }
        }
        return ['regulares' => $reg, 'amonestados' => $amo, 'inhabilitados' => $inh, 'total_estudiantes' => $total];
    }

    private function getAsistenciaStats($selectedCiclo = null)
    {
        $query = RegistroAsistencia::query();
        if ($selectedCiclo && $selectedCiclo->fecha_inicio && $selectedCiclo->fecha_fin) {
            $query->whereBetween('fecha_registro', [
                Carbon::parse($selectedCiclo->fecha_inicio)->startOfDay(), 
                Carbon::parse($selectedCiclo->fecha_fin)->endOfDay()
            ]);
        }
        return [
            'heatmap' => $query->selectRaw('HOUR(fecha_registro) as hora, COUNT(*) as total')->whereNotNull('nro_documento')->groupBy('hora')->orderBy('hora')->get(), 
            'rango' => $selectedCiclo ? "Periodo: " . Carbon::parse($selectedCiclo->fecha_inicio)->format('d/m/Y') . " - " . Carbon::parse($selectedCiclo->fecha_fin)->format('d/m/Y') : 'Consolidado Actual'
        ];
    }

    private function getFinanzasStats($ciclo_id)
    {
        $ciclo = null;
        if ($ciclo_id !== 'global') {
            $ciclo = Ciclo::find($ciclo_id);
        }

        if ($ciclo && $ciclo->programa_id == 2) {
            // Finanzas para Reforzamiento
            $total = DB::table('pagos_reforzamiento as p')
                ->join('inscripciones_reforzamiento as i', 'p.inscripcion_id', '=', 'i.id')
                ->where('i.ciclo_id', $ciclo_id)
                ->sum('p.monto');
            
            return [
                'total_recaudado' => $total,
                'por_carrera' => [] // Reforzamiento no suele tener carreras en esta vista
            ];
        }

        // Finanzas para CEPRE Regular
        $query = Postulacion::where('estado', 'aprobado');
        if ($ciclo_id && $ciclo_id !== 'global') $query->where('ciclo_id', $ciclo_id);
        
        return [
            'total_recaudado' => $query->sum('monto_total_pagado'),
            'por_carrera' => $query->selectRaw('carrera_id, sum(monto_total_pagado) as total')->groupBy('carrera_id')->get()->map(fn($item) => ['nombre' => Carrera::find($item->carrera_id)->nombre ?? 'N/A', 'total' => $item->total])
        ];
    }

    private function getProcedenciaStats($ciclo_id)
    {
        $ciclo = null;
        if ($ciclo_id !== 'global') {
            $ciclo = Ciclo::find($ciclo_id);
        }

        if ($ciclo && $ciclo->programa_id == 2) {
            // Procedencia para Reforzamiento
            $porColegio = DB::table('inscripciones_reforzamiento')
                ->where('ciclo_id', $ciclo_id)
                ->whereNotNull('colegio_procedencia')
                ->select('colegio_procedencia as nombre', DB::raw('count(*) as total'))
                ->groupBy('colegio_procedencia')
                ->orderBy('total', 'desc')
                ->limit(10)->get();
            
            return [
                'colegios' => $porColegio,
                'ciudades' => collect([]) // Reforzamiento no tiene ubigeo mapeado directamente en esta tabla
            ];
        }

        // Procedencia para CEPRE Regular
        $query = Postulacion::whereNotNull('centro_educativo_id');
        if ($ciclo_id && $ciclo_id !== 'global') $query->where('ciclo_id', $ciclo_id);
        
        $porColegio = (clone $query)->select('centro_educativo_id', DB::raw('count(*) as total'))->groupBy('centro_educativo_id')->orderBy('total', 'desc')->limit(10)->get();
        $centros = CentroEducativo::whereIn('id', $porColegio->pluck('centro_educativo_id'))->get()->keyBy('id');
        $colegiosResult = $porColegio->map(fn($item) => (object)['nombre' => $centros->get($item->centro_educativo_id)->cen_edu ?? 'C.E. #' . $item->centro_educativo_id, 'total' => $item->total]);

        $ciudadesStats = [];
        $todosLosCentrosIds = (clone $query)->pluck('centro_educativo_id')->unique();
        $todosLosCentros = CentroEducativo::whereIn('id', $todosLosCentrosIds)->select('id', 'd_prov')->get()->keyBy('id');
        
        foreach ((clone $query)->get() as $post) {
            $centro = $todosLosCentros->get($post->centro_educativo_id);
            if ($centro && $centro->d_prov) $ciudadesStats[$centro->d_prov] = ($ciudadesStats[$centro->d_prov] ?? 0) + 1;
        }
        
        return [
            'colegios' => $colegiosResult, 
            'ciudades' => collect($ciudadesStats)->map(fn($total, $ciudad) => (object)['ciudad' => $ciudad, 'total' => $total])->sortByDesc('total')->take(10)->values()
        ];
    }

    private function emptyInhabilitadosStats()
    {
        return ['regulares' => 0, 'amonestados' => 0, 'inhabilitados' => 0, 'total_activos' => 0, 'total_desercion' => 0, 'por_carrera' => []];
    }

    protected function authorizePermission($permission)
    {
        if (!auth()->user()->hasPermission($permission)) {
            abort(403, 'No tienes permiso para acceder a esta sección.');
        }
    }
}
