<?php
// app/Http/Controllers/CicloController.php

namespace App\Http\Controllers;

use App\Models\Ciclo;
use Illuminate\Http\Request;

class CicloController extends Controller
{
    public function index()
    {
        return view('academico.ciclos.index');
    }

    public function create()
    {
        return view('academico.ciclos.create');
    }

    public function edit(Ciclo $ciclo)
    {
        return view('academico.ciclos.edit', compact('ciclo'));
    }
}
