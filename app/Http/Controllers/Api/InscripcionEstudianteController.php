<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ciclo;
use App\Models\CicloCarreraVacante;
use App\Models\Carrera;
use App\Models\Turno;
use App\Models\CentroEducativo;
use App\Models\Inscripcion;
use App\Models\Postulacion;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class InscripcionEstudianteController extends Controller
{
    /**
     * Obtener información del ciclo activo y carreras disponibles
     */
    public function getCicloActivo()
    {
        try {
            $cicloActivo = Ciclo::activo()->first();
            
            if (!$cicloActivo) {
                return response()->json([
                    'success' => false,
                    'message' => 'No hay ciclo activo en este momento'
                ]);
            }
            
            // Obtener carreras con vacantes disponibles
            $carreras = CicloCarreraVacante::with('carrera')
                ->where('ciclo_id', $cicloActivo->id)
                ->where('estado', true)
                ->where(function($query) {
                    $query->where('vacantes_total', 0) // Sin límite
                          ->orWhereRaw('vacantes_total > (vacantes_ocupadas + vacantes_reservadas)'); // Con vacantes disponibles
                })
                ->get()
                ->map(function($vacante) {
                    return [
                        'id' => $vacante->carrera_id,
                        'nombre' => $vacante->carrera->nombre,
                        'codigo' => $vacante->carrera->codigo,
                        'vacantes_disponibles' => $vacante->vacantes_total == 0 ? 
                            'Sin límite' : $vacante->vacantes_disponibles,
                        'tiene_vacantes' => $vacante->vacantes_total == 0 || $vacante->vacantes_disponibles > 0
                    ];
                });
            
            // Obtener turnos disponibles
            $turnos = Turno::where('estado', true)
                ->select('id', 'nombre', 'hora_inicio', 'hora_fin')
                ->get();
            
            return response()->json([
                'success' => true,
                'ciclo' => [
                    'id' => $cicloActivo->id,
                    'nombre' => $cicloActivo->nombre,
                    'codigo' => $cicloActivo->codigo,
                    'fecha_inicio' => $cicloActivo->fecha_inicio,
                    'fecha_fin' => $cicloActivo->fecha_fin,
                    'estado' => $cicloActivo->estado
                ],
                'carreras' => $carreras,
                'turnos' => $turnos,
                'tipos_inscripcion' => [
                    ['value' => 'postulante', 'label' => 'Postulante'],
                    ['value' => 'reforzamiento', 'label' => 'Reforzamiento']
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener información: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Obtener departamentos
     */
    public function getDepartamentos()
    {
        try {
            $departamentos = CentroEducativo::getDepartamentos();
            
            return response()->json([
                'success' => true,
                'departamentos' => $departamentos
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener departamentos: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Obtener provincias por departamento
     */
    public function getProvincias($departamento)
    {
        try {
            $provincias = CentroEducativo::getProvincias($departamento);
            
            return response()->json([
                'success' => true,
                'provincias' => $provincias
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener provincias: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Obtener distritos por departamento y provincia
     */
    public function getDistritos($departamento, $provincia)
    {
        try {
            $distritos = CentroEducativo::getDistritos($departamento, $provincia);
            
            return response()->json([
                'success' => true,
                'distritos' => $distritos
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener distritos: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Buscar colegios
     */
    public function buscarColegios(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'departamento' => 'required|string',
                'provincia' => 'required|string',
                'distrito' => 'required|string',
                'termino' => 'nullable|string|min:2'
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }
            
            $colegios = CentroEducativo::buscarColegios(
                $request->departamento,
                $request->provincia,
                $request->distrito,
                $request->termino
            );
            
            return response()->json([
                'success' => true,
                'colegios' => $colegios->map(function($colegio) {
                    return [
                        'id' => $colegio->id,
                        'nombre' => $colegio->cen_edu,
                        'nivel' => $colegio->d_niv_mod,
                        'direccion' => $colegio->dir_cen
                    ];
                })
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al buscar colegios: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Verificar si el estudiante ya está inscrito o tiene postulación en el ciclo actual
     */
    public function verificarInscripcion()
    {
        try {
            $user = Auth::user();
            $cicloActivo = Ciclo::activo()->first();
            
            if (!$cicloActivo) {
                return response()->json([
                    'success' => true,
                    'inscrito' => false,
                    'message' => 'No hay ciclo activo'
                ]);
            }
            
            // Verificar si tiene una postulación pendiente
            $postulacion = Postulacion::where('estudiante_id', $user->id)
                ->where('ciclo_id', $cicloActivo->id)
                ->first();
            
            if ($postulacion) {
                return response()->json([
                    'success' => true,
                    'inscrito' => true,
                    'inscripcion' => [
                        'id' => $postulacion->id,
                        'codigo' => $postulacion->codigo_postulante,
                        'codigo_postulante' => $postulacion->codigo_postulante,
                        'tipo_inscripcion' => $postulacion->tipo_inscripcion,
                        'carrera' => $postulacion->carrera->nombre ?? '',
                        'turno' => $postulacion->turno->nombre ?? '',
                        'estado' => $postulacion->estado,
                        'fecha_inscripcion' => $postulacion->fecha_postulacion,
                        'es_postulacion' => true
                    ]
                ]);
            }
            
            // Verificar si ya está inscrito
            $inscripcion = Inscripcion::where('estudiante_id', $user->id)
                ->where('ciclo_id', $cicloActivo->id)
                ->first();
            
            if ($inscripcion) {
                return response()->json([
                    'success' => true,
                    'inscrito' => true,
                    'inscripcion' => [
                        'id' => $inscripcion->id,
                        'codigo' => $inscripcion->codigo_inscripcion,
                        'codigo_postulante' => $inscripcion->codigo_inscripcion,
                        'tipo_inscripcion' => $inscripcion->tipo_inscripcion,
                        'carrera' => $inscripcion->carrera->nombre ?? '',
                        'turno' => $inscripcion->turno->nombre ?? '',
                        'estado' => $inscripcion->estado_inscripcion,
                        'fecha_inscripcion' => $inscripcion->fecha_inscripcion,
                        'es_postulacion' => false
                    ]
                ]);
            }
            
            return response()->json([
                'success' => true,
                'inscrito' => false
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al verificar inscripción: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Registrar nueva postulación (antes era inscripción directa)
     */
    public function registrarInscripcion(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tipo_inscripcion' => 'required|in:postulante,reforzamiento',
            'carrera_id' => 'required|exists:carreras,id',
            'turno_id' => 'required|exists:turnos,id',
            'centro_educativo_id' => 'required',
            // Documentos obligatorios
            'voucher_pago' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'certificado_estudios' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'carta_compromiso' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'constancia_estudios' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'dni_documento' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'foto_carnet' => 'required|file|mimes:jpg,jpeg,png|max:2048',
            // Datos del voucher
            'numero_recibo' => 'required|string|max:50',
            'fecha_emision_voucher' => 'required|date',
            'monto_matricula' => 'required|numeric|min:0',
            'monto_ensenanza' => 'required|numeric|min:0'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }
        
        DB::beginTransaction();
        
        try {
            $user = Auth::user();
            $cicloActivo = Ciclo::activo()->first();
            
            if (!$cicloActivo) {
                throw new \Exception('No hay ciclo activo para inscripción');
            }
            
            // Verificar si ya tiene una postulación o inscripción
            $postulacionExistente = Postulacion::where('estudiante_id', $user->id)
                ->where('ciclo_id', $cicloActivo->id)
                ->first();
            
            if ($postulacionExistente) {
                throw new \Exception('Ya tienes una postulación en proceso para este ciclo');
            }
            
            $inscripcionExistente = Inscripcion::where('estudiante_id', $user->id)
                ->where('ciclo_id', $cicloActivo->id)
                ->first();
            
            if ($inscripcionExistente) {
                throw new \Exception('Ya estás inscrito en este ciclo');
            }
            
            // NO ocupar vacante aún, solo verificar disponibilidad
            $vacante = CicloCarreraVacante::where('ciclo_id', $cicloActivo->id)
                ->where('carrera_id', $request->carrera_id)
                ->first();
            
            if ($vacante && $vacante->vacantes_total > 0) {
                if ($vacante->vacantes_disponibles <= 0) {
                    throw new \Exception('No hay vacantes disponibles para esta carrera');
                }
                // NO ocupar la vacante hasta que se apruebe la postulación
            }
            
            // Crear directorio para documentos si no existe
            // Usar el ID del usuario si no tiene DNI registrado
            $userIdentifier = $user->dni ?: 'user_' . $user->id;
            $uploadPath = 'inscripciones/' . $cicloActivo->codigo . '/' . $userIdentifier;
            
            // Subir documentos
            $documentPaths = [];
            
            // Voucher de pago
            if ($request->hasFile('voucher_pago')) {
                $documentPaths['voucher_path'] = $request->file('voucher_pago')
                    ->store($uploadPath . '/voucher', 'public');
            }
            
            // Certificado de estudios
            if ($request->hasFile('certificado_estudios')) {
                $documentPaths['certificado_estudios_path'] = $request->file('certificado_estudios')
                    ->store($uploadPath . '/certificados', 'public');
            }
            
            // Carta de compromiso
            if ($request->hasFile('carta_compromiso')) {
                $documentPaths['carta_compromiso_path'] = $request->file('carta_compromiso')
                    ->store($uploadPath . '/carta', 'public');
            }
            
            // Constancia de estudios
            if ($request->hasFile('constancia_estudios')) {
                $documentPaths['constancia_estudios_path'] = $request->file('constancia_estudios')
                    ->store($uploadPath . '/constancia', 'public');
            }
            
            // DNI
            if ($request->hasFile('dni_documento')) {
                $documentPaths['dni_path'] = $request->file('dni_documento')
                    ->store($uploadPath . '/dni', 'public');
            }
            
            // Foto carnet
            if ($request->hasFile('foto_carnet')) {
                $documentPaths['foto_path'] = $request->file('foto_carnet')
                    ->store($uploadPath . '/foto', 'public');
                
                // También guardar como foto de perfil del usuario
                $user->foto_perfil = $documentPaths['foto_path'];
                $user->save();
            }
            
            // Calcular monto total
            $montoTotal = $request->monto_matricula + $request->monto_ensenanza;
            
            // Debug: Ver qué documentos se van a guardar
            \Log::info('Documentos a guardar:', $documentPaths);
            
            // Crear postulación (NO inscripción directa)
            $datosPostulacion = [
                'estudiante_id' => $user->id,
                'ciclo_id' => $cicloActivo->id,
                'carrera_id' => $request->carrera_id,
                'turno_id' => $request->turno_id,
                'tipo_inscripcion' => $request->tipo_inscripcion,
                'centro_educativo_id' => $request->centro_educativo_id,
                'fecha_postulacion' => now(),
                'estado' => 'pendiente', // Estado inicial pendiente de aprobación
                // Datos del voucher
                'numero_recibo' => $request->numero_recibo,
                'fecha_emision_voucher' => $request->fecha_emision_voucher,
                'monto_matricula' => $request->monto_matricula,
                'monto_ensenanza' => $request->monto_ensenanza,
                'monto_total_pagado' => $montoTotal,
                'documentos_verificados' => false, // Pendiente de verificación
                'pago_verificado' => false // Pendiente de verificación
            ];
            
            // Agregar documentos al array
            foreach ($documentPaths as $key => $value) {
                $datosPostulacion[$key] = $value;
            }
            
            \Log::info('Datos completos de postulación:', $datosPostulacion);
            
            $postulacion = Postulacion::create($datosPostulacion);
            
            // Actualizar información del usuario si es necesario
            if ($request->centro_educativo_id && !$user->centro_educativo_id) {
                $user->centro_educativo_id = $request->centro_educativo_id;
                $user->save();
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Postulación enviada exitosamente. Se encuentra pendiente de aprobación.',
                'postulacion' => [
                    'id' => $postulacion->id,
                    'codigo_postulacion' => $postulacion->codigo_postulante,
                    'codigo_postulante' => $postulacion->codigo_postulante,
                    'tipo' => $postulacion->tipo_inscripcion,
                    'carrera' => $postulacion->carrera->nombre,
                    'turno' => $postulacion->turno->nombre,
                    'fecha' => $postulacion->fecha_postulacion,
                    'estado' => 'pendiente'
                ]
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }
}