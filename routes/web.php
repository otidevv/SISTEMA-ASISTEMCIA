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
