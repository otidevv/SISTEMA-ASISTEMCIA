<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\AsistenciaController;
use App\Http\Controllers\PerfilController;
use App\Http\Controllers\ParentescoController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\HorarioDocenteController;
use App\Http\Controllers\PagoDocenteController;
use App\Http\Controllers\AsistenciaDocenteController;
use App\Http\Controllers\CursoController;
// Ruta principal
Route::get('/', [HomeController::class, 'index'])->name('home');



// Rutas de autenticación
Route::middleware('guest')->group(function () {
    // Login
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);

    // Registro (opcional)
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);

    // Recuperación de contraseña
    Route::get('/forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::get('/reset-password/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password', [ResetPasswordController::class, 'reset'])->name('password.update');
});

// Rutas protegidas (requieren autenticación)
Route::middleware('auth')->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Logout
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    // Usuarios - Requiere permiso 'users.view'
    Route::middleware('can:users.view')->group(function () {
        Route::get('/usuarios', [UsuarioController::class, 'index'])->name('usuarios.index');
        Route::get('/usuarios/create', [UsuarioController::class, 'create'])->name('usuarios.create')->middleware('can:users.create');
        Route::post('/usuarios', [UsuarioController::class, 'store'])->name('usuarios.store')->middleware('can:users.create');
        Route::get('/usuarios/{usuario}/edit', [UsuarioController::class, 'edit'])->name('usuarios.edit')->middleware('can:users.edit');
        Route::put('/usuarios/{usuario}', [UsuarioController::class, 'update'])->name('usuarios.update')->middleware('can:users.edit');
        Route::delete('/usuarios/{usuario}', [UsuarioController::class, 'destroy'])->name('usuarios.destroy')->middleware('can:users.delete');
    });

    // Roles - Requiere permiso 'roles.view'
    Route::middleware('can:roles.view')->group(function () {
        Route::get('/roles', [RoleController::class, 'index'])->name('roles.index');
        Route::get('/roles/create', [RoleController::class, 'create'])->name('roles.create')->middleware('can:roles.create');
        Route::post('/roles', [RoleController::class, 'store'])->name('roles.store')->middleware('can:roles.create');
        Route::get('/roles/{role}/edit', [RoleController::class, 'edit'])->name('roles.edit')->middleware('can:roles.edit');
        Route::put('/roles/{role}', [RoleController::class, 'update'])->name('roles.update')->middleware('can:roles.edit');
        Route::delete('/roles/{role}', [RoleController::class, 'destroy'])->name('roles.destroy')->middleware('can:roles.delete');
        Route::get('/roles/permisos', [RoleController::class, 'permisosIndex'])->name('roles.permisos')->middleware('can:roles.assign_permissions');
        Route::post('/roles/permisos', [RoleController::class, 'permisosUpdate'])->name('roles.permisos.update')->middleware('can:roles.assign_permissions');
    });

    // Parentescos - Requiere permiso 'parentescos.view'
    Route::middleware('can:parentescos.view')->group(function () {
        Route::get('/parentescos', [ParentescoController::class, 'index'])->name('parentescos.index');
        Route::get('/parentescos/create', [ParentescoController::class, 'create'])->name('parentescos.create')->middleware('can:parentescos.create');
        Route::post('/parentescos', [ParentescoController::class, 'store'])->name('parentescos.store')->middleware('can:parentescos.create');
        Route::get('/parentescos/{parentesco}/edit', [ParentescoController::class, 'edit'])->name('parentescos.edit')->middleware('can:parentescos.edit');
        Route::put('/parentescos/{parentesco}', [ParentescoController::class, 'update'])->name('parentescos.update')->middleware('can:parentescos.edit');
        Route::delete('/parentescos/{parentesco}', [ParentescoController::class, 'destroy'])->name('parentescos.destroy')->middleware('can:parentescos.delete');
    });

    // Asistencia - Requiere algún permiso de asistencia
    Route::middleware('can:attendance.view')->group(function () {
        Route::get('/asistencia', [AsistenciaController::class, 'index'])->name('asistencia.index');
        Route::get('/asistencia/tiempo-real', [AsistenciaController::class, 'tiempoReal'])->name('asistencia.tiempo-real');
    });

    Route::middleware('can:attendance.register')->group(function () {
        Route::get('/asistencia/registrar', [AsistenciaController::class, 'registrarForm'])->name('asistencia.registrar');
        Route::post('/asistencia/registrar', [AsistenciaController::class, 'registrar'])->name('asistencia.registrar.store');
    });

    Route::middleware('can:attendance.edit')->group(function () {
        Route::get('/asistencia/editar', [AsistenciaController::class, 'editarIndex'])->name('asistencia.editar');
        Route::get('/asistencia/{asistencia}/editar', [AsistenciaController::class, 'editar'])->name('asistencia.editar.form');
        Route::put('/asistencia/{asistencia}', [AsistenciaController::class, 'update'])->name('asistencia.update');
    });

    Route::middleware('can:attendance.export')->group(function () {
        Route::get('/asistencia/exportar', [AsistenciaController::class, 'exportarIndex'])->name('asistencia.exportar');
        Route::post('/asistencia/exportar', [AsistenciaController::class, 'exportar'])->name('asistencia.exportar.action');
    });

    Route::middleware('can:attendance.reports')->group(function () {
        Route::get('/asistencia/reportes', [AsistenciaController::class, 'reportesIndex'])->name('asistencia.reportes');
    });


    Route::middleware('can:ciclos.view')->group(function () {
        Route::get('/ciclos', [App\Http\Controllers\CicloController::class, 'index'])->name('ciclos.index');
        Route::get('/ciclos/create', [App\Http\Controllers\CicloController::class, 'create'])->name('ciclos.create')->middleware('can:ciclos.create');
        Route::post('/ciclos', [App\Http\Controllers\CicloController::class, 'store'])->name('ciclos.store')->middleware('can:ciclos.create');
        Route::get('/ciclos/{ciclo}/edit', [App\Http\Controllers\CicloController::class, 'edit'])->name('ciclos.edit')->middleware('can:ciclos.edit');
        Route::put('/ciclos/{ciclo}', [App\Http\Controllers\CicloController::class, 'update'])->name('ciclos.update')->middleware('can:ciclos.edit');
        Route::delete('/ciclos/{ciclo}', [App\Http\Controllers\CicloController::class, 'destroy'])->name('ciclos.destroy')->middleware('can:ciclos.delete');
        Route::post('/ciclos/{ciclo}/activar', [App\Http\Controllers\CicloController::class, 'activar'])->name('ciclos.activar')->middleware('can:ciclos.activate');
    });

    Route::middleware('can:carreras.view')->group(function () {
        Route::get('/carreras', [App\Http\Controllers\CarreraController::class, 'index'])->name('carreras.index');
        Route::get('/carreras/create', [App\Http\Controllers\CarreraController::class, 'create'])->name('carreras.create')->middleware('can:carreras.create');
        Route::post('/carreras', [App\Http\Controllers\CarreraController::class, 'store'])->name('carreras.store')->middleware('can:carreras.create');
        Route::get('/carreras/{carrera}/edit', [App\Http\Controllers\CarreraController::class, 'edit'])->name('carreras.edit')->middleware('can:carreras.edit');
        Route::put('/carreras/{carrera}', [App\Http\Controllers\CarreraController::class, 'update'])->name('carreras.update')->middleware('can:carreras.edit');
        Route::delete('/carreras/{carrera}', [App\Http\Controllers\CarreraController::class, 'destroy'])->name('carreras.destroy')->middleware('can:carreras.delete');
    });

    Route::middleware('can:turnos.view')->group(function () {
        Route::get('/turnos', [App\Http\Controllers\TurnoController::class, 'index'])->name('turnos.index');
        Route::get('/turnos/create', [App\Http\Controllers\TurnoController::class, 'create'])->name('turnos.create')->middleware('can:turnos.create');
        Route::post('/turnos', [App\Http\Controllers\TurnoController::class, 'store'])->name('turnos.store')->middleware('can:turnos.create');
        Route::get('/turnos/{turno}/edit', [App\Http\Controllers\TurnoController::class, 'edit'])->name('turnos.edit')->middleware('can:turnos.edit');
        Route::put('/turnos/{turno}', [App\Http\Controllers\TurnoController::class, 'update'])->name('turnos.update')->middleware('can:turnos.edit');
        Route::delete('/turnos/{turno}', [App\Http\Controllers\TurnoController::class, 'destroy'])->name('turnos.destroy')->middleware('can:turnos.delete');
    });
    Route::middleware('can:aulas.view')->group(function () {
        Route::get('/aulas', [App\Http\Controllers\AulaController::class, 'index'])->name('aulas.index');
        Route::get('/aulas/create', [App\Http\Controllers\AulaController::class, 'create'])->name('aulas.create')->middleware('can:aulas.create');
        Route::post('/aulas', [App\Http\Controllers\AulaController::class, 'store'])->name('aulas.store')->middleware('can:aulas.create');
        Route::get('/aulas/{aula}/edit', [App\Http\Controllers\AulaController::class, 'edit'])->name('aulas.edit')->middleware('can:aulas.edit');
        Route::put('/aulas/{aula}', [App\Http\Controllers\AulaController::class, 'update'])->name('aulas.update')->middleware('can:aulas.edit');
        Route::delete('/aulas/{aula}', [App\Http\Controllers\AulaController::class, 'destroy'])->name('aulas.destroy')->middleware('can:aulas.delete');
        Route::get('/aulas/disponibilidad', [App\Http\Controllers\AulaController::class, 'disponibilidad'])->name('aulas.disponibilidad')->middleware('can:aulas.availability');
    });
    Route::middleware('can:inscripciones.view')->group(function () {
        Route::get('/inscripciones', [App\Http\Controllers\InscripcionController::class, 'index'])->name('inscripciones.index');
        Route::get('/inscripciones/create', [App\Http\Controllers\InscripcionController::class, 'create'])->name('inscripciones.create')->middleware('can:inscripciones.create');
        Route::post('/inscripciones', [App\Http\Controllers\InscripcionController::class, 'store'])->name('inscripciones.store')->middleware('can:inscripciones.create');
        Route::get('/inscripciones/{inscripcion}/edit', [App\Http\Controllers\InscripcionController::class, 'edit'])->name('inscripciones.edit')->middleware('can:inscripciones.edit');
        Route::put('/inscripciones/{inscripcion}', [App\Http\Controllers\InscripcionController::class, 'update'])->name('inscripciones.update')->middleware('can:inscripciones.edit');
        Route::delete('/inscripciones/{inscripcion}', [App\Http\Controllers\InscripcionController::class, 'destroy'])->name('inscripciones.destroy')->middleware('can:inscripciones.delete');
        Route::get('/inscripciones/reportes', [App\Http\Controllers\InscripcionController::class, 'reportes'])->name('inscripciones.reportes')->middleware('can:inscripciones.reports');
        Route::post('/inscripciones/exportar', [App\Http\Controllers\InscripcionController::class, 'exportar'])->name('inscripciones.exportar')->middleware('can:inscripciones.export');
    });

    // Perfil de usuario - Accesible para todos los usuarios autenticados
    Route::get('/perfil', [PerfilController::class, 'index'])->name('perfil.index');
    Route::get('/perfil/configuracion', [PerfilController::class, 'configuracion'])->name('perfil.configuracion');
    Route::put('/perfil', [PerfilController::class, 'update'])->name('perfil.update');
    Route::put('/perfil/password', [PerfilController::class, 'updatePassword'])->name('perfil.password');

    // Actualizar foto de perfil
    Route::put('/perfil/foto', [PerfilController::class, 'updateFoto'])->name('perfil.update.foto');
    // Eliminar foto de perfil
    Route::get('/perfil/foto/eliminar', [PerfilController::class, 'eliminarFoto'])->name('perfil.eliminar.foto');
    // Actualizar preferencias
    Route::put('/perfil/preferencias', [PerfilController::class, 'updatePreferencias'])->name('perfil.preferencias');
});

