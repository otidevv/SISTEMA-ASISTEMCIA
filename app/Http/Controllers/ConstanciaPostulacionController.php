<?php

namespace App\Http\Controllers;

use App\Models\Postulacion;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Carbon\Carbon;

class ConstanciaPostulacionController extends Controller
{
    /**
     * Generar constancia de inscripción en PDF
     */
    public function generarConstancia($postulacionId)
    {
        try {
            $postulacion = Postulacion::with(['estudiante', 'ciclo', 'carrera', 'turno'])
                ->findOrFail($postulacionId);
            
            // Verificar que el usuario tenga permiso para generar la constancia
            if (Auth::id() !== $postulacion->estudiante_id && !Auth::user()->hasRole('admin')) {
                abort(403, 'No tienes permiso para generar esta constancia');
            }
            
            // Marcar que se generó la constancia
            if (!$postulacion->constancia_generada) {
                $postulacion->constancia_generada = true;
                $postulacion->fecha_constancia_generada = now();
                $postulacion->save();
            }
            
            // Usar el código de postulante o el ID si no existe
            $codigoPostulante = $postulacion->codigo_postulante ?: ('TEMP-' . $postulacion->id);
            
            // Preparar datos para la vista
            $data = [
                'postulacion' => $postulacion,
                'estudiante' => $postulacion->estudiante,
                'ciclo' => $postulacion->ciclo,
                'carrera' => $postulacion->carrera,
                'turno' => $postulacion->turno,
                'fecha_generacion' => Carbon::now()->format('d/m/Y H:i'),
                'codigo_postulante' => $codigoPostulante,
                'codigo_verificacion' => md5($postulacion->id . $codigoPostulante)
            ];
            
            // Generar PDF
            $pdf = PDF::loadView('pdf.constancia-postulacion', $data);
            
            // Configurar el PDF
            $pdf->setPaper('A4', 'portrait');
            
            // Descargar el PDF
            return $pdf->download('constancia_postulacion_' . $codigoPostulante . '.pdf');
            
        } catch (\Exception $e) {
            \Log::error('Error al generar constancia: ' . $e->getMessage());
            abort(500, 'Error al generar la constancia. Por favor intente nuevamente.');
        }
    }
    
