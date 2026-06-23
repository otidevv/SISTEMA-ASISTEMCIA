<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Documento oficial del TUSNE en PDF (sustento legal del catálogo).
 */
class TusneDocumento extends Model
{
    protected $table = 'tusne_documentos';

    protected $fillable = [
        'anio',
        'nombre_original',
        'path',
        'vigente',
    ];

    protected $casts = [
        'vigente' => 'boolean',
    ];
}
