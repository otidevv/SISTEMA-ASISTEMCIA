<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\URL;
use App\Models\User;
use App\Models\Curso;
use App\Models\Ciclo;
use App\Models\Carrera;
use App\Models\Aula;
use App\Models\Turno;

class SitemapController extends Controller
{
    public function index()
    {
        $urls = collect();

        // URLs principales
        $urls->push(['loc' => URL::to('/'), 'priority' => '1.0', 'changefreq' => 'daily']);
        $urls->push(['loc' => URL::to('/login'), 'priority' => '0.9', 'changefreq' => 'monthly']);
        $urls->push(['loc' => URL::to('/register'), 'priority' => '0.8', 'changefreq' => 'monthly']);
        $urls->push(['loc' => URL::to('/dashboard'), 'priority' => '0.9', 'changefreq' => 'daily']);

        // URLs de asistencia
        $urls->push(['loc' => URL::to('/asistencia'), 'priority' => '0.8', 'changefreq' => 'daily']);
        $urls->push(['loc' => URL::to('/asistencia/registrar'), 'priority' => '0.7', 'changefreq' => 'daily']);
        $urls->push(['loc' => URL::to('/asistencia/reportes'), 'priority' => '0.7', 'changefreq' => 'weekly']);
        $urls->push(['loc' => URL::to('/asistencia/exportar'), 'priority' => '0.6', 'changefreq' => 'weekly']);

        // URLs de asistencia docente
        $urls->push(['loc' => URL::to('/asistencia-docente'), 'priority' => '0.8', 'changefreq' => 'daily']);
        $urls->push(['loc' => URL::to('/asistencia-docente/reportes'), 'priority' => '0.7', 'changefreq' => 'weekly']);
        $urls->push(['loc' => URL::to('/asistencia-docente/exportar'), 'priority' => '0.6', 'changefreq' => 'weekly']);

        // URLs de cursos
        $urls->push(['loc' => URL::to('/cursos'), 'priority' => '0.8', 'changefreq' => 'weekly']);
        $urls->push(['loc' => URL::to('/cursos/crear'), 'priority' => '0.6', 'changefreq' => 'monthly']);

        // URLs de usuarios
        $urls->push(['loc' => URL::to('/usuarios'), 'priority' => '0.7', 'changefreq' => 'weekly']);
        $urls->push(['loc' => URL::to('/usuarios/create'), 'priority' => '0.6', 'changefreq' => 'monthly']);

        // URLs de ciclos
        $urls->push(['loc' => URL::to('/ciclos'), 'priority' => '0.7', 'changefreq' => 'weekly']);
        $urls->push(['loc' => URL::to('/ciclos/create'), 'priority' => '0.6', 'changefreq' => 'monthly']);

        // URLs de carreras
        $urls->push(['loc' => URL::to('/carreras'), 'priority' => '0.7', 'changefreq' => 'weekly']);
        $urls->push(['loc' => URL::to('/carreras/create'), 'priority' => '0.6', 'changefreq' => 'monthly']);

        // URLs de turnos
        $urls->push(['loc' => URL::to('/turnos'), 'priority' => '0.7', 'changefreq' => 'weekly']);
        $urls->push(['loc' => URL::to('/turnos/create'), 'priority' => '0.6', 'changefreq' => 'monthly']);

        // URLs de aulas
        $urls->push(['loc' => URL::to('/aulas'), 'priority' => '0.7', 'changefreq' => 'weekly']);
        $urls->push(['loc' => URL::to('/aulas/create'), 'priority' => '0.6', 'changefreq' => 'monthly']);

        // URLs de inscripciones
        $urls->push(['loc' => URL::to('/inscripciones'), 'priority' => '0.8', 'changefreq' => 'daily']);
        $urls->push(['loc' => URL::to('/inscripciones/create'), 'priority' => '0.7', 'changefreq' => 'daily']);
        $urls->push(['loc' => URL::to('/inscripciones/reportes'), 'priority' => '0.6', 'changefreq' => 'weekly']);

        // URLs de horarios docentes
        $urls->push(['loc' => URL::to('/horarios-calendario'), 'priority' => '0.7', 'changefreq' => 'weekly']);
        $urls->push(['loc' => URL::to('/horarios-docentes'), 'priority' => '0.7', 'changefreq' => 'weekly']);

        // URLs de pagos docentes
        $urls->push(['loc' => URL::to('/pagos-docentes'), 'priority' => '0.7', 'changefreq' => 'weekly']);

        // URLs de roles
        $urls->push(['loc' => URL::to('/roles'), 'priority' => '0.6', 'changefreq' => 'monthly']);

        // URLs de parentescos
        $urls->push(['loc' => URL::to('/parentescos'), 'priority' => '0.6', 'changefreq' => 'monthly']);

        // URLs de perfil
        $urls->push(['loc' => URL::to('/perfil'), 'priority' => '0.8', 'changefreq' => 'weekly']);

        // URLs dinámicas para usuarios específicos (solo si tienen contenido público)
        $urls->push(['loc' => URL::to('/forgot-password'), 'priority' => '0.7', 'changefreq' => 'monthly']);
        $urls->push(['loc' => URL::to('/reset-password'), 'priority' => '0.7', 'changefreq' => 'monthly']);

        // Generar URLs dinámicas para recursos públicos
        $cursos = Curso::where('estado', 1)->get();
        foreach ($cursos as $curso) {
            $urls->push([
                'loc' => URL::to("/cursos/{$curso->id}/editar"),
                'priority' => '0.6',
                'changefreq' => 'weekly'
            ]);
        }

        $ciclos = Ciclo::where('estado', 1)->get();
        foreach ($ciclos as $ciclo) {
            $urls->push([
                'loc' => URL::to("/ciclos/{$ciclo->id}/editar"),
                'priority' => '0.6',
                'changefreq' => 'weekly'
            ]);
        }

        $carreras = Carrera::where('estado', 1)->get();
        foreach ($carreras as $carrera) {
            $urls->push([
                'loc' => URL::to("/carreras/{$carrera->id}/editar"),
                'priority' => '0.6',
                'changefreq' => 'weekly'
            ]);
        }

        $aulas = Aula::where('estado', 1)->get();
        foreach ($aulas as $aula) {
            $urls->push([
                'loc' => URL::to("/aulas/{$aula->id}/editar"),
                'priority' => '0.6',
                'changefreq' => 'weekly'
            ]);
        }

        $turnos = Turno::where('estado', 1)->get();
        foreach ($turnos as $turno) {
            $urls->push([
                'loc' => URL::to("/turnos/{$turno->id}/editar"),
                'priority' => '0.6',
                'changefreq' => 'weekly'
            ]);
        }

        $content = view('sitemap', ['urls' => $urls]);

        return response($content, 200)
            ->header('Content-Type', 'application/xml');
    }
}
