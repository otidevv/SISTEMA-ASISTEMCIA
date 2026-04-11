<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\PostulanteRegisterController;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\ReniecController;
use App\Http\Controllers\PostulacionUnificadaController;
use App\Http\Controllers\Api\AcademicApiController;
use App\Http\Controllers\Api\TeacherApiController;
use App\Http\Controllers\Api\PostulationApiController;
use App\Http\Controllers\Api\PaymentApiController;
use App\Http\Controllers\Api\AuditApiController;
use App\Http\Controllers\Api\DocumentApiController;
use App\Http\Controllers\Api\CargaHorariaApiController;
use App\Http\Controllers\Api\MailNotificationController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\MaterialAcademicoApiController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Aquí se definen las rutas de la API para la aplicación. Estas rutas 
| son cargadas por el RouteServiceProvider y todas se les asigna el 
| grupo de middleware "api".
|
*/

// --- RUTAS PÚBLICAS ---
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register/postulante', [PostulanteRegisterController::class, 'register'])->name('api.register.postulante');

// Rutas protegidas - Requieren Autenticación (Soportan Token Sanctum y Sesión Web)
Route::middleware(['auth:sanctum,web'])->group(function () {
    
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/profile', [AuthController::class, 'profile']);
    
    // --- SERVICIOS DE DASHBOARD ---
    Route::prefix('dashboard')->group(function () {
        Route::get('/datos-generales', [DashboardController::class, 'getDatosGenerales']);
        Route::get('/anuncios', [DashboardController::class, 'getAnuncios']);
        Route::get('/ultimos-registros', [DashboardController::class, 'getUltimosRegistros']);
        Route::get('/admin/estadisticas-asistencia', [DashboardController::class, 'getEstadisticasAsistencia']);
        Route::get('/admin', [DashboardController::class, 'getDatosAdmin']);
        Route::get('/estudiante', [DashboardController::class, 'getDatosEstudiante']);
        Route::get('/profesor', [DashboardController::class, 'getDatosProfesor']);
        Route::post('/profesor/registrar-tema', [DashboardController::class, 'registrarTemaDesarrollado']);
        Route::get('/profesor/reporte/carga-horaria/{type?}', [DashboardController::class, 'exportWorkloadPdf']);
        Route::get('/profesor/reporte/asistencia', [DashboardController::class, 'exportAttendancePdf']);
        Route::get('/admin/monitoreo-diario', [\App\Http\Controllers\AsistenciaDocenteController::class, 'getDailySchedule']);
    });

    // --- SERVICIOS ACADÉMICOS (ESTUDIANTES) ---
    Route::prefix('academic')->group(function () {
        Route::get('/materials', [AcademicApiController::class, 'getMaterials']);
        Route::get('/exam-results', [AcademicApiController::class, 'getExamResults']);
        Route::get('/eligibility', [AcademicApiController::class, 'getEligibility']);
        Route::get('/report-cards', [AcademicApiController::class, 'getReportCards']);
    });

    // --- SERVICIOS DOCENTES ---
    Route::prefix('teacher')->group(function () {
        Route::get('/schedule', [TeacherApiController::class, 'getMySchedule']);
    });

    Route::prefix('carga-horaria')->group(function () {
        Route::get('/docente/{docente}/ciclo/{ciclo}', [CargaHorariaApiController::class, 'calcular']);
        Route::get('/ciclo/{ciclo}/docentes', [CargaHorariaApiController::class, 'listarDocentes']);
    });

    // --- DOCUMENTOS Y CARNETS ---
    Route::prefix('documents')->group(function () {
        Route::get('/my-carnet', [DocumentApiController::class, 'getMyCarnet']);
        Route::get('/certificates', [DocumentApiController::class, 'getMyCertificates']);
    });

    // --- POSTULACIÓN ---
    Route::prefix('postulation')->group(function () {
        Route::post('/store', [PostulationApiController::class, 'store']);
        Route::get('/status', [PostulationApiController::class, 'status']);
    });

    // --- RENIEC (Internal) ---
    Route::post('/reniec/consultar-multiple', [ReniecController::class, 'consultarMultiple']);

    // --- POSTULACIÓN UNIFICADA (AUXILIAR) ---
    Route::prefix('postulacion-unificada')->group(function () {
        Route::get('/buscar-colegios', [PostulacionUnificadaController::class, 'buscarColegios']);
        Route::post('/buscar-colegios', [PostulacionUnificadaController::class, 'buscarColegios']);
    });

    // --- USUARIOS Y GESTIÓN ---
    Route::post('/user/settings', [\App\Http\Controllers\Api\UserController::class, 'updateSettings']);
    
    // --- BÚSQUEDA Y GESTIÓN ADMIN ---
    Route::middleware('can:users.view')->group(function () {
        Route::get('/admin/estudiantes', [\App\Http\Controllers\AsistenciaController::class, 'getEstudiantesPorFiltros']);
        Route::get('/admin/audit', [AuditApiController::class, 'index']);
    });

    // --- SERVICIOS DOCENTES EXPANDIDOS ---
    Route::prefix('teacher')->group(function () {
        Route::get('/schedule', [TeacherApiController::class, 'getMySchedule']);
        Route::get('/class-students/{horario_id}', [TeacherApiController::class, 'getClassStudents']);
        Route::post('/upload-material', [TeacherApiController::class, 'uploadMaterial']);
    });

    // --- GESTIÓN DE MATERIAL ACADÉMICO ---
    Route::group(['prefix' => 'materiales-academicos'], function () {
        Route::get('/', [MaterialAcademicoApiController::class, 'index']);
        Route::get('/form-data', [MaterialAcademicoApiController::class, 'getFormData']);
        Route::post('/', [MaterialAcademicoApiController::class, 'store']);
        Route::post('/{id}', [MaterialAcademicoApiController::class, 'update']); // Usar POST para update con files por limitaciones de PUT en multipart
        Route::delete('/{id}', [MaterialAcademicoApiController::class, 'destroy']);
    });

    // --- PAGOS ---
    Route::get('/payments/validate/{dni}', [PaymentApiController::class, 'validateByDni']);

    // --- API V1 (Servicios para Gestión Administrativa) ---
    Route::prefix('v1')->group(function () {
        Route::get('/pagos-docentes/ultima-tarifa/{docenteId}', [\App\Http\Controllers\PagoDocenteController::class, 'getUltimaTarifa']);
    });
});

