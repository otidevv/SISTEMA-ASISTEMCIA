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
use App\Http\Controllers\CarnetController;
use App\Http\Controllers\Api\DashboardController as ApiDashboardController;
use App\Http\Controllers\Auth\PostulanteRegisterController;
use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Api\ReniecController;
use App\Http\Controllers\PostulacionController;
use App\Http\Controllers\PostulacionUnificadaController;
use App\Http\Controllers\MaterialAcademicoController;
use App\Http\Controllers\TarjetasController;

// Ruta principal
Route::get('/', [HomeController::class, 'index'])->name('home');

// Ruta para sitemap.xml
Route::get('/sitemap.xml', [App\Http\Controllers\SitemapController::class, 'index'])->name('sitemap');

// Agregar temporalmente en tu routes/web.php para debugging
Route::get('/debug-horarios-docentes', function () {
    $docentes = \App\Models\User::whereHas('roles', function ($query) {
        $query->where('nombre', 'profesor');
    })->with(['horarios.curso'])->get();

    echo "<h2>Debug de Horarios de Docentes</h2>";
    echo "<style>table { border-collapse: collapse; } td, th { border: 1px solid #ddd; padding: 8px; }</style>";

    foreach ($docentes as $docente) {
        echo "<h3>{$docente->nombre} {$docente->apellido_paterno} (ID: {$docente->id}, Doc: {$docente->numero_documento})</h3>";

        if ($docente->horarios->count() > 0) {
            echo "<table>";
            echo "<tr><th>Día (DB)</th><th>Día (Texto)</th><th>Hora Inicio</th><th>Hora Fin</th><th>Curso</th><th>Aula</th></tr>";

            foreach ($docente->horarios as $horario) {
                $dias = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];

                // Determinar qué tipo de día es
                $tipoValor = is_numeric($horario->dia_semana) ? 'Número' : 'Texto';

                echo "<tr>";
                echo "<td>{$horario->dia_semana} ({$tipoValor})</td>";
                echo "<td>";
                if (is_numeric($horario->dia_semana)) {
                    echo isset($dias[$horario->dia_semana]) ? $dias[$horario->dia_semana] : "Día {$horario->dia_semana}";
                } else {
                    echo $horario->dia_semana;
                }
                echo "</td>";
                echo "<td>{$horario->hora_inicio}</td>";
                echo "<td>{$horario->hora_fin}</td>";
                echo "<td>" . ($horario->curso ? $horario->curso->nombre : 'Sin curso') . "</td>";
                echo "<td>" . ($horario->aula ? $horario->aula->nombre : 'Sin aula') . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p>No tiene horarios asignados</p>";
        }
        echo "<hr>";
    }

    // Mostrar también los registros de asistencia recientes
    echo "<h2>Últimos Registros de Asistencia</h2>";
    $asistencias = \App\Models\RegistroAsistencia::with('usuario')
        ->whereHas('usuario.roles', function ($q) {
            $q->where('nombre', 'profesor');
        })
        ->orderBy('fecha_registro', 'desc')
        ->take(10)
        ->get();

    echo "<table>";
    echo "<tr><th>Docente</th><th>Fecha/Hora</th><th>Día Semana (Número)</th><th>Día Semana (Nombre)</th><th>Hora</th></tr>";

    $diasNombres = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];

    foreach ($asistencias as $asistencia) {
        $fecha = \Carbon\Carbon::parse($asistencia->fecha_registro);
        $diaSemanaNum = $fecha->dayOfWeek;
        $diaSemanaTexto = $diasNombres[$diaSemanaNum];

        echo "<tr>";
        echo "<td>" . ($asistencia->usuario ? $asistencia->usuario->nombre : 'N/A') . "</td>";
        echo "<td>{$asistencia->fecha_registro}</td>";
        echo "<td>{$diaSemanaNum}</td>";
        echo "<td>{$diaSemanaTexto}</td>";
        echo "<td>{$fecha->format('H:i:s')}</td>";
        echo "</tr>";
    }
    echo "</table>";

    // Verificar el tipo de datos en la BD
    echo "<h2>Información de la tabla horarios_docentes</h2>";
    $columna = \DB::select("SHOW COLUMNS FROM horarios_docentes WHERE Field = 'dia_semana'");
    if (!empty($columna)) {
        echo "<pre>";
        print_r($columna[0]);
        echo "</pre>";
    }
})->middleware('auth');

// API de consulta RENIEC (pública para todos los formularios)
Route::post('/api/reniec/consultar', [ReniecController::class, 'consultarDni'])->name('api.reniec.consultar');
Route::post('/api/reniec/consultar-multiple', [ReniecController::class, 'consultarMultiple'])->name('api.reniec.consultar.multiple');

// Rutas de autenticación
Route::middleware('guest')->group(function () {
    // Login
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);

    // Registro (opcional)
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);
    
    // Registro de postulantes
    Route::post('/register/postulante', [PostulanteRegisterController::class, 'register'])->name('register.postulante');
    
    
    // Verificación de email
    Route::get('/email/verify/{id}/{token}', [EmailVerificationController::class, 'verify'])->name('verification.verify');
    Route::post('/email/resend', [EmailVerificationController::class, 'resend'])->name('verification.resend');

    // Recuperación de contraseña
    Route::get('/forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::get('/reset-password/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password', [ResetPasswordController::class, 'reset'])->name('password.update');
});

