<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Parentesco extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'parentescos';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'estudiante_id',
        'padre_id',
        'tipo_parentesco',
        'acceso_portal',
        'recibe_notificaciones',
        'contacto_emergencia',
        'estado',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'acceso_portal' => 'boolean',
        'recibe_notificaciones' => 'boolean',
        'contacto_emergencia' => 'boolean',
        'estado' => 'boolean',
    ];

    /**
     * Get the estudiante that owns the parentesco.
     */
    public function estudiante()
    {
        return $this->belongsTo(User::class, 'estudiante_id');
    }

    /**
     * Get the padre that owns the parentesco.
     */
    public function padre()
    {
        return $this->belongsTo(User::class, 'padre_id');
    }

    /**
     * Scope a query to only include active parentescos.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActivo($query)
    {
        return $query->where('estado', true);
    }

    /**
     * Scope a query to filter by tipo_parentesco.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $tipo
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeTipo($query, $tipo)
    {
        return $query->where('tipo_parentesco', $tipo);
    }

    /**
     * Scope a query to filter by emergency contacts.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeContactoEmergencia($query)
    {
        return $query->where('contacto_emergencia', true);
    }
}
