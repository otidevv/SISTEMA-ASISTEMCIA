<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserRole extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'user_roles';

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
        'rol_id',
        'fecha_asignacion',
        'asignado_por',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'fecha_asignacion' => 'datetime',
    ];

    /**
     * Get the user that has the role.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    /**
     * Get the role assigned to the user.
     */
    public function role()
    {
        return $this->belongsTo(Role::class, 'rol_id');
    }

    /**
     * Get the user who assigned the role.
     */
    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'asignado_por');
    }
}