// Postulación Pública (Accesible para todos, incluso logueados para pruebas)
Route::post('/postulacion/check-postulante', [App\Http\Controllers\PublicPostulacionController::class, 'checkPostulante'])->name('public.postulacion.check');
Route::post('/postulacion/validate-payment', [App\Http\Controllers\PublicPostulacionController::class, 'validatePayment'])->name('public.postulacion.validate-payment');
Route::post('/postulacion/store', [App\Http\Controllers\PublicPostulacionController::class, 'store'])->name('public.postulacion.store');

// API Ubigeo y Colegios (Público)
Route::get('/api/public/departamentos', [App\Http\Controllers\PublicPostulacionController::class, 'getDepartamentos']);
Route::get('/api/public/provincias/{departamento}', [App\Http\Controllers\PublicPostulacionController::class, 'getProvincias']);
Route::get('/api/public/distritos/{departamento}/{provincia}', [App\Http\Controllers\PublicPostulacionController::class, 'getDistritos']);
Route::post('/api/public/buscar-colegios', [App\Http\Controllers\PublicPostulacionController::class, 'buscarColegios']);

// Endpoint para refrescar el token CSRF (accesible para usuarios autenticados)
Route::get('/refresh-csrf', function () {
    return response()->json([
        'token' => csrf_token(),
        'timestamp' => now()->toIso8601String()
    ]);
})->middleware('web');

// API pública para obtener anuncios activos (para el modal de la página principal)
Route::get('/api/anuncios/activos', [App\Http\Controllers\AnnouncementController::class, 'getActivos'])->name('api.anuncios.activos');

