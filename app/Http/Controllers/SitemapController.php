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

        // URLs principales e Información Pública
        $urls->push(['loc' => URL::to('/'), 'priority' => '1.0', 'changefreq' => 'daily']);
        $urls->push(['loc' => URL::to('/carreras-profesionales'), 'priority' => '0.9', 'changefreq' => 'weekly']);
        $urls->push(['loc' => URL::to('/cuadro-vacantes'), 'priority' => '0.9', 'changefreq' => 'weekly']);
        $urls->push(['loc' => URL::to('/login'), 'priority' => '0.5', 'changefreq' => 'monthly']);

        $content = view('sitemap', ['urls' => $urls]);

        return response($content, 200)
            ->header('Content-Type', 'application/xml');
    }
}
