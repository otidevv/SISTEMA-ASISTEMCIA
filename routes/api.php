<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\ReniecController;
use App\Http\Controllers\PostulacionUnificadaController;

// Dashboard API endpoints - Using web auth
Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/dashboard/datos-generales', [DashboardController::class, 'getDatosGenerales']);
    Route::get('/dashboard/anuncios', [DashboardController::class, 'getAnuncios']);
    Route::get('/dashboard/ultimos-registros', [DashboardController::class, 'getUltimosRegistros']);
    Route::get('/dashboard/admin', [DashboardController::class, 'getDatosAdmin']);
    Route::get('/dashboard/estudiante', [DashboardController::class, 'getDatosEstudiante']);
    Route::get('/dashboard/profesor', [DashboardController::class, 'getDatosProfesor']);
});

// RENIEC API endpoints
Route::middleware(['web', 'auth'])->group(function () {
    // Se comenta la ruta /api/reniec/consultar para evitar conflicto con la ruta pública definida en web.php
    // Route::post('/reniec/consultar', [ReniecController::class, 'consultarDni']);
    Route::post('/reniec/consultar-multiple', [ReniecController::class, 'consultarMultiple']);
});

// Postulación Unificada API endpoints
Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/postulacion-unificada/buscar-colegios', [PostulacionUnificadaController::class, 'buscarColegios']);
});

// En routes/ause Illuminate\Http\Request;pi.php
Route::get('/ultimos-registros', function (Request $request) {
    $ultimoId = $request->input('ultimo_id', 0);

    $registros = \App\Models\RegistroAsistencia::with('usuario')
        ->where('id', '>', $ultimoId)
        ->orderBy('id', 'asc')
        ->get()
        ->map(function ($registro) {
            return [
                'id' => $registro->id,
                'nro_documento' => $registro->nro_documento,
                'nombre_completo' => $registro->usuario ?
                    $registro->usuario->nombre . ' ' . $registro->usuario->apellido_paterno : null,
                'fecha_hora_formateada' => $registro->fecha_hora->format('d/m/Y H:i:s'),
                'tipo_verificacion_texto' => $registro->tipo_verificacion_texto,
                'foto_url' => $registro->usuario && $registro->usuario->foto_perfil ?
                    asset('storage/' . $registro->usuario->foto_perfil) : null,
                'iniciales' => $registro->usuario ?
                    strtoupper(substr($registro->usuario->nombre, 0, 1)) : null,
            ];
        });

    return response()->json($registros);
});
