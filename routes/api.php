<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

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
