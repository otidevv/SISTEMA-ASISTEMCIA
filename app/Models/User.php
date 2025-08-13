<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;

use Carbon\Carbon;

class User extends Authenticatable implements MustVerifyEmail
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'username',
        'email',
        'email_verified_at',
        'email_verification_token',
        'password_hash',
        'nombre',
        'apellido_paterno',
        'apellido_materno',
        'tipo_documento',
        'numero_documento',
        'telefono',
        'direccion',
        'fecha_nacimiento',
        'centro_educativo_id',
        'genero',
        'foto_perfil',
        'ultimo_acceso',
        'estado',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password_hash',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'fecha_nacimiento' => 'date',
        'ultimo_acceso' => 'datetime',
        'fecha_creacion' => 'datetime',
        'fecha_actualizacion' => 'datetime',
        'estado' => 'boolean',
    ];

    /**
     * Get the name of the password column for the model.
     *
     * @return string
     */
    public function getAuthPassword()
    {
        return $this->password_hash;
    }

    /**
     * Get the roles for the user.
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_roles', 'usuario_id', 'rol_id')
            ->withPivot('fecha_asignacion', 'asignado_por')
            ->withTimestamps();
    }

    /**
     * Get the permissions for the user through their roles.
     */
    public function permissions()
    {
        return $this->hasManyThrough(
            Permission::class,
            RolePermission::class,
            'rol_id',
            'id',
            'rol_id',
            'permiso_id'
        );
    }

    /**
     * Check if the user has a specific role.
     *
     * @param string $roleName
     * @return bool
     */
    public function hasRole($roleName)
    {
        return $this->roles()->where('nombre', $roleName)->exists();
    }

    /**
     * Check if the user has any of the given roles.
     *
     * @param array $roleNames
     * @return bool
     */
    public function hasAnyRole($roleNames)
    {
        return $this->roles()->whereIn('nombre', $roleNames)->exists();
    }
    
    /**
     * Assign a role to the user.
     *
     * @param string $roleName
     * @return bool
     */
    public function assignRole($roleName)
    {
        $role = \App\Models\Role::where('nombre', $roleName)->first();
        
        if (!$role) {
            return false;
        }
        
        // Check if user already has this role
        if ($this->hasRole($roleName)) {
            return true;
        }
        
        // Attach the role to the user
        $this->roles()->attach($role->id);
        
        return true;
    }
    
    /**
     * Remove a role from the user.
     *
     * @param string $roleName
     * @return bool
     */
    public function removeRole($roleName)
    {
        $role = \App\Models\Role::where('nombre', $roleName)->first();
        
        if (!$role) {
            return false;
        }
        
        // Check if user has this role
        if (!$this->hasRole($roleName)) {
            return true;
        }
        
        // Detach the role from the user
        $this->roles()->detach($role->id);
        
        return true;
    }

    /**
     * Check if the user has a specific permission.
     *
     * @param string $permissionCode
     * @return bool
     */
    public function hasPermission($permissionCode)
    {
        return $this->roles()
            ->whereHas('permissions', function ($query) use ($permissionCode) {
                $query->where('codigo', $permissionCode);
            })
            ->exists();
    }

    /**
     * Get the sessions for the user.
     */
    public function sessions()
    {
        return $this->hasMany(UserSession::class, 'usuario_id');
    }

    /**
     * Get the password history for the user.
     */
    public function passwordHistory()
    {
        return $this->hasMany(PasswordHistory::class, 'usuario_id');
    }

    /**
     * Get the reset tokens for the user.
     */
    public function resetTokens()
    {
        return $this->hasMany(PasswordResetToken::class, 'usuario_id');
    }

    /**
     * Get the user's full name.
     *
     * @return string
     */
    public function getFullNameAttribute()
    {
        return "{$this->nombre} {$this->apellido_paterno} " . ($this->apellido_materno ? $this->apellido_materno : '');
    }

    // Agrega esto a tu modelo User.php

    /**
     * Obtener el código del usuario (usando numero_documento)
     */
    public function getCodigoAttribute()
    {
        return $this->numero_documento;
    }

    /**
     * Obtener el nombre completo del usuario
     */
    public function getNombreCompletoAttribute()
    {
        return trim($this->nombre . ' ' . $this->apellido_paterno . ' ' . $this->apellido_materno);
    }


    /**
     * Obtener los horarios del docente
     */
    public function horarios()
    {
        return $this->hasMany(HorarioDocente::class, 'docente_id');
    }

    /**
     * Obtener el horario del docente para un día y hora específicos
     */
    public function getHorarioActual($fecha = null)
    {
        $fecha = $fecha ? Carbon::parse($fecha) : Carbon::now();
        $diaSemana = $fecha->dayOfWeek;
        $hora = $fecha->format('H:i:s');

        // Ajustar el día si tu BD usa 1-7 en lugar de 0-6
        $diaSemanaDB = $diaSemana == 0 ? 7 : $diaSemana;

        return $this->horarios()
            ->where('dia_semana', $diaSemanaDB)
            ->where('hora_inicio', '<=', $hora)
            ->where('hora_fin', '>=', $hora)
            ->with(['curso', 'aula'])
            ->first();
    }
}
