<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Carnet extends Model
{
    use HasFactory;

    protected $table = 'carnets';

    protected $fillable = [
        'codigo_carnet',
        'estudiante_id',
        'ciclo_id',
        'carrera_id',
        'turno_id',
        'aula_id',
        'tipo_carnet',
        'modalidad',
        'grupo',
        'fecha_emision',
        'fecha_vencimiento',
        'qr_code',
        'foto_path',
        'estado',
        'impreso',
        'fecha_impresion',
        'impreso_por',
        'entregado',
        'fecha_entrega',
        'entregado_por',
        'ip_entrega',
        'observaciones'
    ];

    protected $casts = [
        'fecha_emision' => 'date',
        'fecha_vencimiento' => 'date',
        'fecha_impresion' => 'datetime',
        'impreso' => 'boolean',
        'entregado' => 'boolean',
        'fecha_entrega' => 'datetime'
    ];

    /**
     * Relación con el estudiante
     */
    public function estudiante()
    {
        return $this->belongsTo(User::class, 'estudiante_id');
    }

    /**
     * Relación con el ciclo
     */
    public function ciclo()
    {
        return $this->belongsTo(Ciclo::class);
    }

    /**
     * Relación con la carrera
     */
    public function carrera()
    {
        return $this->belongsTo(Carrera::class);
    }

    /**
     * Relación con el turno
     */
    public function turno()
    {
        return $this->belongsTo(Turno::class);
    }

    /**
     * Relación con el aula
     */
    public function aula()
    {
        return $this->belongsTo(Aula::class);
    }

    /**
     * Relación con el usuario que imprimió
     */
    public function impresor()
    {
        return $this->belongsTo(User::class, 'impreso_por');
    }

    /**
     * Relación con el usuario que entregó
     */
    public function entregador()
    {
        return $this->belongsTo(User::class, 'entregado_por');
    }

    /**
     * Generar código único para el carnet
     */
    public static function generarCodigo($cicloId, $carreraId)
    {
        $ciclo = Ciclo::find($cicloId);
        $año = Carbon::now()->format('Y');
        $prefijo = "C{$año}";
        
        // Obtener el último número para este año
        $ultimoCarnet = self::where('codigo_carnet', 'like', $prefijo . '%')
            ->orderBy('codigo_carnet', 'desc')
            ->first();
        
        if ($ultimoCarnet) {
            $ultimoNumero = intval(substr($ultimoCarnet->codigo_carnet, -5));
            $nuevoNumero = $ultimoNumero + 1;
        } else {
            $nuevoNumero = 1;
        }
        
        return $prefijo . str_pad($nuevoNumero, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Verificar si el carnet está vencido
     */
    public function estaVencido()
    {
        return $this->fecha_vencimiento < Carbon::now();
    }

    /**
     * Actualizar estado a vencido si corresponde
     */
    public function actualizarEstadoVencimiento()
    {
        if ($this->estaVencido() && $this->estado == 'activo') {
            $this->estado = 'vencido';
            $this->save();
        }
    }

    /**
     * Scope para carnets activos
     */
    public function scopeActivos($query)
    {
        return $query->where('estado', 'activo');
    }

    /**
     * Scope para carnets por ciclo
     */
    public function scopePorCiclo($query, $cicloId)
    {
        return $query->where('ciclo_id', $cicloId);
    }

    /**
     * Scope para carnets por carrera
     */
    public function scopePorCarrera($query, $carreraId)
    {
        return $query->where('carrera_id', $carreraId);
    }

    /**
     * Scope para carnets pendientes de impresión
     */
    public function scopePendientesImpresion($query)
    {
        return $query->where('impreso', false);
    }

    /**
     * Scope para carnets entregados
     */
    public function scopeEntregados($query)
    {
        return $query->where('entregado', true);
    }

    /**
     * Scope para carnets pendientes de entrega
     */
    public function scopePendientesEntrega($query)
    {
        return $query->where('impreso', true)->where('entregado', false);
    }

    /**
     * Marcar como impreso
     */
    public function marcarComoImpreso($usuarioId)
    {
        $this->impreso = true;
        $this->fecha_impresion = Carbon::now();
        $this->impreso_por = $usuarioId;
        return $this->save();
    }

    /**
     * Marcar como entregado
     */
    public function marcarComoEntregado($usuarioId, $ip = null)
    {
        $this->entregado = true;
        $this->fecha_entrega = Carbon::now();
        $this->entregado_por = $usuarioId;
        $this->ip_entrega = $ip;
        return $this->save();
    }

    /**
     * Obtener estado de entrega
     */
    public function getEstadoEntregaAttribute()
    {
        if (!$this->impreso) {
            return 'No impreso';
        }
        if ($this->entregado) {
            return 'Entregado';
        }
        return 'Pendiente entrega';
    }

    /**
     * Obtener nombre completo del estudiante
     */
    public function getNombreCompletoAttribute()
    {
        $estudiante = $this->estudiante;
        return $estudiante ? "{$estudiante->apellido_paterno} {$estudiante->apellido_materno}, {$estudiante->nombre}" : '';
    }

    /**
     * Obtener la foto del estudiante
     */
    public function getFotoAttribute()
    {
        // Primero verificar si el carnet tiene foto propia
        if ($this->foto_path) {
            return $this->foto_path;
        }
        
        // Si no, buscar en la postulación del estudiante
        $postulacion = Postulacion::where('estudiante_id', $this->estudiante_id)
            ->where('ciclo_id', $this->ciclo_id)
            ->first();
            
        if ($postulacion && $postulacion->foto_path) {
            return $postulacion->foto_path;
        }
        
        // Si no, usar la foto del perfil del estudiante
        return $this->estudiante->foto_perfil ?? null;
    }
}