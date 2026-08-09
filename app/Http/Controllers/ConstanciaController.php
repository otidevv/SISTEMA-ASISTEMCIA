<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ConstanciaController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Obtener constancias
        $query = DB::table('constancias_generadas')
            ->join('inscripciones', 'constancias_generadas.inscripcion_id', '=', 'inscripciones.id')
            ->join('users as estudiante_user', 'constancias_generadas.estudiante_id', '=', 'estudiante_user.id') // Alias for student user
            ->join('ciclos', 'inscripciones.ciclo_id', '=', 'ciclos.id')
            ->join('carreras', 'inscripciones.carrera_id', '=', 'carreras.id')
            ->leftJoin('users as generador_user', 'constancias_generadas.generado_por', '=', 'generador_user.id'); // Join for generator user

        // Si el usuario no es admin y no tiene permiso para ver todas, filtrar por sus constancias
        if (!$user->hasRole('admin') && !$user->hasPermission('constancias.view')) {
            $query->where(function($q) use ($user) {
                $q->where('constancias_generadas.estudiante_id', $user->id)
                  ->orWhere('constancias_generadas.generado_por', $user->id);
            });
        }

        $constancias = $query->select(
                'constancias_generadas.*',
                'inscripciones.id as inscripcion_id',
                'estudiante_user.nombre as estudiante_nombre',
                'estudiante_user.apellido_paterno as estudiante_apellido_paterno',
                'estudiante_user.apellido_materno as estudiante_apellido_materno',
                'ciclos.nombre as ciclo_nombre',
                'carreras.nombre as carrera_nombre',
                'generador_user.nombre as generador_nombre', // Select generator's name
                'generador_user.apellido_paterno as generador_apellido_paterno', // Select generator's paternal surname
                'generador_user.apellido_materno as generador_apellido_materno' // Select generator's maternal surname
            )
            ->orderBy('constancias_generadas.created_at', 'desc')
            ->get();

        // Transformar los resultados para que sean más fáciles de usar en la vista
        $constancias = $constancias->map(function($constancia) {
            $constancia->estudiante = (object) [
                'nombre' => $constancia->estudiante_nombre,
                'apellido_paterno' => $constancia->estudiante_apellido_paterno,
                'apellido_materno' => $constancia->estudiante_apellido_materno
            ];
            $constancia->inscripcion = (object) [
                'id' => $constancia->inscripcion_id,
                'ciclo' => (object) ['nombre' => $constancia->ciclo_nombre],
                'carrera' => (object) ['nombre' => $constancia->carrera_nombre]
            ];
            $constancia->generador = (object) [
                'nombre' => $constancia->generador_nombre,
                'apellido_paterno' => $constancia->generador_apellido_paterno,
                'apellido_materno' => $constancia->generador_apellido_materno
            ];
            return $constancia;
        });

        $ciclos = \App\Models\Ciclo::orderBy('created_at', 'desc')->get();

        return view('constancias.index', compact('constancias', 'ciclos'));
    }

    /**
     * Obtener constancias por estudiante (para AJAX)
     */
    public function getByEstudiante($estudianteId)
    {
        $user = Auth::user();

        // Verificar permisos
        if ($user->id != $estudianteId && !$user->hasRole('admin') && !$user->hasPermission('constancias.view')) {
            abort(403, 'No tienes permiso para ver estas constancias');
        }

        $constancias = DB::table('constancias_generadas')
            ->where('estudiante_id', $estudianteId)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($constancias);
    }

    /**
     * Obtener estadísticas de constancias
     */
    public function estadisticas()
    {
        $user = Auth::user();

        $estadisticas = DB::table('constancias_generadas')
            ->selectRaw('tipo, COUNT(*) as total')
            ->where('generado_por', $user->id)
            ->groupBy('tipo')
            ->get();

        return response()->json($estadisticas);
    }

    /**
     * Obtener inscripciones disponibles para generar constancias
     */
    public function getInscripcionesDisponibles(Request $request)
    {
        $user = Auth::user();
        $tipo = $request->get('tipo'); // 'estudios' o 'vacante'
        $dni = $request->get('dni');
        $cicloId = $request->get('ciclo_id');

        $query = DB::table('inscripciones')
            ->join('users', 'inscripciones.estudiante_id', '=', 'users.id')
            ->join('ciclos', 'inscripciones.ciclo_id', '=', 'ciclos.id')
            ->join('carreras', 'inscripciones.carrera_id', '=', 'carreras.id')
            ->leftJoin('turnos', 'inscripciones.turno_id', '=', 'turnos.id')
            ->where('inscripciones.estado_inscripcion', 'activo')
            ->select(
                'inscripciones.id as inscripcion_id',
                'inscripciones.codigo_inscripcion',
                'users.nombre',
                'users.apellido_paterno',
                'users.apellido_materno',
                'users.numero_documento',
                'ciclos.nombre as ciclo_nombre',
                'ciclos.fecha_inicio',
                'ciclos.fecha_fin',
                'carreras.nombre as carrera_nombre',
                'turnos.nombre as turno_nombre'
            );

        // Filtrar por ciclo si se proporciona
        if ($cicloId) {
            $query->where('inscripciones.ciclo_id', $cicloId);
        }

        // Filtrar por DNI si se proporciona
        if ($dni) {
            $query->where('users.numero_documento', $dni);
        }

        // Si no es admin, solo mostrar inscripciones del usuario actual o que tenga permisos
        if (!$user->hasRole('admin')) {
            if ($user->hasPermission('constancias.generar-estudios') || $user->hasPermission('constancias.generar-vacante')) {
                // Los usuarios con permisos pueden ver todas las inscripciones activas
            } else {
                // Solo las propias
                $query->where('inscripciones.estudiante_id', $user->id);
            }
        }

        $inscripciones = $query->orderBy('ciclos.fecha_inicio', 'desc')
            ->orderBy('users.apellido_paterno')
            ->get();

        // Transformar los resultados
        $inscripciones = $inscripciones->map(function($inscripcion) {
            return [
                'id' => $inscripcion->inscripcion_id,
                'codigo_inscripcion' => $inscripcion->codigo_inscripcion,
                'estudiante' => [
                    'nombre' => $inscripcion->nombre,
                    'apellido_paterno' => $inscripcion->apellido_paterno,
                    'apellido_materno' => $inscripcion->apellido_materno,
                    'numero_documento' => $inscripcion->numero_documento
                ],
                'ciclo' => [
                    'nombre' => $inscripcion->ciclo_nombre,
                    'fecha_inicio' => $inscripcion->fecha_inicio,
                    'fecha_fin' => $inscripcion->fecha_fin
                ],
                'carrera' => [
                    'nombre' => $inscripcion->carrera_nombre
                ],
                'turno' => [
                    'nombre' => $inscripcion->turno_nombre
                ]
            ];
        });

        return response()->json($inscripciones);
    }

    /**
     * Obtener ciclos disponibles para filtrar inscripciones
     */
    public function getCiclosDisponibles()
    {
        $user = Auth::user();

        $query = DB::table('ciclos')
            ->join('inscripciones', 'ciclos.id', '=', 'inscripciones.ciclo_id')
            ->where('inscripciones.estado_inscripcion', 'activo')
            ->select(
                'ciclos.id',
                'ciclos.nombre',
                'ciclos.fecha_inicio',
                'ciclos.fecha_fin'
            )
            ->distinct();

        // Si no es admin, solo mostrar ciclos con inscripciones del usuario actual o que tenga permisos
        if (!$user->hasRole('admin')) {
            if ($user->hasPermission('constancias.generar-estudios') || $user->hasPermission('constancias.generar-vacante')) {
                // Los usuarios con permisos pueden ver todos los ciclos con inscripciones activas
            } else {
                // Solo los ciclos donde el usuario está inscrito
                $query->where('inscripciones.estudiante_id', $user->id);
            }
        }

        $ciclos = $query->orderBy('ciclos.fecha_inicio', 'desc')
            ->get();

        return response()->json($ciclos);
    }

    /**
     * Eliminar una constancia
     */
    public function eliminar($constanciaId)
    {
        try {
            $user = Auth::user();

            // Buscar la constancia
            $constancia = DB::table('constancias_generadas')
                ->where('id', $constanciaId)
                ->first();

            if (!$constancia) {
                return response()->json(['error' => 'Constancia no encontrada'], 404);
            }

            // La autorización se maneja con el middleware 'can:constancias.eliminar'

            // Eliminar archivo si existe
            if ($constancia->constancia_firmada_path && Storage::disk('public')->exists($constancia->constancia_firmada_path)) {
                Storage::disk('public')->delete($constancia->constancia_firmada_path);
            }

            // Eliminar registro de la base de datos
            DB::table('constancias_generadas')->where('id', $constanciaId)->delete();

            return response()->json(['success' => 'Constancia eliminada correctamente']);

        } catch (\Exception $e) {
            \Log::error('Error al eliminar constancia: ' . $e->getMessage());
            return response()->json(['error' => 'Error al eliminar la constancia'], 500);
        }
    }

    /**
     * Generar constancias de vacante o estudios de forma masiva (por lista de DNIs o Excel)
     */
    public function generarMasiva(Request $request)
    {
        try {
            $user = Auth::user();

            if ($request->isMethod('get') && !$request->has('dnis_texto') && !$request->hasFile('archivo_excel')) {
                return redirect()->route('constancias.index');
            }
            
            // Validaciones
            $tipo = $request->input('tipo', 'vacante'); // 'vacante' o 'estudios'
            $formatoSalida = $request->input('formato_salida', 'pdf'); // 'pdf' o 'zip'
            $origen = $request->input('origen', 'texto'); // 'texto' o 'excel'
            
            // Verificar permisos
            $permisoRequerido = $tipo === 'vacante' ? 'constancias.generar-vacante' : 'constancias.generar-estudios';
            if (!$user->hasRole('admin') && !$user->hasPermission($permisoRequerido)) {
                return back()->with('error', 'No tienes permiso para generar constancias masivas de ' . $tipo);
            }

            $dnis = [];

            if ($origen === 'excel' && $request->hasFile('archivo_excel')) {
                $file = $request->file('archivo_excel');
                $arrays = \Maatwebsite\Excel\Facades\Excel::toArray(new class implements \Maatwebsite\Excel\Concerns\ToArray {
                    public function array(array $array) { return $array; }
                }, $file);
                
                $allText = json_encode($arrays);
                preg_match_all('/\b\d{8}\b/', $allText, $matches);
                $dnis = array_unique(array_filter($matches[0]));
            } else {
                $dnisTexto = $request->input('dnis_texto', '');
                preg_match_all('/\b\d{8}\b/', $dnisTexto, $matches);
                $dnis = array_unique(array_filter($matches[0]));
            }

            if (empty($dnis)) {
                return back()->with('error', 'No se encontraron DNIs válidos de 8 dígitos en la información proporcionada.');
            }

            // Filtrar inscripciones activas por ciclo específico o por ciclo activo vigente
            $cicloId = $request->input('ciclo_id');
            $queryInscripciones = \App\Models\Inscripcion::with(['estudiante', 'ciclo', 'carrera', 'turno', 'aula'])
                ->whereHas('estudiante', function($q) use ($dnis) {
                    $q->whereIn('numero_documento', $dnis);
                })
                ->whereIn('estado_inscripcion', ['activo', 'validado']);

            if ($cicloId) {
                $queryInscripciones->where('ciclo_id', $cicloId);
            } else {
                $cicloActivo = \App\Models\Ciclo::where('es_activo', true)->first();
                if ($cicloActivo) {
                    $queryInscripciones->where('ciclo_id', $cicloActivo->id);
                }
            }

            $inscripciones = $queryInscripciones->orderBy('created_at', 'desc')
                ->get()
                ->keyBy(function($item) {
                    return trim($item->estudiante->numero_documento);
                });

            if ($inscripciones->isEmpty()) {
                return back()->with('error', 'Ninguno de los ' . count($dnis) . ' DNIs ingresados cuenta con una inscripción activa o validada en el sistema.');
            }

            // Contador para correlativo en este lote
            $año = date('Y');
            $ultimo = DB::table('constancias_generadas')
                ->where('tipo', $tipo)
                ->whereYear('created_at', $año)
                ->count();

            \Carbon\Carbon::setLocale('es');
            setlocale(LC_TIME, 'es_PE.UTF-8', 'es_ES.UTF-8', 'Spanish');
            $fecha = ucfirst(\Carbon\Carbon::now()->translatedFormat('d \d\e F \d\e Y'));

            $itemsData = [];

            foreach ($inscripciones as $dniKey => $inscripcion) {
                // Verificar si ya existe constancia registrada
                $constanciaExistente = DB::table('constancias_generadas')
                    ->where('tipo', $tipo)
                    ->where('estudiante_id', $inscripcion->estudiante_id)
                    ->where('inscripcion_id', $inscripcion->id)
                    ->first();

                if ($constanciaExistente) {
                    $datosStored = json_decode($constanciaExistente->datos, true);
                    $numeroConstancia = $constanciaExistente->numero_constancia;
                    $codigoVerificacion = $constanciaExistente->codigo_verificacion;
                    $qrCode = $datosStored['qr_code'] ?? '';
                } else {
                    $ultimo++;
                    $numeroConstancia = sprintf('%s-%04d', $año, $ultimo);
                    $prefix = $tipo === 'vacante' ? 'VAC' : 'EST';
                    $codigoVerificacion = $prefix . '-' . $numeroConstancia . '-' . md5($inscripcion->id . now()->timestamp . rand(1000, 9999));
                    
                    $urlValidacion = route('constancias.validar', $codigoVerificacion);
                    try {
                        $qrCode = base64_encode(\SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')->size(150)->generate($urlValidacion));
                    } catch (\Exception $e) {
                        $qrCode = '';
                    }

                    $dataRegister = [
                        'inscripcion' => $inscripcion,
                        'estudiante' => $inscripcion->estudiante,
                        'ciclo' => $inscripcion->ciclo,
                        'carrera' => $inscripcion->carrera,
                        'turno' => $inscripcion->turno,
                        'aula' => $inscripcion->aula,
                        'numero_constancia' => $numeroConstancia,
                        'codigo_verificacion' => $codigoVerificacion,
                        'qr_code' => $qrCode,
                        'fecha_generacion' => \Carbon\Carbon::now()->format('d/m/Y H:i'),
                        'lugar' => 'Puerto Maldonado',
                        'fecha' => $fecha
                    ];

                    if ($tipo === 'estudios') {
                        $dataRegister['pie_linea1'] = 'UNAMAD: Parque científico Tecnológico sostenible con Investigación e Innovación';
                        $dataRegister['pie_linea2'] = 'Av. Dos de Mayo N° 960 — Puerto Maldonado — CEL: 917061893 — 975844977';
                        $dataRegister['pie_linea3'] = 'CEPRE-UNAMAD CEL: 993110927';
                    }

                    DB::table('constancias_generadas')->insert([
                        'tipo' => $tipo,
                        'codigo_verificacion' => $codigoVerificacion,
                        'numero_constancia' => $numeroConstancia,
                        'inscripcion_id' => $inscripcion->id,
                        'estudiante_id' => $inscripcion->estudiante_id,
                        'datos' => json_encode($dataRegister),
                        'generado_por' => $user->id,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }

                $itemsData[] = [
                    'inscripcion' => $inscripcion,
                    'estudiante' => $inscripcion->estudiante,
                    'ciclo' => $inscripcion->ciclo,
                    'carrera' => $inscripcion->carrera,
                    'turno' => $inscripcion->turno,
                    'aula' => $inscripcion->aula,
                    'numero_constancia' => $numeroConstancia,
                    'codigo_verificacion' => $codigoVerificacion,
                    'qr_code' => $qrCode,
                    'fecha_generacion' => \Carbon\Carbon::now()->format('d/m/Y H:i'),
                    'lugar' => 'Puerto Maldonado',
                    'fecha' => $fecha
                ];
            }

            if ($formatoSalida === 'zip') {
                $zipFileName = 'constancias_' . $tipo . '_masivas_' . date('Ymd_His') . '.zip';
                $zipPath = storage_path('app/public/' . $zipFileName);

                $zip = new \ZipArchive();
                if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === TRUE) {
                    $viewName = $tipo === 'vacante' ? 'pdf.constancia-vacante' : 'pdf.constancia-estudios';
                    foreach ($itemsData as $item) {
                        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView($viewName, $item);
                        $pdf->setPaper('A4', 'portrait');
                        $pdfContent = $pdf->output();

                        $est = $item['estudiante'];
                        $nombreLimpio = preg_replace('/[^A-Za-z0-9_]/', '_', $est->nombre . '_' . $est->apellido_paterno);
                        $filenameInZip = "Constancia_{$tipo}_{$est->numero_documento}_{$nombreLimpio}.pdf";
                        $zip->addFromString($filenameInZip, $pdfContent);
                    }
                    $zip->close();
                }

                return response()->download($zipPath)->deleteFileAfterSend(true);
            } else {
                // PDF Consolidado multipágina utilizando TAL CUAL la plantilla individual exacta
                $viewName = $tipo === 'vacante' ? 'pdf.constancia-vacante' : 'pdf.constancia-estudios';
                $combinedHtml = '';
                $extractedStyles = '';

                foreach ($itemsData as $index => $item) {
                    $singleHtml = view($viewName, $item)->render();
                    
                    if ($index === 0 && preg_match('/<style[^>]*>(.*?)<\/style>/is', $singleHtml, $styleMatches)) {
                        $extractedStyles = $styleMatches[1];
                    }

                    if (preg_match('/<body[^>]*>(.*?)<\/body>/is', $singleHtml, $bodyMatches)) {
                        $bodyContent = $bodyMatches[1];
                    } else {
                        $bodyContent = $singleHtml;
                    }

                    $pageBreakStyle = ($index < count($itemsData) - 1) ? 'style="page-break-after: always; position: relative;"' : 'style="position: relative;"';
                    $combinedHtml .= '<div class="single-constancia-item" ' . $pageBreakStyle . '>' . $bodyContent . '</div>';
                }

                $fullDocumentHtml = '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><title>Constancias Masivas</title><style>' 
                    . $extractedStyles . 
                    ' .page-break { page-break-after: always; } .single-constancia-item { width: 100%; position: relative; }</style></head><body>' 
                    . $combinedHtml . '</body></html>';

                $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHtml($fullDocumentHtml);
                $pdf->setPaper('A4', 'portrait');

                $filename = 'constancias_masivas_' . $tipo . '_' . date('Ymd_His') . '.pdf';
                return response($pdf->output(), 200, [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'inline; filename="' . $filename . '"',
                ]);
            }

        } catch (\Exception $e) {
            \Log::error('Error en generación masiva de constancias: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            return back()->with('error', 'Ocurrió un error al procesar las constancias masivas: ' . $e->getMessage());
        }
    }
}