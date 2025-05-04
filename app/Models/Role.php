<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'roles';

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
        'descripcion',
        'is_default',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_default' => 'boolean',
        'fecha_creacion' => 'datetime',
    ];

    /**
     * Get the users for the role.
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_roles', 'rol_id', 'usuario_id')
            ->withPivot('fecha_asignacion', 'asignado_por')
            ->withTimestamps();
    }

    /**
     * Get the permissions for the role.
     */
    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'role_permissions', 'rol_id', 'permiso_id');
    }

    /**
     * Check if the role has a specific permission.
     *
     * @param string $permissionCode
     * @return bool
     */
    public function hasPermission($permissionCode)
    {
        return $this->permissions()
            ->where('codigo', $permissionCode)
            ->exists();
    }

    /**
     * Assign a permission to the role.
     *
     * @param int $permissionId
     * @return void
     */
    public function assignPermission($permissionId)
    {
        if (!$this->permissions()->where('permissions.id', $permissionId)->exists()) {
            $this->permissions()->attach($permissionId);
        }
    }

    /**
     * Remove a permission from the role.
     *
     * @param int $permissionId
     * @return void
     */
    public function removePermission($permissionId)
    {
        $this->permissions()->detach($permissionId);
    }
}
