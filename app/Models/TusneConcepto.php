<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Concepto del catálogo de precios (TUSNE).
 */
class TusneConcepto extends Model
{
    protected $table = 'tusne_conceptos';

    protected $fillable = [
        'codigo',
        'nombre',
        'descripcion',
        'costo',
        'categoria',
        'requiere_pago',
        'anio',
        'activo',
    ];

    protected $casts = [
        'costo' => 'decimal:2',
        'requiere_pago' => 'boolean',
        'activo' => 'boolean',
    ];

    public function tipos()
    {
        return $this->hasMany(SolicitudTipo::class, 'tusne_concepto_id');
    }
}