// Rutas protegidas (requieren autenticación)
Route::middleware('auth')->group(function () {
    // Verificación de email - página de aviso
    Route::get('/email/verify', [EmailVerificationController::class, 'notice'])->name('verification.notice');
    
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Dashboard API endpoints
    Route::prefix('api/dashboard')->group(function () {
        Route::get('/datos-generales', [ApiDashboardController::class, 'getDatosGenerales']);
        Route::get('/anuncios', [ApiDashboardController::class, 'getAnuncios']);
        Route::get('/ultimos-registros', [ApiDashboardController::class, 'getUltimosRegistros']);
        Route::get('/admin', [ApiDashboardController::class, 'getDatosAdmin']);
        Route::get('/estudiante', [ApiDashboardController::class, 'getDatosEstudiante']);
        Route::get('/profesor', [ApiDashboardController::class, 'getDatosProfesor']);
    });

    // Notificaciones
    Route::get('/notificaciones', [App\Http\Controllers\NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notificaciones/fetch', [App\Http\Controllers\NotificationController::class, 'fetch'])->name('notifications.fetch');
    Route::get('/notificaciones/{id}/read', [App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('notifications.read');

    // Logout
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    // API de registro de postulantes (disponible para admins autenticados)
    Route::post('/api/register/postulante', [\App\Http\Controllers\Api\PostulanteRegisterController::class, 'register'])->name('api.register.postulante');
    Route::get('/api/register/check-email-server', [\App\Http\Controllers\Api\PostulanteRegisterController::class, 'checkEmailServer'])->name('api.register.check-email');
    Route::post('/api/register/resend-verification', [\App\Http\Controllers\Api\PostulanteRegisterController::class, 'resendVerification'])->name('api.register.resend');

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

  
    // ✅ ANUNCIOS - Sistema Completo CON CÓDIGOS CORRECTOS
    Route::prefix('anuncios')->middleware('auth')->name('anuncios.')->group(function () {
        // Ver lista de anuncios (para administradores)
        Route::get('/', [App\Http\Controllers\AnnouncementController::class, 'index'])
            ->name('index')
            ->middleware('can:announcements_view');
        
        // Crear nuevo anuncio
        Route::get('/crear', [App\Http\Controllers\AnnouncementController::class, 'create'])
            ->name('create')
            ->middleware('can:announcements_create');
        
        Route::post('/', [App\Http\Controllers\AnnouncementController::class, 'store'])
            ->name('store')
            ->middleware('can:announcements_create');
        
        // Ver anuncio específico
        Route::get('/{anuncio}', [App\Http\Controllers\AnnouncementController::class, 'show'])
            ->name('show')
            ->middleware('can:announcements_view');
        
        // Editar anuncio
        Route::get('/{anuncio}/editar', [App\Http\Controllers\AnnouncementController::class, 'edit'])
            ->name('edit')
            ->middleware('can:announcements_edit');
        
        Route::put('/{anuncio}', [App\Http\Controllers\AnnouncementController::class, 'update'])
            ->name('update')
            ->middleware('can:announcements_edit');
        // 🚀 AGREGAR ESTA LÍNEA PARA EL BOTÓN ACTIVAR/DESACTIVAR
        Route::patch('/{anuncio}/toggle-status', [App\Http\Controllers\AnnouncementController::class, 'toggleStatus'])
        ->name('toggle-status')
        ->middleware('can:announcements_edit');
        
        // Eliminar anuncio
        Route::delete('/{anuncio}', [App\Http\Controllers\AnnouncementController::class, 'destroy'])
            ->name('destroy')
            ->middleware('can:announcements_delete');
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

    // Asistencia de Estudiantes - Requiere algún permiso de asistencia
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
        
        // Nuevas rutas para registro masivo y regularización
        Route::get('/asistencia/estudiantes-filtrados', [AsistenciaController::class, 'getEstudiantesPorFiltros'])->name('asistencia.estudiantes.filtrados');
        Route::post('/asistencia/registrar-masivo', [AsistenciaController::class, 'registrarMasivo'])->name('asistencia.registrar.masivo');
        Route::post('/asistencia/regularizar', [AsistenciaController::class, 'regularizarEstudiante'])->name('asistencia.regularizar');
    });


    Route::middleware('can:attendance.export')->group(function () {
        Route::get('/asistencia/exportar', [AsistenciaController::class, 'exportarIndex'])->name('asistencia.exportar');
        Route::post('/asistencia/exportar', [AsistenciaController::class, 'exportarAction'])->name('asistencia.exportar.action');
    });

    Route::middleware('can:attendance.reports')->group(function () {
        Route::get('/asistencia/reportes', [AsistenciaController::class, 'reportesIndex'])->name('asistencia.reportes');
    });

    // ==========================================
    // ASISTENCIA DOCENTE - RUTAS WEB
    // ==========================================
    Route::prefix('asistencia-docente')->group(function () {
        Route::get('/', [AsistenciaDocenteController::class, 'index'])->name('asistencia-docente.index');
        Route::get('/crear', [AsistenciaDocenteController::class, 'create'])->name('asistencia-docente.create');
        Route::post('/', [AsistenciaDocenteController::class, 'store'])->name('asistencia-docente.store');
        Route::get('/{id}/editar', [AsistenciaDocenteController::class, 'edit'])->name('asistencia-docente.edit');
        Route::put('/{id}', [AsistenciaDocenteController::class, 'update'])->name('asistencia-docente.update');
        Route::delete('/{id}', [AsistenciaDocenteController::class, 'destroy'])->name('asistencia-docente.destroy');
        Route::get('/exportar', [AsistenciaDocenteController::class, 'exportar'])->name('asistencia-docente.exportar');
        Route::get('/reportes', [AsistenciaDocenteController::class, 'reports'])->name('asistencia-docente.reports');
        Route::get('/monitor', [AsistenciaDocenteController::class, 'monitor'])->name('asistencia-docente.monitor');
        
        // Ruta API específica para AJAX (registros recientes)
        Route::get('/ultimas-procesadas', [AsistenciaDocenteController::class, 'ultimasProcesadas'])->name('asistencia-docente.ultimas-procesadas');
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

    // Postulaciones - Gestión de postulaciones de estudiantes
    Route::middleware('can:postulaciones.view')->group(function () {
        Route::get('/postulaciones', [PostulacionController::class, 'index'])->name('postulaciones.index');
        Route::post('/postulaciones/crear-desde-admin', [PostulacionController::class, 'crearDesdeAdmin'])->name('postulaciones.crearDesdeAdmin')->middleware('can:postulaciones.create');
        
        // Importación Masiva
        Route::post('/postulaciones/importar', [PostulacionController::class, 'importar'])->name('postulaciones.importar')->middleware('can:postulaciones.create');
        Route::get('/postulaciones/plantilla', [PostulacionController::class, 'descargarPlantilla'])->name('postulaciones.plantilla')->middleware('can:postulaciones.create');

        // Reportes de postulaciones
        Route::get('/postulaciones/reportes/completos', [PostulacionController::class, 'reportesCompletos'])->name('postulaciones.reportes.completos')->middleware('can:postulaciones.reports');
        Route::get('/postulaciones/reportes/resumen', [PostulacionController::class, 'reportesResumen'])->name('postulaciones.reportes.resumen')->middleware('can:postulaciones.reports');

        // Exportar reportes
        Route::post('/postulaciones/reportes/completos/exportar', [PostulacionController::class, 'exportarReporteCompleto'])->name('postulaciones.reportes.completos.exportar')->middleware('can:postulaciones.export');
        Route::post('/postulaciones/reportes/resumen/exportar', [PostulacionController::class, 'exportarReporteResumen'])->name('postulaciones.reportes.resumen.exportar')->middleware('can:postulaciones.export');
    });
    
    // ✅ NUEVA FUNCIONALIDAD: Postulación Unificada - Para uso administrativo (Admin, Secretaria, Coordinador)
    Route::prefix('postulacion-unificada')->middleware('auth')->group(function () {
        Route::get('/crear', [PostulacionUnificadaController::class, 'create'])->name('postulacion-unificada.create')->middleware('can:postulaciones.create-unified');
        Route::get('/modal', [PostulacionUnificadaController::class, 'createModal'])->name('postulacion-unificada.modal')->middleware('can:postulaciones.create-unified');
        Route::get('/form-content', [PostulacionUnificadaController::class, 'getFormContent'])->name('postulacion-unificada.form-content')->middleware('can:postulaciones.create-unified');
        Route::get('/form-registro', [PostulacionUnificadaController::class, 'getFormRegistro'])->name('postulacion-unificada.form-registro')->middleware('can:postulaciones.create-unified');
        Route::post('/', [PostulacionUnificadaController::class, 'store'])->name('postulacion-unificada.store')->middleware('can:postulaciones.create-unified');
        Route::post('/registro-completo', [PostulacionUnificadaController::class, 'storeRegistroCompleto'])->name('postulacion-unificada.store-registro')->middleware('can:postulaciones.create-unified');
    });
    
    // ✅ NUEVA FUNCIONALIDAD: API para Postulación Unificada
    Route::prefix('api/postulacion-unificada')->middleware('auth')->group(function () {
        Route::get('/departamentos', [PostulacionUnificadaController::class, 'getDepartamentos']);
        Route::get('/provincias/{departamento}', [PostulacionUnificadaController::class, 'getProvincias']);
        Route::get('/distritos/{departamento}/{provincia}', [PostulacionUnificadaController::class, 'getDistritos']);
        Route::post('/buscar-colegios', [PostulacionUnificadaController::class, 'buscarColegios']);
    });
    
    // API para buscar postulantes existentes
    Route::prefix('api/postulantes')->middleware('auth')->group(function () {
        Route::get('/buscar/{dni}', [PostulacionUnificadaController::class, 'buscarPostulante'])->middleware('can:postulaciones.create-unified');
    });
    
    // Carnets - Gestión de carnets de estudiantes
    Route::middleware('can:carnets.view')->group(function () {
        Route::get('/carnets', [CarnetController::class, 'index'])->name('carnets.index');
        Route::post('/carnets/exportar-pdf', [CarnetController::class, 'exportarPDF'])->name('carnets.exportar-pdf')->middleware('can:carnets.export');
        
        // Escaneo y entrega de carnets
        Route::get('/carnets/escanear', [CarnetController::class, 'vistaEscanear'])->name('carnets.escanear')->middleware('can:carnets.scan_delivery');
        Route::post('/carnets/escanear-qr', [CarnetController::class, 'escanearQR'])->name('carnets.escanear-qr')->middleware('can:carnets.scan_delivery');
        Route::post('/carnets/registrar-entrega', [CarnetController::class, 'registrarEntrega'])->name('carnets.registrar-entrega')->middleware('can:carnets.scan_delivery');
        
        // Reportes de entrega
        Route::post('/carnets/exportar-entregas', [CarnetController::class, 'exportarExcelEntregas'])->name('carnets.exportar-entregas')->middleware('can:carnets.export_delivery');
        Route::get('/carnets/estadisticas-entrega', [CarnetController::class, 'estadisticasEntrega'])->name('carnets.estadisticas-entrega')->middleware('can:carnets.delivery_reports');
    });
    
    // Plantillas de Carnets - Editor visual
    Route::prefix('carnets/plantillas')->middleware('can:carnets.templates.view')->group(function () {
        Route::get('/', [App\Http\Controllers\CarnetTemplateController::class, 'index'])->name('carnets.templates.index');
        Route::get('/crear', [App\Http\Controllers\CarnetTemplateController::class, 'create'])->name('carnets.templates.create')->middleware('can:carnets.templates.create');
        Route::post('/', [App\Http\Controllers\CarnetTemplateController::class, 'store'])->name('carnets.templates.store')->middleware('can:carnets.templates.create');
        Route::get('/{id}/editar', [App\Http\Controllers\CarnetTemplateController::class, 'edit'])->name('carnets.templates.edit')->middleware('can:carnets.templates.edit');
        Route::put('/{id}', [App\Http\Controllers\CarnetTemplateController::class, 'update'])->name('carnets.templates.update')->middleware('can:carnets.templates.edit');
        Route::post('/{id}/activar', [App\Http\Controllers\CarnetTemplateController::class, 'activate'])->name('carnets.templates.activate')->middleware('can:carnets.templates.activate');
        Route::delete('/{id}', [App\Http\Controllers\CarnetTemplateController::class, 'destroy'])->name('carnets.templates.destroy')->middleware('can:carnets.templates.delete');
        Route::post('/upload-fondo', [App\Http\Controllers\CarnetTemplateController::class, 'uploadFondo'])->name('carnets.templates.upload-fondo')->middleware('can:carnets.templates.create');
    });
    
    // Rutas para constancias de postulación (accesibles para estudiantes/postulantes)
    Route::prefix('postulacion/constancia')->middleware('auth')->group(function () {
        Route::get('/generar/{postulacion}', [App\Http\Controllers\ConstanciaPostulacionController::class, 'generarConstancia'])
            ->name('postulacion.constancia.generar');
        Route::post('/subir/{postulacion}', [App\Http\Controllers\ConstanciaPostulacionController::class, 'subirConstanciaFirmada'])
            ->name('postulacion.constancia.subir');
        Route::post('/subir-admin/{postulacion}', [App\Http\Controllers\ConstanciaPostulacionController::class, 'subirConstanciaFirmadaAdmin'])
            ->name('postulacion.constancia.subir-admin');
        Route::get('/ver/{postulacion}', [App\Http\Controllers\ConstanciaPostulacionController::class, 'verConstanciaFirmada'])
            ->name('postulacion.constancia.ver');
        Route::get('/estado/{postulacion}', [App\Http\Controllers\ConstanciaPostulacionController::class, 'estadoConstancia'])
            ->name('postulacion.constancia.estado');
    });

    // Rutas para constancias de estudios (accesibles para estudiantes inscritos)
    Route::prefix('constancias/estudios')->group(function () {
        Route::get('/generar/{inscripcion}', [App\Http\Controllers\ConstanciaEstudiosController::class, 'generarConstancia'])
            ->name('constancias.estudios.generar')
            ->middleware('can:constancias.generar-estudios');
        Route::get('/ver/{constancia}', [App\Http\Controllers\ConstanciaEstudiosController::class, 'verConstancia'])
            ->name('constancias.estudios.ver')
            ->middleware('can:constancias.generar-estudios');
        Route::post('/subir-firmada/{inscripcion}', [App\Http\Controllers\ConstanciaEstudiosController::class, 'subirConstanciaFirmada'])
            ->name('constancias.estudios.subir-firmada')
            ->middleware('can:constancias.generar-estudios');
    });

    // Rutas para constancias de vacante (accesibles para estudiantes inscritos)
    Route::prefix('constancias/vacante')->group(function () {
        Route::get('/generar/{inscripcion}', [App\Http\Controllers\ConstanciaVacanteController::class, 'generarConstancia'])
            ->name('constancias.vacante.generar')
            ->middleware('can:constancias.generar-vacante');
        Route::get('/ver/{constancia}', [App\Http\Controllers\ConstanciaVacanteController::class, 'verConstancia'])
            ->name('constancias.vacante.ver')
            ->middleware('can:constancias.generar-vacante');
        Route::post('/subir-firmada/{inscripcion}', [App\Http\Controllers\ConstanciaVacanteController::class, 'subirConstanciaFirmada'])
            ->name('constancias.vacante.subir-firmada')
            ->middleware('can:constancias.generar-vacante');
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
        Route::get('/inscripciones/reportes-inscripciones', [App\Http\Controllers\InscripcionController::class, 'reportesInscripciones'])->name('inscripciones.reportes.inscripciones')->middleware('can:inscripciones.reports');
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
    
    // Horarios Docentes y Tema desarrollado
    Route::get('/horarios-calendario', [HorarioDocenteController::class, 'calendario'])->name('horarios.calendario');
    // Esta ruta ya existe y apunta a registrarTema
    Route::post('/docente/tema-desarrollado', [AsistenciaDocenteController::class, 'registrarTema'])->name('docente.tema-guardar');
    
    // NUEVO: Ruta para actualizar el tema desarrollado de una asistencia existente
    Route::post('/asistencia-docente/actualizar-tema', [AsistenciaDocenteController::class, 'actualizarTemaDesarrollado'])->name('asistencia-docente.actualizar-tema');

    // Pagos Docentes
    Route::prefix('pagos-docentes')->group(function () {
        Route::get('/', [App\Http\Controllers\PagoDocenteController::class, 'index'])->name('pagos-docentes.index');
        Route::get('/crear', [App\Http\Controllers\PagoDocenteController::class, 'create'])->name('pagos-docentes.create');
        Route::post('/', [App\Http\Controllers\PagoDocenteController::class, 'store'])->name('pagos-docentes.store');
        Route::get('/{id}/editar', [App\Http\Controllers\PagoDocenteController::class, 'edit'])->name('pagos-docentes.edit');
        Route::put('/{pagoDocente}', [PagoDocenteController::class, 'update'])->name('pagos-docentes.update');
        Route::delete('/{id}', [App\Http\Controllers\PagoDocenteController::class, 'destroy'])->name('pagos-docentes.destroy');
    });

    // Cursos
    Route::prefix('cursos')->group(function () {
        Route::get('/', [App\Http\Controllers\CursoController::class, 'index'])->name('cursos.index');
        Route::get('/crear', [App\Http\Controllers\CursoController::class, 'create'])->name('cursos.create');
        Route::post('/', [App\Http\Controllers\CursoController::class, 'store'])->name('cursos.store');
        Route::get('/{curso}/editar', [App\Http\Controllers\CursoController::class, 'edit'])->name('cursos.edit');
        Route::put('/{curso}', [App\Http\Controllers\CursoController::class, 'update'])->name('cursos.update');
        Route::delete('/{curso}', [App\Http\Controllers\CursoController::class, 'destroy'])->name('cursos.destroy');

        // Ruta para alternar estado (activar/desactivar)
        Route::put('/{id}/toggle', [App\Http\Controllers\CursoController::class, 'toggle'])->name('cursos.toggle');
    });
        // Materiales Académicos
        Route::prefix('materiales-academicos')->name('materiales-academicos.')->group(function () {
            Route::get('/', [MaterialAcademicoController::class, 'index'])
                ->name('index')
                ->middleware('can:material-academico.ver');
        
            Route::get('/create', [MaterialAcademicoController::class, 'create'])
                ->name('crear')
                ->middleware('can:material-academico.crear');
        
            Route::post('/', [MaterialAcademicoController::class, 'store'])
                ->name('store')
                ->middleware('can:material-academico.crear');
        
            Route::get('/{materialAcademico}', [MaterialAcademicoController::class, 'show'])
                ->name('show')
                ->middleware('can:material-academico.ver');
        
            Route::get('/{materialAcademico}/edit', [MaterialAcademicoController::class, 'edit'])
                ->name('edit')
                ->middleware('can:material-academico.editar');
        
            Route::put('/{materialAcademico}', [MaterialAcademicoController::class, 'update'])
                ->name('update')
                ->middleware('can:material-academico.editar');
        
            Route::delete('/{materialAcademico}', [MaterialAcademicoController::class, 'destroy'])
                ->name('destroy')
                ->middleware('can:material-academico.eliminar');
        });
        // Reportes Financieros
        Route::prefix('reportes/financieros')->name('reportes.financieros.')->group(function () {
            Route::get('/', [App\Http\Controllers\ReportesFinancierosController::class, 'index'])
                ->name('index')
                ->middleware('can:reportes.financieros.ver');

            Route::get('/exportar', [App\Http\Controllers\ReportesFinancierosController::class, 'exportarExcel'])
                ->name('exportar')
                ->middleware('can:reportes.financieros.exportar');

            Route::get('/voucher/{postulacionId}', [App\Http\Controllers\ReportesFinancierosController::class, 'descargarVoucher'])
                ->name('descargar-voucher')
                ->middleware('can:reportes.financieros.ver');
        });

    // Boletines
    Route::prefix('boletines')->name('boletines.')->middleware('auth')->group(function () {
        Route::get('/', [App\Http\Controllers\BoletinController::class, 'index'])->name('index')->middleware('can:boletines.view');
        Route::get('/data', [App\Http\Controllers\BoletinController::class, 'getData'])->name('data')->middleware('can:boletines.view');
        Route::get('/asistentes', [App\Http\Controllers\BoletinController::class, 'getAsistentes'])->name('asistentes')->middleware('can:boletines.view');
        Route::post('/marcar', [App\Http\Controllers\BoletinController::class, 'marcarEntrega'])->name('marcar')->middleware('can:boletines.manage');
        Route::get('/exportar', [App\Http\Controllers\BoletinController::class, 'exportar'])->name('exportar')->middleware('can:boletines.view');
    });

    // Tarjetas Pre Universitario
    Route::get('/tarjetas-preuni', function () {
        return view('tarjetas.index');
    })->name('tarjetas-preuni.index');

    // API para Tarjetas Pre Universitario
    Route::get('/api/tarjetas-preuni', [TarjetasController::class, 'obtenerPostulantes'])->name('api.tarjetas-preuni');
    Route::post('/tarjetas/exportar-pdf', [TarjetasController::class, 'exportarPDF'])->name('tarjetas.exportar-pdf');
    
    // NUEVAS RUTAS PARA GESTIÓN DE EXAMEN
    Route::get('/api/tarjetas/edificio', [TarjetasController::class, 'getEdificioData'])->name('api.tarjetas.edificio');
    Route::post('/api/tarjetas/distribuir-aleatorio', [TarjetasController::class, 'generarDistribucionAleatoria'])->name('api.tarjetas.distribuir');
    Route::post('/api/tarjetas/guardar-docente', [TarjetasController::class, 'guardarDistribucionDocente'])->name('api.tarjetas.guardar-docente');
    
    // CRUD Aulas y Detalle
    Route::get('/api/tarjetas/aula/{id}', [TarjetasController::class, 'getAulaDetalle'])->name('api.tarjetas.aula-detalle');
    Route::post('/api/tarjetas/aula', [TarjetasController::class, 'guardarAula'])->name('api.tarjetas.guardar-aula');
    Route::post('/api/tarjetas/piso', [TarjetasController::class, 'guardarPisoCompleto'])->name('api.tarjetas.guardar-piso');
    Route::delete('/api/tarjetas/aula/{id}', [TarjetasController::class, 'eliminarAula'])->name('api.tarjetas.eliminar-aula');

});

// Agrega el prefijo 'json' para todas las rutas de API
Route::middleware(['auth'])->prefix('json')->group(function () {
    // Rutas para filtros
    Route::prefix('filters')->group(function () {
        Route::get('/ciclos', [App\Http\Controllers\Api\FilterController::class, 'getCiclos'])->name('api.filters.ciclos');
        Route::get('/carreras', [App\Http\Controllers\Api\FilterController::class, 'getCarreras'])->name('api.filters.carreras');
    });

    
    // ==========================================
    // RUTAS PARA GESTIÓN DE SESIÓN (NUEVO)
    // ==========================================
    Route::get('/session/verify', function() {
        return response()->json([
            'valid' => auth()->check(),
            'timestamp' => now(),
            'user_id' => auth()->id()
        ]);
    });
    
    Route::post('/session/renew', function() {
        if (auth()->check()) {
            // Regenerar la sesión para renovarla
            session()->regenerate();
            
            return response()->json([
                'renewed' => true,
                'timestamp' => now(),
                'user_id' => auth()->id(),
                'session_lifetime' => config('session.lifetime') . ' minutes'
            ]);
        }
        
        return response()->json([
            'renewed' => false,
            'error' => 'Sesión no válida'
        ], 401);
    });

    // API Ciclos
    Route::prefix('ciclos')->group(function () {
        Route::get('/', [App\Http\Controllers\Api\CicloController::class, 'index']);
        Route::post('/', [App\Http\Controllers\Api\CicloController::class, 'store']);
        Route::get('/{id}', [App\Http\Controllers\Api\CicloController::class, 'show']);
        Route::put('/{id}', [App\Http\Controllers\Api\CicloController::class, 'update']);
        Route::delete('/{id}', [App\Http\Controllers\Api\CicloController::class, 'destroy']);
        Route::post('/{id}/activar', [App\Http\Controllers\Api\CicloController::class, 'activar']);
        Route::get('/activo/actual', [App\Http\Controllers\Api\CicloController::class, 'cicloActivo']);
        
        // Vacantes por ciclo
        Route::get('/{cicloId}/vacantes', [App\Http\Controllers\Api\CicloVacanteController::class, 'getVacantesByCiclo']);
        Route::post('/{cicloId}/vacantes', [App\Http\Controllers\Api\CicloVacanteController::class, 'saveVacantes']);
        Route::post('/{cicloId}/vacantes/agregar', [App\Http\Controllers\Api\CicloVacanteController::class, 'addVacanteCarrera']);
    });
    
    // API Vacantes
    Route::prefix('vacantes')->group(function () {
        Route::put('/{vacanteId}', [App\Http\Controllers\Api\CicloVacanteController::class, 'updateVacante']);
        Route::delete('/{vacanteId}', [App\Http\Controllers\Api\CicloVacanteController::class, 'deleteVacante']);
        Route::get('/resumen', [App\Http\Controllers\Api\CicloVacanteController::class, 'getResumenVacantes']);
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
        Route::get('/por-carrera', [App\Http\Controllers\Api\TurnoController::class, 'porCarrera']);
        Route::get('/{id}', [App\Http\Controllers\Api\TurnoController::class, 'show']);
        Route::put('/{id}', [App\Http\Controllers\Api\TurnoController::class, 'update']);
        Route::delete('/{id}', [App\Http\Controllers\Api\TurnoController::class, 'destroy']);
        Route::patch('/{id}/status', [App\Http\Controllers\Api\TurnoController::class, 'changeStatus']);
        Route::get('/activos/lista', [App\Http\Controllers\Api\TurnoController::class, 'listaActivos']);
    });

    // API Postulaciones
    Route::prefix('postulaciones')->group(function () {
        Route::get('/', [PostulacionController::class, 'listar']);
        Route::get('/stats', [PostulacionController::class, 'getStats']);
        Route::get('/mi-postulacion-actual', [PostulacionController::class, 'miPostulacionActual']);
        Route::post('/crear', [PostulacionController::class, 'crearDesdeAdmin']); // Nueva ruta para crear desde admin
        Route::get('/{id}', [PostulacionController::class, 'show']);
        Route::get('/{id}/editar-aprobada', [PostulacionController::class, 'editarAprobada']);
        Route::put('/{id}/actualizar-aprobada', [PostulacionController::class, 'actualizarAprobada']);
        Route::post('/{id}/verificar-documentos', [PostulacionController::class, 'verificarDocumentos']);
        Route::post('/{id}/verificar-pago', [PostulacionController::class, 'verificarPago']);
        Route::post('/{id}/aprobar', [PostulacionController::class, 'aprobar']);
        Route::post('/{id}/rechazar', [PostulacionController::class, 'rechazar']);
        Route::post('/{id}/observar', [PostulacionController::class, 'observar']);
        Route::post('/{id}/actualizar-documentos', [PostulacionController::class, 'actualizarDocumentos']);
        Route::delete('/{id}', [PostulacionController::class, 'destroy']);
    });

    // API Inscripciones Estudiantes
    Route::prefix('inscripciones-estudiante')->group(function () {
        Route::get('/ciclo-activo', [App\Http\Controllers\Api\InscripcionEstudianteController::class, 'getCicloActivo']);
        Route::get('/verificar', [App\Http\Controllers\Api\InscripcionEstudianteController::class, 'verificarInscripcion']);
        Route::post('/registrar', [App\Http\Controllers\Api\InscripcionEstudianteController::class, 'registrarInscripcion']);
        Route::get('/departamentos', [App\Http\Controllers\Api\InscripcionEstudianteController::class, 'getDepartamentos']);
        Route::get('/provincias/{departamento}', [App\Http\Controllers\Api\InscripcionEstudianteController::class, 'getProvincias']);
        Route::get('/distritos/{departamento}/{provincia}', [App\Http\Controllers\Api\InscripcionEstudianteController::class, 'getDistritos']);
        Route::post('/buscar-colegios', [App\Http\Controllers\Api\InscripcionEstudianteController::class, 'buscarColegios']);
    });
    
    // API Aulas
    Route::prefix('aulas')->group(function () {
        Route::get('/disponibles', [App\Http\Controllers\Api\AulaController::class, 'disponibles']);
        Route::get('/', [App\Http\Controllers\Api\AulaController::class, 'index']);
        Route::post('/', [App\Http\Controllers\Api\AulaController::class, 'store']);
        Route::get('/{id}', [App\Http\Controllers\Api\AulaController::class, 'show']);
        Route::put('/{id}', [App\Http\Controllers\Api\AulaController::class, 'update']);
        Route::delete('/{id}', [App\Http\Controllers\Api\AulaController::class, 'destroy']);
        Route::patch('/{id}/status', [App\Http\Controllers\Api\AulaController::class, 'changeStatus']);
        Route::get('/disponibles/capacidad/{capacidad}', [App\Http\Controllers\Api\AulaController::class, 'porCapacidad']);
        Route::get('/tipo/{tipo}', [App\Http\Controllers\Api\AulaController::class, 'porTipo']);
    });
    
    // API Carnets
    Route::prefix('carnets')->group(function () {
        Route::get('/', [CarnetController::class, 'listar']);
        Route::post('/generar-masivo', [CarnetController::class, 'generarMasivo']);
        Route::post('/generar-individual', [CarnetController::class, 'generarIndividual']);
        Route::post('/marcar-impresos', [CarnetController::class, 'marcarImpresos']);
        Route::post('/{id}/cambiar-estado', [CarnetController::class, 'cambiarEstado']);
        Route::get('/{id}', [CarnetController::class, 'show']);
        Route::put('/{id}', [CarnetController::class, 'update']);
        Route::delete('/{id}', [CarnetController::class, 'destroy']);
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
        
        // Nueva funcionalidad: Grilla Visual
        Route::get('/grilla', [App\Http\Controllers\HorarioDocenteController::class, 'grid'])->name('horarios-docentes.grid');
        Route::post('/bulk-store', [App\Http\Controllers\HorarioDocenteController::class, 'bulkStore'])->name('horarios-docentes.bulk-store');
        Route::get('/get-schedules', [App\Http\Controllers\HorarioDocenteController::class, 'getSchedules'])->name('horarios-docentes.get-schedules');
        Route::get('/export-pdf', [App\Http\Controllers\HorarioDocenteController::class, 'exportPDF'])->name('horarios-docentes.export-pdf');
    });
});

Route::get('api/consulta/{dni}', [App\Http\Controllers\ApiProxyController::class, 'consultaDNI']);

// Rutas para gestión de constancias
Route::middleware('auth')->group(function () {
    Route::get('/constancias', [App\Http\Controllers\ConstanciaController::class, 'index'])
        ->name('constancias.index');
    Route::get('/constancias/estadisticas', [App\Http\Controllers\ConstanciaController::class, 'estadisticas'])
        ->name('constancias.estadisticas');
    Route::get('/constancias/estudiante/{estudiante}', [App\Http\Controllers\ConstanciaController::class, 'getByEstudiante'])
        ->name('constancias.by-estudiante');
    Route::delete('/constancias/{constancia}', [App\Http\Controllers\ConstanciaController::class, 'eliminar'])
        ->name('constancias.eliminar')
        ->middleware('can:constancias.eliminar');
});

// Rutas públicas para validación de constancias
Route::get('/constancias/validar/{codigo}', [App\Http\Controllers\ConstanciaEstudiosController::class, 'validarConstancia'])
    ->name('constancias.validar');

// API para obtener inscripciones disponibles para constancias
Route::middleware('auth')->group(function () {
        Route::get('/json/constancias/inscripciones-disponibles', [App\Http\Controllers\ConstanciaController::class, 'getInscripcionesDisponibles'])
        ->name('json.inscripciones');
    Route::get('/json/ciclos-disponibles', [App\Http\Controllers\ConstanciaController::class, 'getCiclosDisponibles'])
        ->name('json.ciclos-disponibles');
});

// ==========================================
// RESULTADOS DE EXÁMENES - RUTAS
// ==========================================

// Rutas públicas para ver resultados
Route::get('/resultados-examenes', [App\Http\Controllers\ResultadoExamenController::class, 'publicIndex'])
    ->name('resultados-examenes.public');
Route::get('/resultados-examenes/{id}/ver', [App\Http\Controllers\ResultadoExamenController::class, 'view'])
    ->name('resultados-examenes.view');
Route::get('/resultados-examenes/{id}/descargar', [App\Http\Controllers\ResultadoExamenController::class, 'download'])
    ->name('resultados-examenes.download');

// Rutas protegidas para administración
Route::prefix('admin/resultados-examenes')->middleware(['auth'])->name('resultados-examenes.')->group(function () {
    Route::get('/', [App\Http\Controllers\ResultadoExamenController::class, 'index'])
        ->name('index')
        ->middleware('can:resultados-examenes.view');
    
    Route::get('/crear', [App\Http\Controllers\ResultadoExamenController::class, 'create'])
        ->name('create')
        ->middleware('can:resultados-examenes.create');
    
    Route::post('/', [App\Http\Controllers\ResultadoExamenController::class, 'store'])
        ->name('store')
        ->middleware('can:resultados-examenes.create');
    
    Route::get('/{id}/editar', [App\Http\Controllers\ResultadoExamenController::class, 'edit'])
        ->name('edit')
        ->middleware('can:resultados-examenes.edit');
    
    Route::put('/{id}', [App\Http\Controllers\ResultadoExamenController::class, 'update'])
        ->name('update')
        ->middleware('can:resultados-examenes.edit');
    
    Route::delete('/{id}', [App\Http\Controllers\ResultadoExamenController::class, 'destroy'])
        ->name('destroy')
        ->middleware('can:resultados-examenes.delete');
    
    Route::patch('/{id}/toggle-visibility', [App\Http\Controllers\ResultadoExamenController::class, 'toggleVisibility'])
        ->name('toggle-visibility')
        ->middleware('can:resultados-examenes.publish');
});
