<?php

namespace App\Http\Controllers;

use App\Models\Carnet;
use App\Models\Ciclo;
use App\Models\Carrera;
use App\Models\Turno;
use App\Models\Aula;
use App\Models\Inscripcion;
use App\Models\Postulacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Barryvdh\DomPDF\Facade\Pdf;

class CarnetController extends Controller
{
    /**
     * Mostrar lista de carnets
     */
    public function index()
    {
        if (!Auth::user()->hasPermission('carnets.view')) {
            abort(403, 'No tienes permisos para ver carnets');
        }

        $ciclos = Ciclo::orderBy('fecha_inicio', 'desc')->get();
        $carreras = Carrera::where('estado', true)->orderBy('nombre')->get();
        $turnos = Turno::where('estado', true)->orderBy('nombre')->get();
        $aulas = Aula::where('estado', true)->orderBy('nombre')->get();

        return view('carnets.index', compact('ciclos', 'carreras', 'turnos', 'aulas'));
    }

    /**
     * Obtener lista de carnets para DataTables
     */
    public function listar(Request $request)
    {
        if (!Auth::user()->hasPermission('carnets.view')) {
            return response()->json(['error' => 'Sin permisos'], 403);
        }

        try {
            $query = Carnet::with(['estudiante', 'ciclo', 'carrera', 'turno', 'aula']);

            // Filtros
            if ($request->ciclo_id) {
                $query->where('ciclo_id', $request->ciclo_id);
            }
            if ($request->carrera_id) {
                $query->where('carrera_id', $request->carrera_id);
            }
            if ($request->turno_id) {
                $query->where('turno_id', $request->turno_id);
            }
            if ($request->aula_id) {
                $query->where('aula_id', $request->aula_id);
            }
            if ($request->estado) {
                $query->where('estado', $request->estado);
            }
            if ($request->impreso !== null) {
                $query->where('impreso', $request->impreso == '1');
            }

            $carnets = $query->orderBy('created_at', 'desc')->get();

            $data = $carnets->map(function ($carnet) {
                return $this->formatCarnetData($carnet);
            });

            return response()->json([
                'success' => true,
                'data' => $data
            ]);

        } catch (
Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar carnets: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        if (!Auth::user()->hasPermission('carnets.view')) {
            return response()->json(['error' => 'Sin permisos'], 403);
        }

        try {
            $carnet = Carnet::with(['estudiante', 'ciclo', 'carrera', 'turno', 'aula'])->findOrFail($id);
            return response()->json([
                'success' => true,
                'data' => $this->formatCarnetData($carnet)
            ]);
        } catch (
Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Carnet no encontrado.'
            ], 404);
        }
    }

    private function formatCarnetData($carnet)
    {
        return [
            'id' => $carnet->id,
            'codigo' => $carnet->codigo_carnet,
            'estudiante' => $carnet->nombre_completo,
            'dni' => $carnet->estudiante->numero_documento ?? 'N/A',
            'ciclo' => $carnet->ciclo->nombre,
            'carrera' => $carnet->carrera->nombre,
            'turno' => $carnet->turno->nombre,
            'aula' => $carnet->aula->nombre ?? 'Sin asignar',
            'fecha_emision' => $carnet->fecha_emision->format('d/m/Y'),
            'fecha_vencimiento' => $carnet->fecha_vencimiento->format('d/m/Y'),
            'estado' => $carnet->estado,
            'impreso' => $carnet->impreso,
            'fecha_impresion' => $carnet->fecha_impresion ? $carnet->fecha_impresion->format('d/m/Y H:i') : null,
            'tiene_foto' => !empty($carnet->foto_path),
            'foto_path' => $carnet->foto_path,
            'actions' => $this->generarAcciones($carnet)
        ];
    }

    /**
     * Generar carnets masivamente
     */
    public function generarMasivo(Request $request)
    {
        if (!Auth::user()->hasPermission('carnets.generate')) {
            return response()->json(['error' => 'Sin permisos'], 403);
        }

        $request->validate([
            'ciclo_id' => 'required|exists:ciclos,id',
            'carrera_id' => 'nullable|exists:carreras,id',
            'turno_id' => 'nullable|exists:turnos,id',
            'aula_id' => 'nullable|exists:aulas,id',
            'fecha_vencimiento' => 'required|date|after:today'
        ]);

        DB::beginTransaction();
        
        try {
            // Buscar inscripciones que coincidan con los filtros
            $query = Inscripcion::where('ciclo_id', $request->ciclo_id)
                ->where('estado_inscripcion', 'activo')
                ->whereHas('estudiante.roles', function($q) {
                    $q->where('nombre', 'estudiante');
                });

            if ($request->carrera_id) {
                $query->where('carrera_id', $request->carrera_id);
            }
            if ($request->turno_id) {
                $query->where('turno_id', $request->turno_id);
            }
            if ($request->aula_id) {
                $query->where('aula_id', $request->aula_id);
            }

            $inscripciones = $query->get();
            $carnetsGenerados = 0;
            $carnetsExistentes = 0;

            foreach ($inscripciones as $inscripcion) {
                // Verificar si ya existe un carnet para este estudiante en este ciclo
                $carnetExistente = Carnet::where('estudiante_id', $inscripcion->estudiante_id)
                    ->where('ciclo_id', $inscripcion->ciclo_id)
                    ->first();

                if ($carnetExistente) {
                    $carnetsExistentes++;
                    continue;
                }

                // Buscar la postulación para obtener la foto
                $postulacion = Postulacion::where('estudiante_id', $inscripcion->estudiante_id)
                    ->where('ciclo_id', $inscripcion->ciclo_id)
                    ->first();

                // Generar código QR
                $codigoCarnet = Carnet::generarCodigo($inscripcion->ciclo_id, $inscripcion->carrera_id);
                $qrContent = json_encode([
                    'codigo' => $codigoCarnet,
                    'dni' => $inscripcion->estudiante->numero_documento,
                    'estudiante' => $inscripcion->estudiante->nombre . ' ' . $inscripcion->estudiante->apellido_paterno
                ]);

                // Crear el carnet
                $carnet = Carnet::create([
                    'codigo_carnet' => $codigoCarnet,
                    'estudiante_id' => $inscripcion->estudiante_id,
                    'ciclo_id' => $inscripcion->ciclo_id,
                    'carrera_id' => $inscripcion->carrera_id,
                    'turno_id' => $inscripcion->turno_id,
                    'aula_id' => $inscripcion->aula_id,
                    'tipo_carnet' => 'estudiante',
                    'modalidad' => $inscripcion->tipo_inscripcion == 'postulante' ? 'presencial' : 'reforzamiento',
                    'grupo' => $inscripcion->aula ? $inscripcion->aula->nombre : null,
                    'fecha_emision' => Carbon::now(),
                    'fecha_vencimiento' => $request->fecha_vencimiento,
                    'foto_path' => $postulacion ? $postulacion->foto_path : null,
                    'estado' => 'activo'
                ]);

                // Generar y guardar QR
                $qrPath = $this->generarQR($carnet->id, $qrContent);
                $carnet->qr_code = $qrPath;
                $carnet->save();

                $carnetsGenerados++;
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Se generaron {$carnetsGenerados} carnets exitosamente" . 
                           ($carnetsExistentes > 0 ? ". {$carnetsExistentes} estudiantes ya tenían carnet." : ""),
                'generados' => $carnetsGenerados,
                'existentes' => $carnetsExistentes
            ]);

        } catch (
Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al generar carnets: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generar carnet individual
     */
    public function generarIndividual(Request $request)
    {
        if (!Auth::user()->hasPermission('carnets.create')) {
            return response()->json(['error' => 'Sin permisos'], 403);
        }

        $request->validate([
            'estudiante_id' => 'required|exists:users,id',
            'ciclo_id' => 'required|exists:ciclos,id',
            'carrera_id' => 'required|exists:carreras,id',
            'turno_id' => 'required|exists:turnos,id',
            'aula_id' => 'nullable|exists:aulas,id',
            'fecha_vencimiento' => 'required|date|after:today'
        ]);

        DB::beginTransaction();
        
        try {
            // Verificar si ya existe un carnet
            $carnetExistente = Carnet::where('estudiante_id', $request->estudiante_id)
                ->where('ciclo_id', $request->ciclo_id)
                ->first();

            if ($carnetExistente) {
                return response()->json([
                    'success' => false,
                    'message' => 'El estudiante ya tiene un carnet para este ciclo'
                ], 400);
            }

            // Buscar la postulación para obtener la foto
            $postulacion = Postulacion::where('estudiante_id', $request->estudiante_id)
                ->where('ciclo_id', $request->ciclo_id)
                ->first();

            // Generar código
            $codigoCarnet = Carnet::generarCodigo($request->ciclo_id, $request->carrera_id);

            // Crear el carnet
            $carnet = Carnet::create([
                'codigo_carnet' => $codigoCarnet,
                'estudiante_id' => $request->estudiante_id,
                'ciclo_id' => $request->ciclo_id,
                'carrera_id' => $request->carrera_id,
                'turno_id' => $request->turno_id,
                'aula_id' => $request->aula_id,
                'tipo_carnet' => 'estudiante',
                'modalidad' => $request->modalidad ?? 'presencial',
                'grupo' => $request->grupo,
                'fecha_emision' => Carbon::now(),
                'fecha_vencimiento' => $request->fecha_vencimiento,
                'foto_path' => $postulacion ? $postulacion->foto_path : null,
                'estado' => 'activo',
                'observaciones' => $request->observaciones
            ]);

            // Generar QR
            $qrContent = json_encode([
                'codigo' => $codigoCarnet,
                'dni' => $carnet->estudiante->numero_documento,
                'estudiante' => $carnet->nombre_completo
            ]);
            
            $qrPath = $this->generarQR($carnet->id, $qrContent);
            $carnet->qr_code = $qrPath;
            $carnet->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Carnet generado exitosamente',
                'carnet' => $carnet
            ]);

        } catch (
Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al generar carnet: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Exportar carnets a PDF para impresión
     */
    public function exportarPDF(Request $request)
    {
        if (!Auth::user()->hasPermission('carnets.export')) {
            abort(403, 'Sin permisos para exportar');
        }

        $carnetIds = $request->carnets ?? [];
        
        if (empty($carnetIds)) {
            return back()->with('error', 'Debe seleccionar al menos un carnet');
        }

        $carnets = Carnet::with(['estudiante', 'ciclo', 'carrera', 'turno', 'aula'])
            ->whereIn('id', $carnetIds)
            ->get();

        // Preparar datos para la vista
        $carnetsData = $carnets->map(function($carnet) {
            // Obtener la foto del estudiante y código de postulante
            $foto = null;
            $codigoPostulante = '00000000';
            
            // Buscar postulación para obtener foto y código
            $postulacion = \App\Models\Postulacion::where('estudiante_id', $carnet->estudiante_id)
                ->where('ciclo_id', $carnet->ciclo_id)
                ->first();
                
            if ($postulacion) {
                // Obtener código del postulante desde la tabla postulaciones
                $codigoPostulante = $postulacion->codigo_postulante ?? '00000000';
                
                // Obtener foto
                if ($carnet->foto) {
                    $foto = 'data:image/jpeg;base64,' . base64_encode(file_get_contents(storage_path('app/public/' . $carnet->foto)));
                } elseif ($postulacion->foto) {
                    $fotoPath = storage_path('app/public/' . $postulacion->foto);
                    if (file_exists($fotoPath)) {
                        $foto = 'data:image/jpeg;base64,' . base64_encode(file_get_contents($fotoPath));
                    }
                }
            }

            // Generar QR si no existe
            $qrCode = null;
            if ($carnet->qr_code) {
                $qrPath = storage_path('app/public/' . $carnet->qr_code);
                if (file_exists($qrPath)) {
                    $qrCode = 'data:image/png;base64,' . base64_encode(file_get_contents($qrPath));
                }
            }

            // Cargar fondo del carnet
            $fondoPath = public_path('images/fondocarnet_postulante.jpg');
            $fondo = null;
            if (file_exists($fondoPath)) {
                $fondo = 'data:image/jpeg;base64,' . base64_encode(file_get_contents($fondoPath));
            }

            return [
                'id' => $carnet->id,
                'codigo' => $carnet->codigo_carnet,
                'codigo_postulante' => $codigoPostulante,
                'estudiante_id' => str_pad($carnet->estudiante_id, 8, '0', STR_PAD_LEFT),
                'nombre_completo' => strtoupper($carnet->nombre_completo),
                'dni' => $carnet->estudiante->numero_documento,
                'carrera' => strtoupper($carnet->carrera->nombre),
                'ciclo' => $carnet->ciclo->nombre,
                'turno' => $carnet->turno->nombre,
                'grupo' => $carnet->grupo ?? $carnet->aula->nombre ?? '',
                'modalidad' => strtoupper($carnet->modalidad ?? 'PRESENCIAL'),
                'fecha_vencimiento' => $carnet->fecha_vencimiento->format('d/m/Y'),
                'foto' => $foto,
                'qr_code' => $qrCode,
                'fondo' => $fondo,
                'fecha_impresion' => Carbon::now()->format('d/m/Y H:i')
            ];
        });

        // Configurar PDF para impresora Primacy 2
        $pdf = PDF::loadView('carnets.pdf', ['carnets' => $carnetsData])
            ->setPaper([0, 0, 153.01, 242.65], 'portrait'); // Tamaño de tarjeta CR80 (53.98mm x 85.6mm) en vertical

        return $pdf->download('carnets_' . date('YmdHis') . '.pdf');
    }

    /**
     * Marcar carnets como impresos
     */
    public function marcarImpresos(Request $request)
    {
        if (!Auth::user()->hasPermission('carnets.mark_printed')) {
            return response()->json(['error' => 'Sin permisos'], 403);
        }

        $carnetIds = $request->carnets ?? [];
        
        if (empty($carnetIds)) {
            return response()->json([
                'success' => false,
                'message' => 'Debe seleccionar al menos un carnet'
            ], 400);
        }

        try {
            $updated = Carnet::whereIn('id', $carnetIds)
                ->update([
                    'impreso' => true,
                    'fecha_impresion' => Carbon::now(),
                    'impreso_por' => Auth::id()
                ]);

            return response()->json([
                'success' => true,
                'message' => "{$updated} carnets marcados como impresos"
            ]);

        } catch (
Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar carnets: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cambiar estado de un carnet
     */
    public function cambiarEstado(Request $request, $id)
    {
        if (!Auth::user()->hasPermission('carnets.manage_status')) {
            return response()->json(['error' => 'Sin permisos'], 403);
        }

        $request->validate([
            'estado' => 'required|in:activo,inactivo,vencido,anulado',
            'motivo' => 'required_if:estado,anulado|string|max:255'
        ]);

        try {
            $carnet = Carnet::findOrFail($id);
            $estadoAnterior = $carnet->estado;

            $carnet->estado = $request->estado;
            
            if ($request->estado == 'anulado') {
                $carnet->observaciones = ($carnet->observaciones ? $carnet->observaciones . "\n" : "") . 
                    "Anulado el " . date('d/m/Y H:i') . " por " . Auth::user()->nombre . 
                    ". Motivo: " . $request->motivo;
            }
            
            $carnet->save();

            return response()->json([
                'success' => true,
                'message' => "Estado cambiado de {$estadoAnterior} a {$request->estado}"
            ]);

        } catch (
Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cambiar estado: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        if (!Auth::user()->hasPermission('carnets.delete')) {
            return response()->json(['error' => 'Sin permisos'], 403);
        }

        try {
            $carnet = Carnet::findOrFail($id);
            
            // Eliminar QR asociado si existe
            if ($carnet->qr_code) {
                Storage::disk('public')->delete($carnet->qr_code);
            }

            $carnet->delete();

            return response()->json([
                'success' => true,
                'message' => 'Carnet eliminado exitosamente.'
            ]);
        } catch (
Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el carnet.'
            ], 500);
        }
    }

    /**
     * Generar código QR
     */
    private function generarQR($carnetId, $content)
    {
        try {
            $qrCode = QrCode::format('png')
                ->size(200)
                ->margin(1)
                ->generate($content);
            
            $filename = 'qr_carnet_' . $carnetId . '.png';
            $path = 'carnets/qr/' . $filename;
            
            Storage::disk('public')->put($path, $qrCode);
            
            return $path;
        } catch (
Exception $e) {
            // Si falla la generación del QR, continuar sin él
            return null;
        }
    }

    /**
     * Generar acciones para la tabla
     */
    private function generarAcciones($carnet)
    {
        $actions = [];
        $user = Auth::user();

        // Ver detalle
        $actions[] = '<button class="btn btn-sm btn-info view-carnet" data-id="' . $carnet->id . '" title="Ver detalle">
            <i class="uil uil-eye"></i>
        </button>';

        // Imprimir
        if ($user->hasPermission('carnets.print')) {
            $actions[] = '<button class="btn btn-sm btn-primary print-carnet" data-id="' . $carnet->id . '" title="Imprimir">
                <i class="uil uil-print"></i>
            </button>';
        }

        // Editar
        if ($user->hasPermission('carnets.edit') && $carnet->estado == 'activo') {
            $actions[] = '<button class="btn btn-sm btn-warning edit-carnet" data-id="' . $carnet->id . '" title="Editar">
                <i class="uil uil-edit"></i>
            </button>';
        }

        // Cambiar estado
        if ($user->hasPermission('carnets.manage_status')) {
            $actions[] = '<button class="btn btn-sm btn-secondary change-status" data-id="' . $carnet->id . '" 
                data-estado="' . $carnet->estado . '" title="Cambiar estado">
                <i class="uil uil-sync"></i>
            </button>';
        }

        // Eliminar
        if ($user->hasPermission('carnets.delete')) {
            $actions[] = '<button class="btn btn-sm btn-danger delete-carnet" data-id="' . $carnet->id . '" title="Eliminar">
                <i class="uil uil-trash-alt"></i>
            </button>';
        }

        return '<div class="btn-group">' . implode(' ', $actions) . '</div>';
    }
}
