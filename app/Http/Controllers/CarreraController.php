<?php
// app/Http/Controllers/CarreraController.php

namespace App\Http\Controllers;

use App\Models\Carrera;
use Illuminate\Http\Request;

class CarreraController extends Controller
{
    public function index()
    {
        return view('carreras.index');
    }
}
