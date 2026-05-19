<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Insertar el permiso en la tabla 'permissions'
        $permissionId = DB::table('permissions')->insertGetId([
            'nombre' => 'Gestionar Recuperación de Clases',
            'codigo' => 'ciclos.recuperaciones',
            'descripcion' => 'Permite configurar fechas de recuperación (sábados de recuperación u otros) para ciclos académicos',
            'modulo' => 'ciclos'
        ]);

        // 2. Obtener el permiso de 'ciclos.edit' para copiar sus asignaciones
        $editPermission = DB::table('permissions')->where('codigo', 'ciclos.edit')->first();

        if ($editPermission && $permissionId) {
            // Obtener todos los roles que tienen asignado 'ciclos.edit'
            $rolesWithEdit = DB::table('role_permissions')
                ->where('permiso_id', $editPermission->id)
                ->pluck('rol_id');

            // Asignar el nuevo permiso de recuperaciones a esos mismos roles
            foreach ($rolesWithEdit as $rolId) {
                // Verificar si ya está asignado para evitar duplicados
                $exists = DB::table('role_permissions')
                    ->where('rol_id', $rolId)
                    ->where('permiso_id', $permissionId)
                    ->exists();

                if (!$exists) {
                    DB::table('role_permissions')->insert([
                        'rol_id' => $rolId,
                        'permiso_id' => $permissionId
                    ]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Obtener el ID del permiso
        $permission = DB::table('permissions')->where('codigo', 'ciclos.recuperaciones')->first();

        if ($permission) {
            // Eliminar asignaciones de roles
            DB::table('role_permissions')->where('permiso_id', $permission->id)->delete();
            // Eliminar el permiso
            DB::table('permissions')->where('id', $permission->id)->delete();
        }
    }
};
