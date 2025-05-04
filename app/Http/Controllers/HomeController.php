<?php

namespace App\Http\Controllers;

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
        // Puedes pasar datos a la vista si es necesario
        return view('welcome');
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
