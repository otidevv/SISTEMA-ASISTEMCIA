<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Anuncio;
use App\Models\Inscripcion;
use App\Models\RegistroAsistencia;
use App\Models\Ciclo;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Models\AsistenciaDocente;
use App\Models\HorarioDocente;
use App\Models\PagoDocente;
use App\Models\User;
use App\Models\Postulacion;
use App\Models\Carnet;
use App\Models\ResultadoExamen;
use App\Models\Carrera;
use App\Models\Curso;
use App\Helpers\AsistenciaHelper;

class DashboardController extends Controller
{
    public function getDatosGenerales()
    {
        try {
            $user = Auth::user();
            $ciclosActivos = Ciclo::where('es_activo', true)->get();
            $totalInscritos = 0;
            
            foreach ($ciclosActivos as $ciclo) {
                $totalInscritos += Inscripcion::where('ciclo_id', $ciclo->id)->where('estado_inscripcion', 'activo')->count();
            }

            $today = Carbon::today();
            $estudiantesHoy = RegistroAsistencia::whereDate('fecha_registro', $today)->where('nro_documento', '!=', '')->distinct('nro_documento')->count('nro_documento');

            return response()->json([
                'user' => ['name' => $user->name],
                'totalInscritosActivos' => $totalInscritos,
                'asistenciaHoy' => [
                    'estudiantes_unicos' => $estudiantesHoy,
                    'porcentaje_asistencia' => $totalInscritos > 0 ? round(($estudiantesHoy / $totalInscritos) * 100, 1) : 0
                ]
            ]);
        } catch (\Exception $e) { return response()->json(['error' => $e->getMessage()], 500); }
    }

