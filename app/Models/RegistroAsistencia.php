<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RegistroAsistencia extends Model
{
    use HasFactory;

    /**
     * Nombre de la tabla en la base de datos.
     *
     * @var string
     */
    protected $table = 'registros_asistencia';

    /**
     * Indica si el modelo debe tener marcas de tiempo.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Los atributos que son asignables en masa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nro_documento',
        'fecha_hora',
        'tipo_verificacion',
        'estado',
        'codigo_trabajo',
        'terminal_id',
        'sn_dispositivo',
        'fecha_registro',
    ];

    /**
     * Los atributos que deben convertirse a tipos nativos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'fecha_hora' => 'datetime',
        'fecha_registro' => 'datetime',
        'estado' => 'boolean',
        'tipo_verificacion' => 'integer',
    ];

    /**
     * Obtener el usuario al que pertenece este registro de asistencia.
     */
    public function usuario()
    {
        return $this->belongsTo(User::class, 'nro_documento', 'numero_documento');
    }

    /**
     * Obtener el tipo de verificación como texto.
     */
    public function getTipoVerificacionTextoAttribute()
    {
        $tipos = [
            0 => 'Huella digital',
            1 => 'Tarjeta RFID',
            2 => 'Facial',
            3 => 'Código QR',
            4 => 'Manual',
        ];

        return isset($tipos[$this->tipo_verificacion]) ? $tipos[$this->tipo_verificacion] : 'Desconocido';
    }
}
