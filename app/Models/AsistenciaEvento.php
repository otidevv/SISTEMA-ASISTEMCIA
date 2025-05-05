<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Jobs\ProcessNewAttendanceEvent;

class AsistenciaEvento extends Model
{
    use HasFactory;

    /**
     * Nombre de la tabla asociada al modelo.
     *
     * @var string
     */
    protected $table = 'asistencia_eventos';

    /**
     * Los atributos que son asignables en masa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'registros_asistencia_id',
        'procesado',
    ];

    /**
     * Los atributos que deben convertirse a tipos nativos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'procesado' => 'boolean',
    ];

    /**
     * Obtener el registro de asistencia al que pertenece este evento.
     */
    public function registroAsistencia()
    {
        return $this->belongsTo(RegistroAsistencia::class, 'registros_asistencia_id', 'id');
    }

    /**
     * El método boot se ejecuta cuando el modelo se inicia.
     */
    protected static function booted()
    {
        static::created(function ($evento) {
            // Esto añadirá un trabajo a la cola cuando se cree un evento
            \App\Jobs\ProcessNewAttendanceEvent::dispatch($evento);
        });
    }
}