// --- SERVICIOS PÚBLICOS O INTERNOS ---

// Endpoint de monitoreo en tiempo real
Route::get('/ultimos-registros', function (Request $request) {
    $ultimoId = $request->input('ultimo_id', 0);

    $registros = \App\Models\RegistroAsistencia::with('usuario')
        ->where('id', '>', $ultimoId)
        ->orderBy('id', 'asc')
        ->get()
        ->map(function ($registro) {
            return [
                'id' => $registro->id,
                'nro_documento' => $registro->nro_documento,
                'nombre_completo' => $registro->usuario ?
                    $registro->usuario->nombre . ' ' . $registro->usuario->apellido_paterno : null,
                'fecha_hora_formateada' => $registro->fecha_hora->format('d/m/Y H:i:s'),
                'tipo_verificacion_texto' => $registro->tipo_verificacion_texto,
                'foto_url' => $registro->usuario && $registro->usuario->foto_perfil ?
                    asset('storage/' . $registro->usuario->foto_perfil) : null,
                'iniciales' => $registro->usuario ?
                    strtoupper(substr($registro->usuario->nombre, 0, 1)) : null,
            ];
        });

    return response()->json($registros);
});

// Ruta interna para notificaciones (llamada por servicio Node.js)
Route::post('/notificar-asistencia-docente', [MailNotificationController::class, 'notificarAsistenciaDocente']);
// Rutas de Postulación Pública para App Móvil
Route::prefix('public-postulation')->group(function () {
    Route::get('/dependencies', [\App\Http\Controllers\Api\PostulanteRegisterApiController::class, 'getFormDependencies']);
    Route::post('/register', [\App\Http\Controllers\Api\PostulanteRegisterApiController::class, 'registerAndPostulate']);
    
    // Ubigueo (Reutilizando lógica de PostulacionUnificada)
    Route::get('/departamentos', [\App\Http\Controllers\PostulacionUnificadaController::class, 'getDepartamentos']);
    Route::get('/provincias/{departamento}', [\App\Http\Controllers\PostulacionUnificadaController::class, 'getProvincias']);
    Route::get('/distritos/{departamento}/{provincia}', [\App\Http\Controllers\PostulacionUnificadaController::class, 'getDistritos']);
    Route::get('/buscar-colegios', [\App\Http\Controllers\PostulacionUnificadaController::class, 'buscarColegios']);
    Route::get('/available-payments/{dni}', [\App\Http\Controllers\Api\PostulanteRegisterApiController::class, 'getAvailablePayments']);
    Route::post('/validate-payment', [\App\Http\Controllers\Api\PostulanteRegisterApiController::class, 'validatePayment']);
    Route::post('/verify-dni', [\App\Http\Controllers\Api\ReniecController::class, 'consultarMultiple']);
});

// --- REFORZAMIENTO PÚBLICO ---
Route::prefix('public-reforzamiento')->group(function () {
    Route::get('/verify-dni/{dni}', [\App\Http\Controllers\Api\ReforzamientoApiController::class, 'verifyDni']);
    Route::post('/check-payment', [\App\Http\Controllers\Api\ReforzamientoApiController::class, 'checkPayment']);
    Route::post('/register', [\App\Http\Controllers\Api\ReforzamientoApiController::class, 'register']);
    Route::get('/constancia/{id}', [\App\Http\Controllers\Api\ReforzamientoApiController::class, 'generarConstancia']);
    Route::post('/download-pack', [\App\Http\Controllers\Api\ReforzamientoApiController::class, 'generateRegistrationPack']);
    Route::post('/reniec/consultar', [\App\Http\Controllers\Api\ReniecController::class, 'consultarDni']);
});

// --- CHATBOT ASSISTANT ---
Route::get('/assistant/config', [App\Http\Controllers\Api\ChatbotApiController::class, 'getAssistantData']);
Route::post('/assistant/ask', [App\Http\Controllers\Api\ChatbotApiController::class, 'ask']);
