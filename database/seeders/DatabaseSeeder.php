<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Insert roles
        $this->seedRoles();

        // Insert permissions
        $this->seedPermissions();
        
        // Ejecutar seeders de módulos adicionales
        $this->call([
            moduloAcademico::class,
            moduloHorarioDocente::class,
        ]);

        // Assign permissions to roles
        $this->assignPermissionsToRoles();

        // Create users
        $this->seedUsers();

        // Seed parentescos
        $this->seedParentescos();

        // Ejecutar seeders de módulos adicionales
        $this->call([
            \Database\Seeders\ModuloHorarioDocente::class,
            \Database\Seeders\ModuloAcademico::class,
            \Database\Seeders\AttendanceViewPermissionSeeder::class,
            \Database\Seeders\MaterialesAcademicosPermissionsSeeder::class,
            \Database\Seeders\ConstanciasPermissionsSeeder::class,
            \Database\Seeders\PostulacionConstanciaPermissionsSeeder::class,
            \Database\Seeders\BoletinPermissionsSeeder::class,
            \Database\Seeders\TarjetasPreuniPermissionsSeeder::class,
            \Database\Seeders\TarjetasPreuniDemoSeeder::class,
        ]);
    }

    private function seedRoles(): void
    {
        DB::table('roles')->insert([
            [
                'nombre' => 'admin',
                'descripcion' => 'Administrador del sistema con acceso completo',
                'is_default' => false,
                'fecha_creacion' => now()
            ],
            [
                'nombre' => 'profesor',
                'descripcion' => 'Profesor con acceso a gestión de asistencias y cursos asignados',
                'is_default' => false,
                'fecha_creacion' => now()
            ],
            [
                'nombre' => 'estudiante',
                'descripcion' => 'Estudiante con acceso a visualizar su asistencia',
                'is_default' => true,
                'fecha_creacion' => now()
            ],
            [
                'nombre' => 'padre',
                'descripcion' => 'Padre o tutor con acceso a visualizar la asistencia e información de sus hijos',
                'is_default' => false,
                'fecha_creacion' => now()
            ]
        ]);
    }

    private function seedPermissions(): void
    {
        // Módulo de usuarios
        DB::table('permissions')->insert([
            [
                'nombre' => 'Ver Usuarios',
                'codigo' => 'users.view',
                'descripcion' => 'Permite ver la lista de usuarios',
                'modulo' => 'usuarios'
            ],
            [
                'nombre' => 'Crear Usuario',
                'codigo' => 'users.create',
                'descripcion' => 'Permite crear nuevos usuarios',
                'modulo' => 'usuarios'
            ],
            [
                'nombre' => 'Editar Usuario',
                'codigo' => 'users.edit',
                'descripcion' => 'Permite editar usuarios existentes',
                'modulo' => 'usuarios'
            ],
            [
                'nombre' => 'Eliminar Usuario',
                'codigo' => 'users.delete',
                'descripcion' => 'Permite eliminar usuarios',
                'modulo' => 'usuarios'
            ],
            [
                'nombre' => 'Cambiar Estado Usuario',
                'codigo' => 'users.change_status',
                'descripcion' => 'Permite activar/desactivar usuarios',
                'modulo' => 'usuarios'
            ]
        ]);

        // Módulo de roles
        DB::table('permissions')->insert([
            [
                'nombre' => 'Ver Roles',
                'codigo' => 'roles.view',
                'descripcion' => 'Permite ver la lista de roles',
                'modulo' => 'roles'
            ],
            [
                'nombre' => 'Crear Rol',
                'codigo' => 'roles.create',
                'descripcion' => 'Permite crear nuevos roles',
                'modulo' => 'roles'
            ],
            [
                'nombre' => 'Editar Rol',
                'codigo' => 'roles.edit',
                'descripcion' => 'Permite editar roles existentes',
                'modulo' => 'roles'
            ],
            [
                'nombre' => 'Eliminar Rol',
                'codigo' => 'roles.delete',
                'descripcion' => 'Permite eliminar roles',
                'modulo' => 'roles'
            ],
            [
                'nombre' => 'Asignar Permisos',
                'codigo' => 'roles.assign_permissions',
                'descripcion' => 'Permite asignar permisos a roles',
                'modulo' => 'roles'
            ]
        ]);

        // Módulo de asistencia
        DB::table('permissions')->insert([
            [
                'nombre' => 'Ver Asistencia',
                'codigo' => 'attendance.view',
                'descripcion' => 'Permite ver registros de asistencia',
                'modulo' => 'asistencia'
            ],
            [
                'nombre' => 'Registrar Asistencia',
                'codigo' => 'attendance.register',
                'descripcion' => 'Permite registrar asistencia',
                'modulo' => 'asistencia'
            ],
            [
                'nombre' => 'Editar Asistencia',
                'codigo' => 'attendance.edit',
                'descripcion' => 'Permite editar registros de asistencia',
                'modulo' => 'asistencia'
            ],
            [
                'nombre' => 'Exportar Asistencia',
                'codigo' => 'attendance.export',
                'descripcion' => 'Permite exportar registros de asistencia',
                'modulo' => 'asistencia'
            ],
            [
                'nombre' => 'Ver Reportes',
                'codigo' => 'attendance.reports',
                'descripcion' => 'Permite ver reportes de asistencia',
                'modulo' => 'asistencia'
            ]
        ]);

        // Módulo de parentescos
        DB::table('permissions')->insert([
            [
                'nombre' => 'Ver Parentescos',
                'codigo' => 'parentescos.view',
                'descripcion' => 'Permite ver los parentescos',
                'modulo' => 'parentescos'
            ],
            [
                'nombre' => 'Crear Parentesco',
                'codigo' => 'parentescos.create',
                'descripcion' => 'Permite crear nuevos parentescos',
                'modulo' => 'parentescos'
            ],
            [
                'nombre' => 'Editar Parentesco',
                'codigo' => 'parentescos.edit',
                'descripcion' => 'Permite editar parentescos existentes',
                'modulo' => 'parentescos'
            ],
            [
                'nombre' => 'Eliminar Parentesco',
                'codigo' => 'parentescos.delete',
                'descripcion' => 'Permite eliminar parentescos',
                'modulo' => 'parentescos'
            ]
        ]);

        // Módulo de Materiales Académicos
        DB::table('permissions')->insert([
            [
                'nombre' => 'Ver Materiales Académicos',
                'codigo' => 'materiales.view',
                'descripcion' => 'Permite ver materiales académicos',
                'modulo' => 'académico'
            ],
            [
                'nombre' => 'Gestionar Materiales Académicos',
                'codigo' => 'materiales.manage',
                'descripcion' => 'Permite crear, editar y eliminar materiales académicos',
                'modulo' => 'académico'
            ]
        ]);
    }

    private function assignPermissionsToRoles(): void
    {
        // Asignar todos los permisos al rol de administrador
        $permisos = DB::table('permissions')->select('id')->get();
        foreach ($permisos as $permiso) {
            DB::table('role_permissions')->insert([
                'rol_id' => 1,
                'permiso_id' => $permiso->id
            ]);
        }

        // Asignar permisos al rol de profesor
        $permisosProfesor = [
            'users.view',
            'roles.view',
            'attendance.view',
            'attendance.register',
            'attendance.edit',
            'attendance.export',
            'attendance.reports',
            'parentescos.view',
            // Permisos de constancias
            'constancias.view',
            // Permisos de horarios docentes
            'horarios-docentes.view',
            'horarios-docentes.create',
            'horarios-docentes.edit',
            'horarios-docentes.delete',
            // Permisos de pagos docentes
            'pagos-docentes.view',
            'pagos-docentes.create',
            'pagos-docentes.edit',
            'pagos-docentes.delete',
            // Permisos de asistencia docente
            'asistencia-docente.view',
            'asistencia-docente.create',
            'asistencia-docente.edit',
            'asistencia-docente.delete',
            'asistencia-docente.export',
            'asistencia-docente.reports',
            'asistencia-docente.monitor',
            // Permisos de cursos
            'cursos.view',
            'cursos.create',
            'cursos.edit',
            'cursos.delete',
            'cursos.toggle',
            // Permisos de materiales
            'materiales.view',
            'materiales.manage',
        ];

        $permisos = DB::table('permissions')
            ->whereIn('codigo', $permisosProfesor)
            ->select('id')
            ->get();

        foreach ($permisos as $permiso) {
            DB::table('role_permissions')->insert([
                'rol_id' => 2,
                'permiso_id' => $permiso->id
            ]);
        }

        // Asignar permisos al rol de estudiante
        $permisosEstudiante = ['attendance.view', 'constancias.view'];

        $permisos = DB::table('permissions')
            ->whereIn('codigo', $permisosEstudiante)
            ->select('id')
            ->get();

        foreach ($permisos as $permiso) {
            DB::table('role_permissions')->insert([
                'rol_id' => 3,
                'permiso_id' => $permiso->id
            ]);
        }

        // Asignar permisos al rol de padre
        $permisosPadre = [
            'attendance.view',
            'parentescos.view'
        ];

        $permisos = DB::table('permissions')
            ->whereIn('codigo', $permisosPadre)
            ->select('id')
            ->get();

        foreach ($permisos as $permiso) {
            DB::table('role_permissions')->insert([
                'rol_id' => 4,
                'permiso_id' => $permiso->id
            ]);
        }
    }

    private function seedUsers(): void
    {
        // Contraseña común para todos los usuarios
        $password = 'password'; // La contraseña para todos los usuarios
        $hashedPassword = Hash::make($password); // Esto generará el hash en el formato correcto para Laravel

        // Admin user
        DB::table('users')->insert([
            'username' => 'admin',
            'email' => 'admin@example.com',
            'password_hash' => $hashedPassword,
            'nombre' => 'Juan',
            'apellido_paterno' => 'Pérez',
            'apellido_materno' => 'Gómez',
            'tipo_documento' => 'DNI',
            'numero_documento' => '12345678',
            'telefono' => '987654321',
            'fecha_nacimiento' => '1990-01-01',
            'genero' => 'Masculino',
            'estado' => true,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $adminId = DB::getPdo()->lastInsertId();

        DB::table('user_roles')->insert([
            'usuario_id' => $adminId,
            'rol_id' => 1,
            'fecha_asignacion' => now()
        ]);

        // Professor users
        DB::table('users')->insert([
            'username' => 'profesor1',
            'email' => 'profesor1@example.com',
            'password_hash' => $hashedPassword,
            'nombre' => 'María',
            'apellido_paterno' => 'Rodríguez',
            'apellido_materno' => 'López',
            'tipo_documento' => 'DNI',
            'numero_documento' => '23456789',
            'telefono' => '987123456',
            'fecha_nacimiento' => '1985-03-15',
            'genero' => 'Femenino',
            'estado' => true,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $profesor1Id = DB::getPdo()->lastInsertId();

        DB::table('user_roles')->insert([
            'usuario_id' => $profesor1Id,
            'rol_id' => 2,
            'fecha_asignacion' => now()
        ]);

        DB::table('users')->insert([
            'username' => 'profesor2',
            'email' => 'profesor2@example.com',
            'password_hash' => $hashedPassword,
            'nombre' => 'Carlos',
            'apellido_paterno' => 'Martínez',
            'apellido_materno' => 'Sánchez',
            'tipo_documento' => 'DNI',
            'numero_documento' => '34567890',
            'telefono' => '987234567',
            'fecha_nacimiento' => '1982-07-20',
            'genero' => 'Masculino',
            'estado' => true,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $profesor2Id = DB::getPdo()->lastInsertId();

        DB::table('user_roles')->insert([
            'usuario_id' => $profesor2Id,
            'rol_id' => 2,
            'fecha_asignacion' => now()
        ]);

        // Student users
        DB::table('users')->insert([
            'username' => 'estudiante1',
            'email' => 'estudiante1@example.com',
            'password_hash' => $hashedPassword,
            'nombre' => 'Ana',
            'apellido_paterno' => 'González',
            'apellido_materno' => 'Torres',
            'tipo_documento' => 'DNI',
            'numero_documento' => '45678901',
            'telefono' => '987345678',
            'fecha_nacimiento' => '2000-05-10',
            'genero' => 'Femenino',
            'estado' => true,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $estudiante1Id = DB::getPdo()->lastInsertId();

        DB::table('user_roles')->insert([
            'usuario_id' => $estudiante1Id,
            'rol_id' => 3,
            'fecha_asignacion' => now()
        ]);

        DB::table('users')->insert([
            'username' => 'estudiante2',
            'email' => 'estudiante2@example.com',
            'password_hash' => $hashedPassword,
            'nombre' => 'Luis',
            'apellido_paterno' => 'Díaz',
            'apellido_materno' => 'Herrera',
            'tipo_documento' => 'DNI',
            'numero_documento' => '56789012',
            'telefono' => '987456789',
            'fecha_nacimiento' => '2001-09-25',
            'genero' => 'Masculino',
            'estado' => true,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $estudiante2Id = DB::getPdo()->lastInsertId();

        DB::table('user_roles')->insert([
            'usuario_id' => $estudiante2Id,
            'rol_id' => 3,
            'fecha_asignacion' => now()
        ]);

        DB::table('users')->insert([
            'username' => 'estudiante3',
            'email' => 'estudiante3@example.com',
            'password_hash' => $hashedPassword,
            'nombre' => 'Elena',
            'apellido_paterno' => 'Vargas',
            'apellido_materno' => 'Flores',
            'tipo_documento' => 'DNI',
            'numero_documento' => '67890123',
            'telefono' => '987567890',
            'fecha_nacimiento' => '2002-11-15',
            'genero' => 'Femenino',
            'estado' => true,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $estudiante3Id = DB::getPdo()->lastInsertId();

        DB::table('user_roles')->insert([
            'usuario_id' => $estudiante3Id,
            'rol_id' => 3,
            'fecha_asignacion' => now()
        ]);

        // Parent users
        DB::table('users')->insert([
            'username' => 'padre1',
            'email' => 'padre1@example.com',
            'password_hash' => $hashedPassword,
            'nombre' => 'Roberto',
            'apellido_paterno' => 'González',
            'apellido_materno' => 'Mejía',
            'tipo_documento' => 'DNI',
            'numero_documento' => '78901234',
            'telefono' => '987678901',
            'fecha_nacimiento' => '1975-04-18',
            'genero' => 'Masculino',
            'estado' => true,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $padre1Id = DB::getPdo()->lastInsertId();

        DB::table('user_roles')->insert([
            'usuario_id' => $padre1Id,
            'rol_id' => 4,
            'fecha_asignacion' => now()
        ]);

        DB::table('users')->insert([
            'username' => 'madre1',
            'email' => 'madre1@example.com',
            'password_hash' => $hashedPassword,
            'nombre' => 'Laura',
            'apellido_paterno' => 'Torres',
            'apellido_materno' => 'Vega',
            'tipo_documento' => 'DNI',
            'numero_documento' => '89012345',
            'telefono' => '987789012',
            'fecha_nacimiento' => '1978-09-22',
            'genero' => 'Femenino',
            'estado' => true,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $madre1Id = DB::getPdo()->lastInsertId();

        DB::table('user_roles')->insert([
            'usuario_id' => $madre1Id,
            'rol_id' => 4,
            'fecha_asignacion' => now()
        ]);

        DB::table('users')->insert([
            'username' => 'padre2',
            'email' => 'padre2@example.com',
            'password_hash' => $hashedPassword,
            'nombre' => 'Miguel',
            'apellido_paterno' => 'Díaz',
            'apellido_materno' => 'Luna',
            'tipo_documento' => 'DNI',
            'numero_documento' => '90123456',
            'telefono' => '987890123',
            'fecha_nacimiento' => '1972-11-05',
            'genero' => 'Masculino',
            'estado' => true,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $padre2Id = DB::getPdo()->lastInsertId();

        DB::table('user_roles')->insert([
            'usuario_id' => $padre2Id,
            'rol_id' => 4,
            'fecha_asignacion' => now()
        ]);
    }

    private function seedParentescos(): void
    {
        // Obtenemos IDs de los estudiantes
        $estudiantes = DB::table('users')
            ->join('user_roles', 'users.id', '=', 'user_roles.usuario_id')
            ->where('user_roles.rol_id', 3) // Rol de estudiante
            ->select('users.id')
            ->get();

        // Obtenemos IDs de los padres
        $padres = DB::table('users')
            ->join('user_roles', 'users.id', '=', 'user_roles.usuario_id')
            ->where('user_roles.rol_id', 4) // Rol de padre
            ->select('users.id', 'users.genero')
            ->get();

        // Crear relaciones de parentesco
        // Relación entre estudiante1 y sus padres
        $estudiante1 = $estudiantes[0]->id;
        $padre1 = $padres[0]->id; // Roberto González
        $madre1 = $padres[1]->id; // Laura Torres

        DB::table('parentescos')->insert([
            [
                'estudiante_id' => $estudiante1,
                'padre_id' => $padre1,
                'tipo_parentesco' => 'padre',
                'acceso_portal' => true,
                'recibe_notificaciones' => true,
                'contacto_emergencia' => true,
                'estado' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'estudiante_id' => $estudiante1,
                'padre_id' => $madre1,
                'tipo_parentesco' => 'madre',
                'acceso_portal' => true,
                'recibe_notificaciones' => true,
                'contacto_emergencia' => true,
                'estado' => true,
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);

        // Relación entre estudiante2 y su padre
        $estudiante2 = $estudiantes[1]->id;
        $padre2 = $padres[2]->id; // Miguel Díaz

        DB::table('parentescos')->insert([
            [
                'estudiante_id' => $estudiante2,
                'padre_id' => $padre2,
                'tipo_parentesco' => 'padre',
                'acceso_portal' => true,
                'recibe_notificaciones' => true,
                'contacto_emergencia' => true,
                'estado' => true,
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);

        // Relación entre estudiante3 y su madre (misma madre que estudiante1)
        $estudiante3 = $estudiantes[2]->id;

        DB::table('parentescos')->insert([
            [
                'estudiante_id' => $estudiante3,
                'padre_id' => $madre1,
                'tipo_parentesco' => 'madre',
                'acceso_portal' => true,
                'recibe_notificaciones' => true,
                'contacto_emergencia' => true,
                'estado' => true,
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);
    }
}
