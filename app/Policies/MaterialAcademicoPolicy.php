<?php

namespace App\Policies;

use App\Models\MaterialAcademico;
use App\Models\User;
use App\Models\Inscripcion;
use Illuminate\Auth\Access\Response;

class MaterialAcademicoPolicy
{
    /**
     * Perform pre-authorization checks.
     */
    public function before(User $user, string $ability): bool|null
    {
        if ($user->hasRole('Admin')) {
            return true;
        }

        return null;
    }

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('materiales.view');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, MaterialAcademico $materialAcademico): bool
    {
        if ($user->hasPermission('materiales.view')) {
            if ($user->hasRole('Estudiante')) {
                $inscripcion = Inscripcion::where('estudiante_id', $user->id)->where('estado', 'activo')->first();
                if ($inscripcion) {
                    return $materialAcademico->ciclo_id === $inscripcion->ciclo_id && $materialAcademico->aula_id === $inscripcion->aula_id;
                }
                return false;
            }
            return true; // Admins y Docentes pueden ver todo
        }
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermission('materiales.manage');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, MaterialAcademico $materialAcademico): bool
    {
        if (!$user->hasPermission('material-academico.editar')) {
            return false;
        }
        return $user->id === $materialAcademico->profesor_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, MaterialAcademico $materialAcademico): bool
    {
        if (!$user->hasPermission('material-academico.eliminar')) {
            return false;
        }
        return $user->id === $materialAcademico->profesor_id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, MaterialAcademico $materialAcademico): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, MaterialAcademico $materialAcademico): bool
    {
        return false;
    }
}