    public function getDatosAdmin(Request $request)
    {
        try {
            $user = Auth::user();
            $ciclo_id = $request->input('ciclo_id', 'global');
            
            $queryCiclos = Ciclo::query();
            if ($ciclo_id === 'global') {
                $queryCiclos->where('es_activo', true);
            } else {
                $queryCiclos->where('id', $ciclo_id);
            }
            $ciclosToProcess = $queryCiclos->get();

            if ($ciclosToProcess->isEmpty()) {
                return response()->json(['error' => 'No hay ciclos activos', 'cicloActivo' => null]);
            }

            $hoy = Carbon::now();
            $data = [
                'totalInscripciones' => 0,
                'postulaciones' => ['total' => 0, 'pendientes' => 0, 'aprobadas' => 0, 'rechazadas' => 0],
                'carnets' => ['total' => 0, 'pendientes_impresion' => 0, 'pendientes_entrega' => 0, 'entregados' => 0],
                'totalDocentesActivos' => 0,
                'totalAulas' => 0,
                'estadisticasAsistencia' => ['regulares' => 0, 'amonestados' => 0, 'inhabilitados' => 0, 'total_estudiantes' => 0],
                'alertas' => []
            ];

            // Info estratégica consolidada
            $proximoHitoGlobal = null;
            $totalPct = 0;
            $ciclosCount = $ciclosToProcess->count();

            foreach ($ciclosToProcess as $ciclo) {
                $inicio = Carbon::parse($ciclo->fecha_inicio);
                $fin = Carbon::parse($ciclo->fecha_fin);
                $totalDias = max(1, $inicio->diffInDays($fin));
                $transcurridos = max(0, $inicio->diffInDays($hoy));
                $totalPct += min(100, round(($transcurridos / $totalDias) * 100, 1));

                // Escanear hitos de este ciclo para encontrar el más cercano globalmente
                $hitosCiclo = [
                    ['n' => 'Inicio de Clases', 'f' => $ciclo->fecha_inicio],
                    ['n' => '1er Examen', 'f' => $ciclo->fecha_primer_examen],
                    ['n' => '2do Examen', 'f' => $ciclo->fecha_segundo_examen],
                    ['n' => '3er Examen', 'f' => $ciclo->fecha_tercer_examen],
                    ['n' => 'Cierre de Ciclo', 'f' => $ciclo->fecha_fin]
                ];

                foreach ($hitosCiclo as $h) {
                    if ($h['f'] && $h['f'] !== '-' && $h['f'] !== '00/00/0000') {
                        $fHito = Carbon::parse($h['f']);
                        if ($fHito->endOfDay()->greaterThan($hoy)) {
                            if (!$proximoHitoGlobal || $fHito->lessThan(Carbon::parse($proximoHitoGlobal['fecha']))) {
                                $proximoHitoGlobal = [
                                    'nombre' => ($ciclo_id === 'global' ? "[{$ciclo->nombre}] " : "") . $h['n'],
                                    'fecha' => $fHito->format('Y-m-d H:i:s'),
                                    'dias_faltantes' => $hoy->diffInDays($fHito, false)
                                ];
                            }
                        }
                    }
                }
            }

            $cRef = $ciclosToProcess->first();
            $data['cicloActivo'] = [
                'nombre' => $ciclo_id === 'global' ? "CONSOLIDADO MAESTRO" : $cRef->nombre,
                'fecha_inicio' => Carbon::parse($cRef->fecha_inicio)->format('d/m/Y'),
                'fecha_fin' => Carbon::parse($cRef->fecha_fin)->format('d/m/Y'),
                'progreso_porcentaje' => $ciclosCount > 0 ? round($totalPct / $ciclosCount, 1) : 0,
                'proximo_hito' => $proximoHitoGlobal,
                'total_ciclos' => $ciclosCount,
                'es_global' => $ciclo_id === 'global'
            ];

            if ($ciclo_id !== 'global') {
                $data['cicloActivo']['fecha_examen_1'] = $cRef->fecha_primer_examen ? Carbon::parse($cRef->fecha_primer_examen)->format('d/m/Y') : null;
                $data['cicloActivo']['fecha_examen_2'] = $cRef->fecha_segundo_examen ? Carbon::parse($cRef->fecha_segundo_examen)->format('d/m/Y') : null;
                $data['cicloActivo']['fecha_examen_3'] = $cRef->fecha_tercer_examen ? Carbon::parse($cRef->fecha_tercer_examen)->format('d/m/Y') : null;
            }

            foreach ($ciclosToProcess as $ciclo) {
                // Agregar datos con seguridad
                $data['totalInscripciones'] += Inscripcion::where('ciclo_id', $ciclo->id)->where('estado_inscripcion', 'activo')->count();
                
                $post = Postulacion::where('ciclo_id', $ciclo->id)
                    ->selectRaw('COUNT(*) as t, SUM(CASE WHEN estado="pendiente" THEN 1 ELSE 0 END) as p, SUM(CASE WHEN estado="aprobado" THEN 1 ELSE 0 END) as a')
                    ->first();
                $data['postulaciones']['total'] += $post->t ?? 0;
                $data['postulaciones']['pendientes'] += $post->p ?? 0;
                $data['postulaciones']['aprobadas'] += $post->a ?? 0;

                $data['totalDocentesActivos'] += HorarioDocente::where('ciclo_id', $ciclo->id)->distinct('docente_id')->count('docente_id');
                $data['totalAulas'] += Inscripcion::where('ciclo_id', $ciclo->id)->where('estado_inscripcion', 'activo')->whereNotNull('aula_id')->distinct('aula_id')->count('aula_id');
                
                $stats = AsistenciaHelper::obtenerEstadisticasCiclo($ciclo);
                $data['estadisticasAsistencia']['regulares'] += $stats['regulares'];
                $data['estadisticasAsistencia']['amonestados'] += $stats['amonestados'];
                $data['estadisticasAsistencia']['inhabilitados'] += $stats['inhabilitados'];
                $data['estadisticasAsistencia']['total_estudiantes'] += $stats['total_estudiantes'];
            }

            $estudiantesHoy = RegistroAsistencia::whereDate('fecha_registro', Carbon::today())->distinct('nro_documento')->count('nro_documento');
            $data['asistenciaHoy'] = [
                'estudiantes_unicos' => $estudiantesHoy,
                'porcentaje' => $data['totalInscripciones'] > 0 ? round(($estudiantesHoy / $data['totalInscripciones']) * 100, 1) : 0
            ];

            return response()->json($data);
        } catch (\Exception $e) { return response()->json(['error' => $e->getMessage()], 500); }
    }

    public function getEstadisticasAsistencia(Request $request) { return $this->getDatosAdmin($request); }
    public function getAnuncios() { return response()->json(Anuncio::where('es_activo', true)->orderBy('fecha_publicacion', 'desc')->take(3)->get()); }
}
