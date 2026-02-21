<?php

namespace App\Http\Controllers;

use App\Models\Ciclo;
use App\Models\Anuncio;
use App\Models\Curso;
use App\Models\User;
use App\Models\Carrera;
use App\Models\CicloCarreraVacante;
use App\Models\Inscripcion;
use Carbon\Carbon;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Mostrar la página de inicio.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $cicloActivo = Ciclo::activo()->first();
        $cursos = Curso::where('estado', 1)->orderBy('id', 'asc')->get();

        // Estadísticas
        $stats = [
            'estudiantes' => Inscripcion::where('estado_inscripcion', 'activo')->count(),
            'docentes' => User::whereHas('roles', function($q) {
                $q->where('nombre', 'profesor');
            })->count(),
            'ingresantes' => 1000, // Valor referencial histórico
            'cursos' => Curso::where('estado', 1)->count(),
        ];

        // Docentes destacados (activos en el ciclo actual con sus cursos)
        $docentes_destacados = collect();
        if ($cicloActivo) {
            $docentes_destacados = User::where('estado', 1)
                ->whereHas('roles', function($q) {
                    $q->where('nombre', 'profesor');
                })
                ->whereHas('horarios', function($q) use ($cicloActivo) {
                    $q->where('ciclo_id', $cicloActivo->id);
                })
                ->with(['horarios' => function($q) use ($cicloActivo) {
                    $q->where('ciclo_id', $cicloActivo->id)->with('curso');
                }])
                ->inRandomOrder()
                ->take(6) // Suficientes para el carrusel
                ->get();
        }

        // Próximos Eventos (Para contadores de la web pública)
        $proximoExamen = null;
        if ($cicloActivo) {
            $proximoExamenInfo = $cicloActivo->getProximoExamen();
            if ($proximoExamenInfo) {
                // Return date as string for easier JS parsing
                $proximoExamen = [
                    'nombre' => $proximoExamenInfo['nombre'],
                    'fecha' => Carbon::parse($proximoExamenInfo['fecha'])->format('M d, Y H:i:s')
                ];
            }
        }

        // Buscar el próximo ciclo que esté configurado para el futuro (incluso si no está activo aún)
        $proximoCicloRaw = Ciclo::where('fecha_inicio', '>', now())
                                ->orderBy('fecha_inicio', 'asc')
                                ->first();
        
        $proximoCiclo = null;
        if ($proximoCicloRaw) {
            $proximoCiclo = [
                'nombre' => $proximoCicloRaw->nombre,
                'fecha' => Carbon::parse($proximoCicloRaw->fecha_inicio)->format('M d, Y H:i:s')
            ];
        }

        return view('welcome', compact('cicloActivo', 'cursos', 'stats', 'docentes_destacados', 'proximoExamen', 'proximoCiclo'));
    }

    /**
     * Listado público de carreras profesionales.
     */
    public function carreras()
    {
        $carreras = Carrera::activas()->orderBy('grupo')->orderBy('nombre')->get();
        return view('public.carreras', compact('carreras'));
    }

    /**
     * Listado público de vacantes por carrera del ciclo activo.
     */
    public function vacantes()
    {
        $cicloActivo = Ciclo::activo()->first();
        $vacantes = [];

        if ($cicloActivo) {
            $vacantes = CicloCarreraVacante::with('carrera')
                ->where('ciclo_id', $cicloActivo->id)
                ->where('estado', true)
                ->get();
        }

        // Datos para el countdown widget
        $proximoExamen = null;
        if ($cicloActivo) {
            $proximoExamenInfo = $cicloActivo->getProximoExamen();
            if ($proximoExamenInfo) {
                $proximoExamen = [
                    'nombre' => $proximoExamenInfo['nombre'],
                    'fecha'  => Carbon::parse($proximoExamenInfo['fecha'])->format('M d, Y H:i:s')
                ];
            }
        }
        $proximoCicloRaw = Ciclo::where('fecha_inicio', '>', now())->orderBy('fecha_inicio')->first();
        $proximoCiclo = $proximoCicloRaw ? ['nombre' => $proximoCicloRaw->nombre, 'fecha' => Carbon::parse($proximoCicloRaw->fecha_inicio)->format('M d, Y H:i:s')] : null;

        return view('public.vacantes', compact('cicloActivo', 'vacantes', 'proximoExamen', 'proximoCiclo'));
    }

    /**
     * Listado público de cursos académicos.
     */
    public function cursos()
    {
        $cursos = Curso::where('estado', 1)->orderBy('id', 'asc')->get();
        return view('public.cursos', compact('cursos'));
    }

    /**
     * Mostrar información sobre el sistema.
     *
     * @return \Illuminate\View\View
     */
    public function about()
    {
        return view('about');
    }

    /**
     * Mostrar página de contacto.
     *
     * @return \Illuminate\View\View
     */
    public function contact()
    {
        return view('contact');
    }
}
