<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'permissions';

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
        'nombre',
        'codigo',
        'descripcion',
        'modulo',
    ];

    /**
     * Get the roles for the permission.
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_permissions', 'permiso_id', 'rol_id');
    }

    /**
     * Get the users that have this permission through their roles.
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_roles', 'rol_id', 'usuario_id')
            ->whereIn('user_roles.rol_id', function ($query) {
                $query->select('rol_id')
                    ->from('role_permissions')
                    ->where('permiso_id', $this->id);
            });
    }

    /**
     * Scope a query to only include permissions for a specific module.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $module
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeModule($query, $module)
    {
        return $query->where('modulo', $module);
    }
}