    /**
     * Subir constancia firmada
     */
    public function subirConstanciaFirmada(Request $request, $postulacionId)
    {
        try {
            $request->validate([
                'documento_constancia' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120' // Max 5MB
            ]);
            
            $postulacion = Postulacion::findOrFail($postulacionId);
            
            // Verificar permisos
            if (Auth::id() !== $postulacion->estudiante_id && !Auth::user()->hasRole('admin')) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permiso para subir esta constancia'
                ], 403);
            }
            
            // Verificar que ya se haya generado la constancia
            if (!$postulacion->constancia_generada) {
                return response()->json([
                    'success' => false,
                    'message' => 'Primero debe generar la constancia antes de subirla firmada'
                ], 400);
            }
            
            // Subir archivo
            if ($request->hasFile('documento_constancia')) {
                // Eliminar documento anterior si existe (verificar ambos campos por compatibilidad)
                if ($postulacion->constancia_firmada_path && Storage::disk('public')->exists($postulacion->constancia_firmada_path)) {
                    Storage::disk('public')->delete($postulacion->constancia_firmada_path);
                }
                // También verificar el campo antiguo por si acaso
                if ($postulacion->documento_constancia && Storage::disk('public')->exists($postulacion->documento_constancia)) {
                    Storage::disk('public')->delete($postulacion->documento_constancia);
                }
                
                // Generar nombre único para el archivo
                $archivo = $request->file('documento_constancia');
                $extension = $archivo->getClientOriginalExtension();
                $codigoPostulante = $postulacion->codigo_postulante ?: $postulacion->id;
                $nombreArchivo = 'constancia_' . $codigoPostulante . '_' . time() . '.' . $extension;
                
                // Guardar nuevo documento
                $path = $archivo->storeAs(
                    'constancias/' . $postulacion->ciclo_id,
                    $nombreArchivo,
                    'public'
                );
                
                // Guardar en ambos campos para mantener compatibilidad
                $postulacion->constancia_firmada_path = $path;  // Campo nuevo
                $postulacion->documento_constancia = $path;     // Campo antiguo (mantener por compatibilidad)
                $postulacion->constancia_firmada = true;
                $postulacion->fecha_constancia_subida = now();
                
                // No cambiamos el estado aquí, se mantiene como está
                // El estado será cambiado por el administrador cuando revise la postulación
                
                $postulacion->save();
                
                return response()->json([
                    'success' => true,
                    'message' => 'Constancia firmada subida exitosamente',
                    'path' => $path
                ]);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'No se pudo subir el archivo'
            ], 400);
            
        } catch (\Exception $e) {
            \Log::error('Error al subir constancia: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar el archivo: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Ver constancia firmada
     */
    public function verConstanciaFirmada($postulacionId)
    {
        try {
            $postulacion = Postulacion::findOrFail($postulacionId);
            
            // Verificar permisos
            if (Auth::id() !== $postulacion->estudiante_id && 
                !Auth::user()->hasRole('admin') && 
                !Auth::user()->hasRole('secretaria')) {
                abort(403, 'No tienes permiso para ver esta constancia');
            }
            
            // Verificar primero el campo nuevo, luego el antiguo
            $pathConstancia = $postulacion->constancia_firmada_path ?: $postulacion->documento_constancia;
            
            if (!$pathConstancia) {
                abort(404, 'No se ha subido la constancia firmada');
            }
            
            // Verificar si el archivo existe
            if (!Storage::disk('public')->exists($pathConstancia)) {
                \Log::error('Archivo de constancia no encontrado: ' . $pathConstancia);
                abort(404, 'El archivo de constancia no se encuentra en el servidor');
            }
            
            // Obtener la ruta completa del archivo
            $rutaCompleta = Storage::disk('public')->path($pathConstancia);
            
            // Determinar el tipo de contenido basado en la extensión
            $extension = pathinfo($pathConstancia, PATHINFO_EXTENSION);
            $contentType = match(strtolower($extension)) {
                'pdf' => 'application/pdf',
                'jpg', 'jpeg' => 'image/jpeg',
                'png' => 'image/png',
                default => 'application/octet-stream'
            };
            
            // Usar el código de postulante o el ID si no existe
            $codigoPostulante = $postulacion->codigo_postulante ?: $postulacion->id;
            
            // Retornar el archivo para visualización
            return response()->file($rutaCompleta, [
                'Content-Type' => $contentType,
                'Content-Disposition' => 'inline; filename="constancia_' . $codigoPostulante . '.' . $extension . '"'
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error al ver constancia firmada: ' . $e->getMessage());
            abort(500, 'Error al procesar la solicitud');
        }
    }
    
    /**
     * Obtener estado de la constancia
     */
    public function estadoConstancia($postulacionId)
    {
        $postulacion = Postulacion::findOrFail($postulacionId);
        
        // Verificar permisos
        if (Auth::id() !== $postulacion->estudiante_id && !Auth::user()->hasRole('admin')) {
            abort(403, 'No tienes permiso para ver este estado');
        }
        
        return response()->json([
            'success' => true,
            'data' => [
                'constancia_generada' => $postulacion->constancia_generada,
                'constancia_firmada' => $postulacion->constancia_firmada,
                'fecha_constancia_generada' => $postulacion->fecha_constancia_generada,
                'fecha_constancia_subida' => $postulacion->fecha_constancia_subida,
                'documento_constancia' => $postulacion->documento_constancia,
                'codigo_postulante' => $postulacion->codigo_postulante
            ]
        ]);
    }
    
    /**
     * Verificar si todos los documentos están completos
     */
    private function verificarDocumentosCompletos($postulacion)
    {
        // Verificar usando los campos nuevos o antiguos
        $tieneVoucher = $postulacion->voucher_path || $postulacion->voucher_pago_path;
        $tieneFoto = $postulacion->foto_path || $postulacion->foto_carnet_path || $postulacion->estudiante->foto_perfil;
        
        return $tieneVoucher &&
               $postulacion->certificado_estudios_path &&
               $postulacion->dni_path &&
               $tieneFoto &&
               $postulacion->constancia_firmada;
    }
    
    /**
     * Dashboard de postulación para estudiante
     */
    public function dashboardPostulacion()
    {
        $user = Auth::user();
        
        // Obtener postulaciones del usuario
        $postulaciones = Postulacion::where('estudiante_id', $user->id)
            ->with(['ciclo', 'carrera', 'turno'])
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Obtener ciclo activo para nueva postulación
        $cicloActivo = \App\Models\Ciclo::where('es_activo', true)->first();
        
        // Verificar si ya tiene una postulación en el ciclo activo
        $tienePostulacionActiva = false;
        if ($cicloActivo) {
            $tienePostulacionActiva = $postulaciones->where('ciclo_id', $cicloActivo->id)->count() > 0;
        }
        
        return view('postulacion.dashboard', compact(
            'postulaciones', 
            'cicloActivo', 
            'tienePostulacionActiva'
        ));
    }
}