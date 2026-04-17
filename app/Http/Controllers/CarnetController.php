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
            if ($request->entregado !== null) {
                $query->where('entregado', $request->entregado == '1');
            }

            $carnets = $query->orderBy('created_at', 'desc')->get();

            $data = $carnets->map(function ($carnet) {
                return $this->formatCarnetData($carnet);
            });

            return response()->json([
                'success' => true,
                'data' => $data
            ]);

        } catch (\Exception $e) {
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
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Carnet no encontrado.'
            ], 404);
        }
    }

    private function formatCarnetData($carnet)
    {
        // Determinación robusta del aula/grupo
        $aulaDisplay = '';
        if ($carnet->aula) {
            $aulaDisplay = $carnet->aula->nombre;
        } else {
            // Intentar buscar inscripción activa si no tiene aula asignada
            $inscripcion = \App\Models\Inscripcion::where('estudiante_id', $carnet->estudiante_id)
                ->where('ciclo_id', $carnet->ciclo_id)
                ->where('estado_inscripcion', 'activo')
                ->whereNotNull('aula_id')
                ->with('aula')
                ->first();
                
            if ($inscripcion && $inscripcion->aula) {
                $aulaDisplay = $inscripcion->aula->nombre;
            } elseif ($carnet->grupo) {
                $aulaDisplay = $carnet->grupo;
            } else {
                $aulaDisplay = 'Sin asignar';
            }
        }

        // Limpiar emojis y normalizar
        $aulaDisplay = $this->limpiarTexto($aulaDisplay);
        $turnoNombre = $carnet->turno ? $this->limpiarTexto($carnet->turno->nombre) : '';

        // Añadir el turno solo si no está ya en el nombre del aula
        if ($turnoNombre && !str_contains(strtoupper($aulaDisplay), strtoupper($turnoNombre))) {
            $aulaDisplay .= ' ' . $turnoNombre;
        }

        $aulaDisplay = mb_strtoupper($aulaDisplay, 'UTF-8');

        // Obtener el código de postulante o inscripción asociado para este ciclo
        $codigoPostulante = $carnet->codigo_carnet; // Fallback
        
        if ($carnet->modalidad === 'reforzamiento_colegio') {
            $ref = \App\Models\InscripcionReforzamiento::where('estudiante_id', $carnet->estudiante_id)
                ->where('ciclo_id', $carnet->ciclo_id)
                ->first();
            if ($ref) $codigoPostulante = $ref->codigo_reforzamiento;
        } else {
            $post = \App\Models\Postulacion::where('estudiante_id', $carnet->estudiante_id)
                ->where('ciclo_id', $carnet->ciclo_id)
                ->first();
            if ($post) {
                $codigoPostulante = $post->codigo_postulante;
            } else {
                $ins = \App\Models\Inscripcion::where('estudiante_id', $carnet->estudiante_id)
                    ->where('ciclo_id', $carnet->ciclo_id)
                    ->first();
                if ($ins) $codigoPostulante = $ins->codigo_inscripcion;
            }
        }

        return [
            'id' => $carnet->id,
            'codigo' => $codigoPostulante,
            'codigo_carnet' => $carnet->codigo_carnet,
            'estudiante' => $carnet->nombre_completo,
            'dni' => $carnet->estudiante->numero_documento ?? 'N/A',
            'ciclo' => $carnet->ciclo->nombre,
            'carrera' => $carnet->carrera ? $carnet->carrera->nombre : ($carnet->modalidad === 'reforzamiento_colegio' ? 'REFORZAMIENTO' : 'N/A'),
            'turno' => $carnet->turno->nombre,
            'aula' => $aulaDisplay,
            'fecha_emision' => $carnet->fecha_emision->format('d/m/Y'),
            'fecha_vencimiento' => $carnet->fecha_vencimiento->format('d/m/Y'),
            'estado' => $carnet->estado,
            'impreso' => $carnet->impreso,
            'fecha_impresion' => $carnet->fecha_impresion ? $carnet->fecha_impresion->format('d/m/Y H:i') : null,
            'entregado' => $carnet->entregado,
            'fecha_entrega' => $carnet->fecha_entrega ? $carnet->fecha_entrega->format('d/m/Y H:i') : null,
            'estado_entrega' => $carnet->estado_entrega,
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
            $carnetsGenerados = 0;
            $carnetsExistentes = 0;
            $estudiantes = collect();

            // 1. Buscar inscripciones activas
            $queryInscripciones = Inscripcion::where('ciclo_id', $request->ciclo_id)
                ->where('estado_inscripcion', 'activo')
                ->whereHas('estudiante.roles', function($q) {
                    $q->where('nombre', 'estudiante');
                });

            if ($request->carrera_id) {
                $queryInscripciones->where('carrera_id', $request->carrera_id);
            }
            if ($request->turno_id) {
                $queryInscripciones->where('turno_id', $request->turno_id);
            }
            if ($request->aula_id) {
                $queryInscripciones->where('aula_id', $request->aula_id);
            }

            $inscripciones = $queryInscripciones->get();

            // 2. Buscar postulaciones aceptadas
            $queryPostulaciones = Postulacion::where('ciclo_id', $request->ciclo_id)
                ->where('estado', 'aprobado');

            if ($request->carrera_id) {
                $queryPostulaciones->where('carrera_id', $request->carrera_id);
            }

            $postulantes = $queryPostulaciones->get();

            // 3. Buscar inscripciones de Reforzamiento (NUEVO)
            $reforzamiento = \App\Models\InscripcionReforzamiento::where('ciclo_id', $request->ciclo_id)
                ->where('estado_inscripcion', 'validado')
                ->get();

            // PROCESAR INSCRIPCIONES REGULARES
            foreach ($inscripciones as $inscripcion) {
                $carnetExistente = Carnet::where('estudiante_id', $inscripcion->estudiante_id)
                    ->where('ciclo_id', $inscripcion->ciclo_id)
                    ->first();

                if ($carnetExistente) {
                    $carnetsExistentes++;
                    continue;
                }

                $codigoCarnet = Carnet::generarCodigo($inscripcion->ciclo_id, $inscripcion->carrera_id);
                $qrContent = json_encode([
                    'codigo' => $codigoCarnet,
                    'dni' => $inscripcion->estudiante->numero_documento,
                    'estudiante' => $inscripcion->estudiante->nombre . ' ' . $inscripcion->estudiante->apellido_paterno
                ]);

                $carnet = Carnet::create([
                    'codigo_carnet' => $codigoCarnet,
                    'estudiante_id' => $inscripcion->estudiante_id,
                    'ciclo_id' => $inscripcion->ciclo_id,
                    'carrera_id' => $inscripcion->carrera_id,
                    'turno_id' => $inscripcion->turno_id,
                    'aula_id' => $inscripcion->aula_id,
                    'tipo_carnet' => 'estudiante',
                    'modalidad' => $inscripcion->tipo_inscripcion,
                    'grupo' => $inscripcion->aula ? $inscripcion->aula->nombre : null,
                    'fecha_emision' => Carbon::now(),
                    'fecha_vencimiento' => $request->fecha_vencimiento,
                    'foto_path' => $inscripcion->estudiante->foto_perfil,
                    'estado' => 'activo'
                ]);

                $qrPath = $this->generarQR($carnet->id, $qrContent);
                $carnet->qr_code = $qrPath;
                $carnet->save();
                $carnetsGenerados++;
            }

            // PROCESAR POSTULACIONES
            foreach ($postulantes as $postulacion) {
                $carnetExistente = Carnet::where('estudiante_id', $postulacion->estudiante_id)
                    ->where('ciclo_id', $postulacion->ciclo_id)
                    ->first();

                if ($carnetExistente) {
                    $carnetsExistentes++;
                    continue;
                }

                $codigoCarnet = Carnet::generarCodigo($postulacion->ciclo_id, $postulacion->carrera_id);
                $qrContent = json_encode([
                    'codigo' => $codigoCarnet,
                    'dni' => $postulacion->estudiante->numero_documento,
                    'estudiante' => $postulacion->estudiante->nombre . ' ' . $postulacion->estudiante->apellido_paterno
                ]);

                $carnet = Carnet::create([
                    'codigo_carnet' => $codigoCarnet,
                    'estudiante_id' => $postulacion->estudiante_id,
                    'ciclo_id' => $postulacion->ciclo_id,
                    'carrera_id' => $postulacion->carrera_id,
                    'turno_id' => $postulacion->turno_id,
                    'tipo_carnet' => 'postulante',
                    'modalidad' => 'postulante',
                    'grupo' => $this->determinarGrupo($postulacion->carrera->nombre),
                    'fecha_emision' => Carbon::now(),
                    'fecha_vencimiento' => $request->fecha_vencimiento,
                    'foto_path' => $postulacion->foto_path,
                    'estado' => 'activo'
                ]);

                $qrPath = $this->generarQR($carnet->id, $qrContent);
                $carnet->qr_code = $qrPath;
                $carnet->save();
                $carnetsGenerados++;
            }

            // PROCESAR REFORZAMIENTO
            foreach ($reforzamiento as $ref) {
                $carnetExistente = Carnet::where('estudiante_id', $ref->estudiante_id)
                    ->where('ciclo_id', $ref->ciclo_id)
                    ->first();

                if ($carnetExistente) {
                    $carnetsExistentes++;
                    continue;
                }

                $codigoCarnet = Carnet::generarCodigo($ref->ciclo_id, null);
                $qrContent = json_encode([
                    'codigo' => $codigoCarnet,
                    'dni' => $ref->estudiante->numero_documento,
                    'estudiante' => $ref->estudiante->nombre . ' ' . $ref->estudiante->apellido_paterno
                ]);

                $carnet = Carnet::create([
                    'codigo_carnet' => $codigoCarnet,
                    'estudiante_id' => $ref->estudiante_id,
                    'ciclo_id' => $ref->ciclo_id,
                    'carrera_id' => null,
                    'turno_id' => ($ref->turno == 'MAÑANA' ? 1 : 2),
                    'tipo_carnet' => 'estudiante',
                    'modalidad' => 'reforzamiento_colegio',
                    'grupo' => 'REF - ' . $ref->grado,
                    'fecha_emision' => Carbon::now(),
                    'fecha_vencimiento' => $request->fecha_vencimiento,
                    'foto_path' => $ref->foto_path,
                    'estado' => 'activo'
                ]);

                $qrPath = $this->generarQR($carnet->id, $qrContent);
                $carnet->qr_code = $qrPath;
                $carnet->save();
                $carnetsGenerados++;
            }

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => "Proceso terminado. Se generaron {$carnetsGenerados} carnets nuevos."
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
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

        $rules = [
            'estudiante_id' => 'required|exists:users,id',
            'ciclo_id' => 'required|exists:ciclos,id',
            'aula_id' => 'nullable|exists:aulas,id',
            'fecha_vencimiento' => 'required|date|after:today',
            'modalidad' => 'required|string|in:postulante,reforzamiento_colegio'
        ];

        if ($request->input('modalidad') !== 'reforzamiento_colegio') {
            $rules['carrera_id'] = 'required|exists:carreras,id';
            $rules['turno_id'] = 'required|exists:turnos,id';
        }

        $request->validate($rules);

        DB::beginTransaction();
        try {
            $carnetExistente = Carnet::where('estudiante_id', $request->estudiante_id)
                ->where('ciclo_id', $request->ciclo_id)
                ->first();

            if ($carnetExistente) {
                return response()->json(['success' => false, 'message' => 'Ya existe un carnet.'], 400);
            }

            $codigoCarnet = Carnet::generarCodigo($request->ciclo_id, $request->carrera_id);
            $carnet = Carnet::create([
                'codigo_carnet' => $codigoCarnet,
                'estudiante_id' => $request->estudiante_id,
                'ciclo_id' => $request->ciclo_id,
                'carrera_id' => $request->carrera_id,
                'turno_id' => $request->turno_id,
                'aula_id' => $request->aula_id,
                'tipo_carnet' => 'estudiante',
                'modalidad' => $request->modalidad,
                'grupo' => $request->grupo,
                'fecha_emision' => Carbon::now(),
                'fecha_vencimiento' => $request->fecha_vencimiento,
                'estado' => 'activo'
            ]);

            $qrPath = $this->generarQR($carnet->id, json_encode(['codigo' => $codigoCarnet]));
            $carnet->qr_code = $qrPath;
            $carnet->save();

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Carnet generado.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
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
            return back()->with('error', 'Seleccione al menos un carnet');
        }

        $carnets = Carnet::with(['estudiante', 'ciclo', 'carrera', 'turno', 'aula'])
            ->whereIn('id', $carnetIds)
            ->get();

        // Preparar datos para la vista (Dinámico por carnet)
        $carnetsData = $carnets->map(function($carnet) {
            // Diferenciar entre Reforzamiento Colegio y otros para la plantilla
            $tipoTemplate = $carnet->modalidad === 'reforzamiento_colegio' ? 'reforzamiento_colegio' : 'postulante';

            // 1. Intentar plantilla exacta para este tipo y ciclo
            $template = \App\Models\CarnetTemplate::obtenerActiva($tipoTemplate, $carnet->ciclo_id);
            
            // 2. Rescate: Si el ciclo viejo no tiene plantilla, usar la última activa de ese mismo tipo
            if (!$template) {
                $template = \App\Models\CarnetTemplate::where('tipo', $tipoTemplate)->where('activa', true)->latest()->first();
            }

            // 3. Rescate extremo: Usar cualquiera de postulante si no hay nada más
            if (!$template) {
                $template = \App\Models\CarnetTemplate::where('tipo', 'postulante')->where('activa', true)->latest()->first();
            }
            
            $foto = null;
            $codigoPostulante = $carnet->dni;
            $infoAcademica = '';

            if ($carnet->modalidad === 'reforzamiento_colegio') {
                $ref = \App\Models\InscripcionReforzamiento::where('estudiante_id', $carnet->estudiante_id)
                    ->where('ciclo_id', $carnet->ciclo_id)
                    ->first();
                $infoAcademica = $ref ? strtoupper("{$ref->grado}° - {$ref->colegio_procedencia}") : 'REF. COLEGIO';
                $codigoPostulante = $ref ? $ref->codigo_reforzamiento : $carnet->dni;
            } else {
                $post = \App\Models\Postulacion::where('estudiante_id', $carnet->estudiante_id)
                    ->where('ciclo_id', $carnet->ciclo_id)
                    ->first();
                
                if ($post) {
                    $codigoPostulante = $post->codigo_postulante;
                } else {
                    $ins = \App\Models\Inscripcion::where('estudiante_id', $carnet->estudiante_id)
                        ->where('ciclo_id', $carnet->ciclo_id)
                        ->first();
                    $codigoPostulante = $ins ? $ins->codigo_inscripcion : $carnet->codigo_carnet;
                }
                
                $infoAcademica = strtoupper($carnet->carrera->nombre ?? 'ADMISIÓN');
            }

            if ($carnet->foto_path) {
                $path = storage_path('app/public/' . $carnet->foto_path);
                if (file_exists($path)) $foto = 'data:image/jpeg;base64,' . base64_encode(file_get_contents($path));
            }

            $qrCode = null;
            if ($carnet->qr_code) {
                $path = storage_path('app/public/' . $carnet->qr_code);
                if (file_exists($path)) $qrCode = 'data:image/png;base64,' . base64_encode(file_get_contents($path));
            }

            $fondo = null;
            if ($template && $template->fondo_path) {
                $path = storage_path('app/public/' . $template->fondo_path);
                if (file_exists($path)) $fondo = 'data:image/jpeg;base64,' . base64_encode(file_get_contents($path));
            }

            $aulaNombre = $carnet->aula ? $carnet->aula->nombre : ($carnet->grupo ?? '---');
            $turnoNombre = $carnet->turno ? $carnet->turno->nombre : '';
            
            // Limpiar textos para evitar problemas con emojis en DomPDF
            $aulaNombreLimpia = $this->limpiarTexto($aulaNombre);
            $turnoNombreLimpia = $this->limpiarTexto($turnoNombre);
            
            $grupoFinal = $aulaNombreLimpia;
            if ($turnoNombreLimpia && !str_contains(strtoupper($aulaNombreLimpia), strtoupper($turnoNombreLimpia))) {
                $grupoFinal .= ' ' . $turnoNombreLimpia;
            }

            return [
                'id' => $carnet->id,
                'codigo_postulante' => $codigoPostulante,
                'nombre_completo' => $this->limpiarTexto(strtoupper($carnet->nombre_completo)),
                'dni' => $carnet->estudiante->numero_documento ?? 'N/A',
                'carrera' => $this->limpiarTexto($infoAcademica), 
                'grado' => $carnet->modalidad === 'reforzamiento_colegio' && isset($ref) ? "{$ref->grado}°" : '---',
                'colegio' => $carnet->modalidad === 'reforzamiento_colegio' && isset($ref) ? $this->limpiarTexto(strtoupper($ref->colegio_procedencia)) : '---',
                'ciclo' => $this->limpiarTexto($carnet->ciclo->nombre ?? '---'),
                'turno' => $turnoNombreLimpia ?: 'N/A',
                'grupo' => mb_strtoupper($grupoFinal, 'UTF-8'),
                'modalidad' => strtoupper(str_replace('_', ' ', $carnet->modalidad ?? 'PRESENCIAL')),
                'fecha_vencimiento' => $carnet->fecha_vencimiento ? $carnet->fecha_vencimiento->format('d/m/Y') : '---',
                'foto' => $foto,
                'qr_code' => $qrCode,
                'fondo' => $fondo,
                'template' => $template
            ];
        });

        // Usamos la plantilla del primer carnet como base para las dimensiones del papel
        $templateBase = $carnetsData->first()['template'];
        if (!$templateBase) {
            return redirect()->back()->with('error', 'No existe ninguna plantilla de diseño configurada en todo el sistema para este tipo de carnet. Debe crear una desde el diseñador.');
        }

        $pdf = PDF::loadView('carnets.pdf-dynamic', [
            'carnets' => $carnetsData,
            'template' => $templateBase
        ])->setPaper([0, 0, $templateBase->ancho_mm * 2.83465, $templateBase->alto_mm * 2.83465], 'portrait');

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

        } catch (\Exception $e) {
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

        } catch (\Exception $e) {
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
        } catch (\Exception $e) {
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
        } catch (\Exception $e) {
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

    /**
     * Vista para escanear QR y entregar carnets
     */
    public function vistaEscanear()
    {
        if (!Auth::user()->hasPermission('carnets.scan_delivery')) {
            abort(403, 'No tienes permisos para escanear carnets');
        }

        return view('carnets.escanear');
    }

    /**
     * Escanear QR y obtener datos del carnet
     */
    public function escanearQR(Request $request)
    {
        if (!Auth::user()->hasPermission('carnets.scan_delivery')) {
            return response()->json(['error' => 'Sin permisos'], 403);
        }

        $request->validate([
            'codigo_carnet' => 'required|string'
        ]);

        try {
            // Intentar decodificar el QR como JSON (formato nuevo)
            $qrData = json_decode($request->codigo_carnet, true);
            
            // Si es JSON válido, extraer el código
            if (is_array($qrData) && isset($qrData['codigo'])) {
                $codigoCarnet = $qrData['codigo'];
            } else {
                // Si no es JSON, usar el valor directo (formato antiguo)
                $codigoCarnet = $request->codigo_carnet;
            }

            $carnet = Carnet::with(['estudiante', 'ciclo', 'carrera', 'turno', 'aula', 'entregador'])
                ->where('codigo_carnet', $codigoCarnet)
                ->first();

            if (!$carnet) {
                return response()->json([
                    'success' => false,
                    'message' => 'Carnet no encontrado. Verifique el código escaneado.'
                ], 404);
            }

            // Verificar que el carnet esté impreso
            if (!$carnet->impreso) {
                return response()->json([
                    'success' => false,
                    'message' => 'Este carnet aún no ha sido impreso. Debe imprimirse antes de entregarse.'
                ], 400);
            }

            // Verificar si ya fue entregado
            if ($carnet->entregado) {
                return response()->json([
                    'success' => false,
                    'message' => 'Este carnet ya fue entregado el ' . 
                                $carnet->fecha_entrega->format('d/m/Y H:i') . 
                                ' por ' . ($carnet->entregador ? $carnet->entregador->nombre : 'N/A'),
                    'ya_entregado' => true,
                    'carnet' => [
                        'codigo' => $carnet->codigo_carnet,
                        'estudiante' => $carnet->nombre_completo,
                        'dni' => $carnet->estudiante->numero_documento,
                        'fecha_entrega' => $carnet->fecha_entrega->format('d/m/Y H:i'),
                        'entregado_por' => $carnet->entregador ? $carnet->entregador->nombre . ' ' . $carnet->entregador->apellido_paterno : 'N/A'
                    ]
                ], 400);
            }

            // Obtener foto del estudiante
            $fotoUrl = null;
            if ($carnet->foto_path) {
                $fotoUrl = asset('storage/' . $carnet->foto_path);
            }

            return response()->json([
                'success' => true,
                'carnet' => [
                    'id' => $carnet->id,
                    'codigo' => $carnet->codigo_carnet,
                    'estudiante' => $carnet->nombre_completo,
                    'dni' => $carnet->estudiante->numero_documento,
                    'carrera' => $carnet->carrera ? $carnet->carrera->nombre : ($carnet->modalidad === 'reforzamiento_colegio' ? 'REFORZAMIENTO' : 'N/A'),
                    'turno' => $carnet->turno->nombre,
                    'aula' => $carnet->aula ? $carnet->aula->nombre : 'Sin asignar',
                    'ciclo' => $carnet->ciclo->nombre,
                    'foto_url' => $fotoUrl,
                    'fecha_emision' => $carnet->fecha_emision->format('d/m/Y'),
                    'fecha_vencimiento' => $carnet->fecha_vencimiento->format('d/m/Y')
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar el código QR: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Registrar entrega de carnet
     */
    public function registrarEntrega(Request $request)
    {
        if (!Auth::user()->hasPermission('carnets.scan_delivery')) {
            return response()->json(['error' => 'Sin permisos'], 403);
        }

        $request->validate([
            'carnet_id' => 'required|exists:carnets,id'
        ]);

        try {
            $carnet = Carnet::findOrFail($request->carnet_id);

            // Verificar que no esté ya entregado
            if ($carnet->entregado) {
                return response()->json([
                    'success' => false,
                    'message' => 'Este carnet ya fue entregado anteriormente.'
                ], 400);
            }

            // Marcar como entregado
            $ip = $request->ip();
            $carnet->marcarComoEntregado(Auth::id(), $ip);

            return response()->json([
                'success' => true,
                'message' => 'Carnet entregado exitosamente',
                'carnet' => [
                    'codigo' => $carnet->codigo_carnet,
                    'estudiante' => $carnet->nombre_completo,
                    'fecha_entrega' => $carnet->fecha_entrega->format('d/m/Y H:i')
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al registrar entrega: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Exportar Excel con control de entregas
     */
    public function exportarExcelEntregas(Request $request)
    {
        if (!Auth::user()->hasPermission('carnets.export_delivery')) {
            return response()->json(['error' => 'Sin permisos'], 403);
        }

        try {
            $filtros = [
                'ciclo_id' => $request->ciclo_id,
                'carrera_id' => $request->carrera_id,
                'turno_id' => $request->turno_id,
                'aula_id' => $request->aula_id,
                'entregado' => $request->entregado,
                'impreso' => $request->impreso
            ];

            $export = new \App\Exports\CarnetsEntregaExport($filtros);
            $filename = 'control_entregas_carnets_' . date('YmdHis') . '.xlsx';

            return \Maatwebsite\Excel\Facades\Excel::download($export, $filename);

        } catch (\Exception $e) {
            return back()->with('error', 'Error al exportar: ' . $e->getMessage());
        }
    }

    /**
     * Obtener estadísticas de entregas
     */
    public function estadisticasEntrega(Request $request)
    {
        if (!Auth::user()->hasPermission('carnets.delivery_reports')) {
            return response()->json(['error' => 'Sin permisos'], 403);
        }

        try {
            $query = Carnet::query();

            // Aplicar filtros si existen
            if ($request->ciclo_id) {
                $query->where('ciclo_id', $request->ciclo_id);
            }
            if ($request->carrera_id) {
                $query->where('carrera_id', $request->carrera_id);
            }

            $total = $query->count();
            $impresos = (clone $query)->where('impreso', true)->count();
            $entregados = (clone $query)->where('entregado', true)->count();
            $pendientesEntrega = (clone $query)->where('impreso', true)->where('entregado', false)->count();
            $noImpresos = (clone $query)->where('impreso', false)->count();

            return response()->json([
                'success' => true,
                'estadisticas' => [
                    'total' => $total,
                    'impresos' => $impresos,
                    'entregados' => $entregados,
                    'pendientes_entrega' => $pendientesEntrega,
                    'no_impresos' => $noImpresos,
                    'porcentaje_entrega' => $impresos > 0 ? round(($entregados / $impresos) * 100, 2) : 0
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener estadísticas: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Determinar el grupo según el nombre de la carrera
     */
    private function determinarGrupo($nombreCarrera)
    {
        $nombreLower = strtolower($nombreCarrera);
        
        // Grupo A - Ingenierías
        if (str_contains($nombreLower, 'ingenieria') || 
            str_contains($nombreLower, 'sistemas') ||
            str_contains($nombreLower, 'informatica') ||
            str_contains($nombreLower, 'forestal') ||
            str_contains($nombreLower, 'medio ambiente') ||
            str_contains($nombreLower, 'agroindustrial')) {
            return 'A';
        }
        
        // Grupo D - Alta especialización / Salud
        if (str_contains($nombreLower, 'medicina humana')) {
            return 'D';
        }
        
        // Grupo B - Ciencias de la Salud
        if (str_contains($nombreLower, 'medicina') ||
            str_contains($nombreLower, 'veterinaria') ||
            str_contains($nombreLower, 'zootecnia') ||
            str_contains($nombreLower, 'enfermeria') ||
            str_contains($nombreLower, 'biologia')) {
            return 'B';
        }
        
        // Grupo C - Ciencias Sociales y Educación
        if (str_contains($nombreLower, 'administracion') ||
            str_contains($nombreLower, 'negocios') ||
            str_contains($nombreLower, 'contabilidad') ||
            str_contains($nombreLower, 'finanzas') ||
            str_contains($nombreLower, 'derecho') ||
            str_contains($nombreLower, 'ciencias politicas') ||
            str_contains($nombreLower, 'ecoturismo') ||
            str_contains($nombreLower, 'educacion') ||
            str_contains($nombreLower, 'primaria') ||
            str_contains($nombreLower, 'inicial') ||
            str_contains($nombreLower, 'especial') ||
            str_contains($nombreLower, 'matematica') ||
            str_contains($nombreLower, 'computacion') ||
            str_contains($nombreLower, 'economia')) {
            return 'C';
        }
        
        return null; // Si no coincide con ningún grupo
    }

    /**
     * Limpiar texto de emojis y caracteres especiales para DomPDF
     */
    private function limpiarTexto($texto)
    {
        if (empty($texto)) return '';
        
        // Expresión regular para eliminar emojis y símbolos Unicode extendidos
        $regex = '/[\x{1F600}-\x{1F64F}\x{1F300}-\x{1F5FF}\x{1F680}-\x{1F6FF}\x{2600}-\x{26FF}\x{2700}-\x{27BF}\x{1F1E0}-\x{1F1FF}\x{1F900}-\x{1F9FF}\x{1F018}-\x{1F093}\x{1F100}-\x{1F1FF}\x{1F200}-\x{1F2FF}\x{1F300}-\x{1F5FF}\x{1F600}-\x{1F64F}\x{1F680}-\x{1F6FF}\x{1F700}-\x{1F77F}\x{1F780}-\x{1F7FF}\x{1F800}-\x{1F8FF}\x{1F900}-\x{1F9FF}\x{1FA00}-\x{1FA6F}\x{1FA70}-\x{1FAFF}]/u';
        $texto = preg_replace($regex, '', $texto);
        
        // Trim de espacios adicionales que puedan quedar
        return trim($texto);
    }
}