// Agrega el prefijo 'json' para todas las rutas de API
Route::prefix('json')->group(function () {


    // API Ciclos
    Route::prefix('ciclos')->group(function () {
        Route::get('/', [App\Http\Controllers\Api\CicloController::class, 'index']);
        Route::post('/', [App\Http\Controllers\Api\CicloController::class, 'store']);
        Route::get('/{id}', [App\Http\Controllers\Api\CicloController::class, 'show']);
        Route::put('/{id}', [App\Http\Controllers\Api\CicloController::class, 'update']);
        Route::delete('/{id}', [App\Http\Controllers\Api\CicloController::class, 'destroy']);
        Route::post('/{id}/activar', [App\Http\Controllers\Api\CicloController::class, 'activar']);
        Route::get('/activo/actual', [App\Http\Controllers\Api\CicloController::class, 'cicloActivo']);
    });

    // API Carreras
    Route::prefix('carreras')->group(function () {
        Route::get('/', [App\Http\Controllers\Api\CarreraController::class, 'index']);
        Route::post('/', [App\Http\Controllers\Api\CarreraController::class, 'store']);
        Route::get('/{id}', [App\Http\Controllers\Api\CarreraController::class, 'show']);
        Route::put('/{id}', [App\Http\Controllers\Api\CarreraController::class, 'update']);
        Route::delete('/{id}', [App\Http\Controllers\Api\CarreraController::class, 'destroy']);
        Route::patch('/{id}/status', [App\Http\Controllers\Api\CarreraController::class, 'changeStatus']);
        Route::get('/activas/lista', [App\Http\Controllers\Api\CarreraController::class, 'listaActivas']);
    });

    // API Turnos
    Route::prefix('turnos')->group(function () {
        Route::get('/', [App\Http\Controllers\Api\TurnoController::class, 'index']);
        Route::post('/', [App\Http\Controllers\Api\TurnoController::class, 'store']);
        Route::get('/{id}', [App\Http\Controllers\Api\TurnoController::class, 'show']);
        Route::put('/{id}', [App\Http\Controllers\Api\TurnoController::class, 'update']);
        Route::delete('/{id}', [App\Http\Controllers\Api\TurnoController::class, 'destroy']);
        Route::patch('/{id}/status', [App\Http\Controllers\Api\TurnoController::class, 'changeStatus']);
        Route::get('/activos/lista', [App\Http\Controllers\Api\TurnoController::class, 'listaActivos']);
    });

    // API Aulas
    Route::prefix('aulas')->group(function () {
        Route::get('/', [App\Http\Controllers\Api\AulaController::class, 'index']);
        Route::post('/', [App\Http\Controllers\Api\AulaController::class, 'store']);
        Route::get('/{id}', [App\Http\Controllers\Api\AulaController::class, 'show']);
        Route::put('/{id}', [App\Http\Controllers\Api\AulaController::class, 'update']);
        Route::delete('/{id}', [App\Http\Controllers\Api\AulaController::class, 'destroy']);
        Route::patch('/{id}/status', [App\Http\Controllers\Api\AulaController::class, 'changeStatus']);
        Route::get('/disponibles/capacidad/{capacidad}', [App\Http\Controllers\Api\AulaController::class, 'porCapacidad']);
        Route::get('/tipo/{tipo}', [App\Http\Controllers\Api\AulaController::class, 'porTipo']);
    });

    // API Inscripciones
    Route::prefix('inscripciones')->group(function () {
        // ✅ RUTAS ESPECÍFICAS PRIMERO
        Route::get('/aulas-disponibles', [App\Http\Controllers\Api\InscripcionController::class, 'aulasDisponibles']);
        Route::get('/estudiante/{estudianteId}', [App\Http\Controllers\Api\InscripcionController::class, 'porEstudiante']);
        Route::get('/ciclo/{cicloId}', [App\Http\Controllers\Api\InscripcionController::class, 'porCiclo']);
        Route::get('/carrera/{carreraId}', [App\Http\Controllers\Api\InscripcionController::class, 'porCarrera']);
        Route::get('/estadisticas/resumen', [App\Http\Controllers\Api\InscripcionController::class, 'estadisticas']);
        Route::get('/exportar/asistencias', [App\Http\Controllers\Api\InscripcionController::class, 'exportarAsistenciasPorCiclo']);
        Route::post('/exportar/excel', [App\Http\Controllers\Api\InscripcionController::class, 'exportarExcel']);

        // ✅ RUTAS GENERALES
        Route::get('/', [App\Http\Controllers\Api\InscripcionController::class, 'index']);
        Route::post('/', [App\Http\Controllers\Api\InscripcionController::class, 'store']);

        // ✅ RUTAS CON ID AL FINAL
        Route::get('/{id}', [App\Http\Controllers\Api\InscripcionController::class, 'show']);
        Route::put('/{id}', [App\Http\Controllers\Api\InscripcionController::class, 'update']);
        Route::delete('/{id}', [App\Http\Controllers\Api\InscripcionController::class, 'destroy']);
        Route::get('/pdf/{id}/reporte-asistencia', [App\Http\Controllers\Api\InscripcionController::class, 'reporteAsistenciaPdf']);
        Route::patch('/{id}/estado', [App\Http\Controllers\Api\InscripcionController::class, 'cambiarEstado']);
    });
    Route::prefix('reportes')->group(function () {
        Route::post('/asistencia-dia', [App\Http\Controllers\Api\ReporteController::class, 'asistenciaDia']);

        Route::post('/asistencia-dia/preview', [App\Http\Controllers\Api\ReporteController::class, 'asistenciaDiaPreview']);
    });

    Route::get('/estudiantes-sin-inscripcion', [App\Http\Controllers\Api\InscripcionController::class, 'estudiantesSinInscripcion']);

    // Ruta para obtener los roles
    Route::get('/roles', [UserController::class, 'getRoles']);
    // Rutas de API para usuarios (cambiado a 'usuarios' para coincidir con tu JS)
    Route::prefix('usuarios')->group(function () {
        Route::get('/', [UserController::class, 'index']);
        Route::post('/', [UserController::class, 'store']);
        Route::get('/{id}', [UserController::class, 'show']);
        Route::put('/{id}', [UserController::class, 'update']);
        Route::delete('/{id}', [UserController::class, 'destroy']);
        Route::patch('/{id}/status', [UserController::class, 'changeStatus']);
    });

    // Rutas para obtener estudiantes y padres para los selectores
    Route::get('/estudiantes', [App\Http\Controllers\Api\UserController::class, 'listarEstudiantes']);
    Route::get('/padres', [App\Http\Controllers\Api\UserController::class, 'listarPadres']);

    // CRUD de parentescos
    Route::prefix('parentescos')->group(function () {
        Route::get('/', [App\Http\Controllers\Api\ParentescoController::class, 'index']);
        Route::post('/', [App\Http\Controllers\Api\ParentescoController::class, 'store']);
        Route::get('/{id}', [App\Http\Controllers\Api\ParentescoController::class, 'show']);
        Route::put('/{id}', [App\Http\Controllers\Api\ParentescoController::class, 'update']);
        Route::delete('/{id}', [App\Http\Controllers\Api\ParentescoController::class, 'destroy']);
        Route::patch('/{id}/status', [App\Http\Controllers\Api\ParentescoController::class, 'changeStatus']);
    });

    // Rutas para el perfil de usuario
    Route::prefix('perfil')->group(function () {
        Route::get('/', [App\Http\Controllers\Api\PerfilController::class, 'index']);
        Route::put('/update', [App\Http\Controllers\Api\PerfilController::class, 'update']);
        Route::put('/password', [App\Http\Controllers\Api\PerfilController::class, 'updatePassword']);
        Route::post('/foto', [App\Http\Controllers\Api\PerfilController::class, 'updateFoto']);
        Route::delete('/foto', [App\Http\Controllers\Api\PerfilController::class, 'eliminarFoto']);
        Route::put('/preferencias', [App\Http\Controllers\Api\PerfilController::class, 'updatePreferencias']);
    });
    // Horarios Docentes
    Route::prefix('horarios-docentes')->middleware(['auth'])->group(function () {
        Route::get('/', [App\Http\Controllers\HorarioDocenteController::class, 'index'])->name('horarios-docentes.index');
        Route::get('/crear', [App\Http\Controllers\HorarioDocenteController::class, 'create'])->name('horarios-docentes.create');
        Route::post('/', [App\Http\Controllers\HorarioDocenteController::class, 'store'])->name('horarios-docentes.store');
        Route::get('/{id}/editar', [App\Http\Controllers\HorarioDocenteController::class, 'edit'])->name('horarios-docentes.edit');
        Route::put('/{id}', [App\Http\Controllers\HorarioDocenteController::class, 'update'])->name('horarios-docentes.update');
        Route::delete('/{id}', [App\Http\Controllers\HorarioDocenteController::class, 'destroy'])->name('horarios-docentes.delete');
    });
   // Pagos Docentes
    Route::prefix('pagos-docentes')->middleware(['auth'])->group(function () {
        Route::get('/', [App\Http\Controllers\PagoDocenteController::class, 'index'])->name('pagos-docentes.index');
        Route::get('/crear', [App\Http\Controllers\PagoDocenteController::class, 'create'])->name('pagos-docentes.create');
        Route::post('/', [App\Http\Controllers\PagoDocenteController::class, 'store'])->name('pagos-docentes.store');
        Route::get('/{id}/editar', [App\Http\Controllers\PagoDocenteController::class, 'edit'])->name('pagos-docentes.edit');
        Route::put('/{id}', [App\Http\Controllers\PagoDocenteController::class, 'update'])->name('pagos-docentes.update');
        Route::delete('/{id}', [App\Http\Controllers\PagoDocenteController::class, 'destroy'])->name('pagos-docentes.delete');
    });
   // Asistencia Docente
   Route::prefix('asistencia-docente')->middleware(['auth'])->group(function () {
       Route::get('/', [AsistenciaDocenteController::class, 'index'])->name('asistencia-docente.index');
       Route::get('/crear', [AsistenciaDocenteController::class, 'create'])->name('asistencia-docente.create');
       Route::post('/', [AsistenciaDocenteController::class, 'store'])->name('asistencia-docente.store');
       Route::get('/{id}/editar', [AsistenciaDocenteController::class, 'edit'])->name('asistencia-docente.edit');
       Route::put('/{id}', [AsistenciaDocenteController::class, 'update'])->name('asistencia-docente.update');
       Route::delete('/{id}', [AsistenciaDocenteController::class, 'destroy'])->name('asistencia-docente.destroy');
   
       // Opcionales si ya están implementadas
       Route::get('/editar', [AsistenciaDocenteController::class, 'editar'])->name('asistencia-docente.editar');
       Route::get('/exportar', [AsistenciaDocenteController::class, 'exportar'])->name('asistencia-docente.exportar');
       Route::post('/exportar', [AsistenciaDocenteController::class, 'exportarAction'])->name('asistencia-docente.exportar.action');
       Route::get('/reportes', [AsistenciaDocenteController::class, 'reports'])->name('asistencia-docente.reports');
       Route::get('/monitor', [AsistenciaDocenteController::class, 'monitor'])->name('asistencia-docente.monitor');
       Route::get('/ultimas-procesadas', [AsistenciaDocenteController::class, 'ultimasProcesadas'])->name('asistencia-docente.ultimas-procesadas');
  });

     // Cursos
     Route::prefix('cursos')->middleware(['auth'])->group(function () {
         Route::get('/', [App\Http\Controllers\CursoController::class, 'index'])->name('cursos.index');
         Route::get('/crear', [App\Http\Controllers\CursoController::class, 'create'])->name('cursos.create');
         Route::post('/', [App\Http\Controllers\CursoController::class, 'store'])->name('cursos.store');
         Route::get('/{curso}/editar', [App\Http\Controllers\CursoController::class, 'edit'])->name('cursos.edit');
         Route::put('/{curso}', [App\Http\Controllers\CursoController::class, 'update'])->name('cursos.update');
         Route::delete('/{curso}', [App\Http\Controllers\CursoController::class, 'destroy'])->name('cursos.destroy');
     
         // Ruta para alternar estado (activar/desactivar)
         Route::put('/{id}/toggle', [App\Http\Controllers\CursoController::class, 'toggle'])->name('cursos.toggle');
     });


});


Route::resource('horarios-docentes', HorarioDocenteController::class)->middleware('auth');

Route::get('api/consulta/{dni}', [App\Http\Controllers\ApiProxyController::class, 'consultaDNI']);
