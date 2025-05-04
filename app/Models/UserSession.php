<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserSession extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'user_sessions';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'usuario_id',
        'ip_address',
        'user_agent',
        'fecha_inicio',
        'fecha_fin',
        'estado',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'fecha_inicio' => 'datetime',
        'fecha_fin' => 'datetime',
    ];

    /**
     * Get the user that owns the session.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    /**
     * Check if the session is active.
     *
     * @return bool
     */
    public function isActive()
    {
        return $this->estado === 'activa';
    }

    /**
     * End the session.
     *
     * @return void
     */
    public function endSession()
    {
        $this->fecha_fin = now();
        $this->estado = 'cerrada';
        $this->save();
    }

    /**
     * Invalidate the session (e.g., for security reasons).
     *
     * @return void
     */
    public function invalidate()
    {
        $this->fecha_fin = now();
        $this->estado = 'invalidada';
        $this->save();
    }
}
