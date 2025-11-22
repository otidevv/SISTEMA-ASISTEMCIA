<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Inscripcion;
use App\Models\Ciclo;
use App\Models\Postulacion;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class TarjetasController extends Controller
{
    public function obtenerPostulantes(Request $request)
    {
        try {
            $cicloActivo = Ciclo::where('es_activo', true)->first();

            if (!$cicloActivo) {
                return response()->json(['error' => 'No hay un ciclo activo configurado.'], 404);
            }

            $inscripciones = Inscripcion::with(['estudiante', 'carrera', 'aula'])
                ->where('ciclo_id', $cicloActivo->id)
                ->where('estado_inscripcion', 'activo')
                ->whereHas('estudiante.postulaciones', function ($query) use ($cicloActivo) {
                    $query->where('ciclo_id', $cicloActivo->id)->where('estado', 'aprobado');
                })
                ->get();

            if ($inscripciones->isEmpty()) {
                $postulantesAprobados = Postulacion::with(['estudiante', 'carrera', 'ciclo'])
                    ->where('ciclo_id', $cicloActivo->id)
                    ->where('estado', 'aprobado')
                    ->get();

                if ($postulantesAprobados->isEmpty()) {
                    return response()->json([]);
                }

                $temas = ['P', 'Q', 'R'];
                $data = $postulantesAprobados->map(function ($postulacion) use ($temas) {
                    $tema = $temas[array_rand($temas)];
                    return [
                        'nombres' => $postulacion->estudiante->nombre . ' ' . $postulacion->estudiante->apellido_paterno,
                        'carrera' => $postulacion->carrera->nombre,
                        'aula' => 'POR ASIGNAR',
                        'codigo' => $postulacion->codigo_postulante,
                        'grupo' => 'POR ASIGNAR',
                        'tema' => $tema,
                        'foto' => $postulacion->foto_path ? asset('storage/' . $postulacion->foto_path) : null,
                        'foto_path' => $postulacion->foto_path,
                    ];
                });
                return response()->json($data);
            }

            $temas = ['P', 'Q', 'R'];
            $postulantes = $inscripciones->map(function ($inscripcion) use ($temas) {
                // Lógica determinista para asignar tema basado en el ID del aula
                // Si no hay aula, usa un hash del id de inscripción para mantener consistencia
                $seed = $inscripcion->aula ? $inscripcion->aula->id : $inscripcion->id;
                $temaIndex = $seed % 3;
                $tema = $temas[$temaIndex];
                
                // Mapeo de Grupo basado en Tema (P->A, Q->B, R->C)
                $grupo = 'N/A';
                if ($tema === 'P') $grupo = 'A';
                if ($tema === 'Q') $grupo = 'B';
                if ($tema === 'R') $grupo = 'C';

                $postulacion = Postulacion::where('estudiante_id', $inscripcion->estudiante_id)
                                          ->where('ciclo_id', $inscripcion->ciclo_id)
                                          ->first();

                return [
                    'nombres' => $inscripcion->estudiante->nombre . ' ' . $inscripcion->estudiante->apellido_paterno,
                    'carrera' => $inscripcion->carrera->nombre,
                    'aula' => $inscripcion->aula ? $inscripcion->aula->nombre : 'N/A',
                    'aula_id' => $inscripcion->aula ? $inscripcion->aula->id : null, // Útil para agrupar
                    'codigo' => $postulacion ? $postulacion->codigo_postulante : $inscripcion->codigo_inscripcion,
                    'grupo' => $grupo,
                    'tema' => $tema,
                    'foto' => $postulacion && $postulacion->foto_path ? asset('storage/' . $postulacion->foto_path) : null,
                    'foto_path' => $postulacion ? $postulacion->foto_path : null,
                ];
            });

            return response()->json($postulantes);

        } catch (\Exception $e) {
            \Log::error('Error en TarjetasController@obtenerPostulantes: ' . $e->getMessage());
            return response()->json(['error' => 'Error al obtener los datos. Revise el log del sistema.'], 500);
        }
    }

    public function exportarPDF(Request $request)
    {
        set_time_limit(300);
        ini_set('memory_limit', '512M');

        $request->validate([
            'postulantes' => 'required|array',
            'postulantes.*.nombres' => 'required|string',
            'postulantes.*.carrera' => 'required|string',
            'postulantes.*.aula' => 'required|string',
            'postulantes.*.codigo' => 'required|string',
            'postulantes.*.grupo' => 'required|string',
            'postulantes.*.tema' => 'required|string',
            'postulantes.*.foto' => 'nullable|string',
            'postulantes.*.foto_path' => 'nullable|string',
        ]);

        $postulantes = $request->postulantes;
        $tarjetasData = [];

        \Log::info('Procesando ' . count($postulantes) . ' postulantes para PDF');

        // Usar un placeholder de 1x1 pixel transparente como fallback definitivo.
        // Esto evita cualquier dependencia del sistema de archivos para el placeholder.
        $placeholderBase64 = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=';

        foreach ($postulantes as $postulante) {
            $postulanteData = (array) $postulante;
            $base64Image = null;
            $fotoPath = $postulanteData['foto_path'] ?? null;

            // 1. Intentar cargar la foto desde el almacenamiento local
            if ($fotoPath) {
                $fullPath = storage_path('app/public/' . $fotoPath);
                if (file_exists($fullPath)) {
                    try {
                        $imageData = file_get_contents($fullPath);
                        $mime = mime_content_type($fullPath);
                        $base64Image = 'data:' . $mime . ';base64,' . base64_encode($imageData);
                    } catch (\Exception $e) {
                        \Log::warning('Fallo al leer foto desde storage: ' . $fullPath . '. Error: ' . $e->getMessage());
                    }
                } else {
                    \Log::warning('Archivo de foto no encontrado en: ' . $fullPath . ' para: ' . $postulanteData['nombres']);
                }
            }

            // 2. Si no se pudo cargar la foto, usar el placeholder
            if (!$base64Image) {
                $base64Image = $placeholderBase64;
                \Log::info('Usando placeholder para: ' . $postulanteData['nombres']);
            }

            $tarjetasData[] = [
                'nombres' => $postulanteData['nombres'],
                'carrera' => $postulanteData['carrera'],
                'aula' => $postulanteData['aula'],
                'codigo' => $postulanteData['codigo'],
                'grupo' => $postulanteData['grupo'],
                'tema' => $postulanteData['tema'],
                'foto' => $base64Image,
            ];
        }

        \Log::info('Datos preparados para PDF: ' . count($tarjetasData) . ' tarjetas');
        $viewPath = 'tarjetas.pdf';
        \Log::info('Generando PDF con vista: ' . $viewPath);

        try {
            $pdf = Pdf::loadView($viewPath, ['tarjetas' => $tarjetasData])
                ->setPaper('a4', 'portrait');
            \Log::info('PDF generado exitosamente');
            return $pdf->download('etiquetas_examen_preuni_' . date('YmdHis') . '.pdf');
        } catch (\Exception $e) {
            \Log::error('Error al generar PDF: ' . $e->getMessage());
            return response()->json(['error' => 'Error al generar PDF: ' . $e->getMessage()], 500);
        }
    }

    // ==========================================
    // GESTIÓN DE DISTRIBUCIÓN DE EXAMEN
    // ==========================================

    public function getEdificioData(Request $request)
    {
        try {
            $cicloActivo = Ciclo::where('es_activo', true)->first();
            if (!$cicloActivo) return response()->json(['error' => 'No hay ciclo activo'], 404);

            // 1. Obtener TODAS las Aulas activas (sin filtro de piso hardcodeado para permitir edición)
            $aulas = \App\Models\Aula::where('estado', true)->get()->map(function($aula) {
                if (!$aula->piso && is_numeric($aula->nombre)) {
                    $aula->piso = substr($aula->nombre, 0, 1);
                }
                return $aula;
            })->values();

            // 2. Obtener Distribución Guardada
            $distribuciones = \App\Models\ExamenDistribucion::with('docente')
                ->where('ciclo_id', $cicloActivo->id)
                ->get()
                ->keyBy('aula_id');

            // 3. Obtener Docentes Disponibles
            $docentes = \App\Models\User::whereHas('roles', function($q) {
                $q->whereIn('nombre', ['profesor', 'docente', 'admin']); 
            })->select('id', 'nombre', 'apellido_paterno', 'apellido_materno')->get();

            // 4. Combinar datos
            $aulasData = $aulas->map(function($aula) use ($distribuciones) {
                $dist = $distribuciones->get($aula->id);
                
                // Determinar nombre del docente (registrado o invitado)
                $docenteNombre = null;
                $docenteId = null;
                $docenteInvitado = null;

                if ($dist) {
                    if ($dist->docente_id && $dist->docente) {
                        $docenteNombre = $dist->docente->nombre_completo;
                        $docenteId = $dist->docente_id;
                    } elseif ($dist->docente_invitado) {
                        $docenteNombre = $dist->docente_invitado;
                        $docenteInvitado = $dist->docente_invitado;
                    }
                }

                return [
                    'id' => $aula->id,
                    'nombre' => $aula->nombre,
                    'piso' => $aula->piso,
                    'capacidad' => $aula->capacidad,
                    'docente_id' => $docenteId,
                    'docente_invitado' => $docenteInvitado,
                    'docente_nombre' => $docenteNombre,
                    'tema' => $dist ? $dist->tema : null,
                    'grupo' => $dist ? $dist->grupo : null,
                    'cantidad_estudiantes' => $dist ? $dist->cantidad_estudiantes : 0,
                    'ocupacion_real' => Inscripcion::where('aula_id', $aula->id)
                                        ->where('ciclo_id', $dist ? $dist->ciclo_id : 0)
                                        ->where('estado_inscripcion', 'activo')
                                        ->count()
                ];
            });

            // Agrupar por piso y ordenar
            $pisos = $aulasData->groupBy('piso')->sortKeysDesc(); 

            // Obtener información del examen actual para el título
            $tituloExamen = "EXAMEN DEL CEPRE-UNAMAD " . ($cicloActivo->nombre ?? 'CICLO ACTUAL');
            
            try {
                // Intentar determinar qué examen es basado en fechas
                $examenInfo = $cicloActivo->getProximoExamen();
                if ($examenInfo) {
                    $tituloExamen = mb_strtoupper($examenInfo['nombre']) . " DEL CEPRE-UNAMAD " . ($cicloActivo->nombre ?? '');
                } else {
                    // Si no hay próximo, ver si hay alguno reciente o usar genérico
                    $examenes = $cicloActivo->getExamenes();
                    if (is_array($examenes) && count($examenes) > 0) {
                        $ultimo = end($examenes);
                        $tituloExamen = mb_strtoupper($ultimo['nombre']) . " DEL CEPRE-UNAMAD " . ($cicloActivo->nombre ?? '');
                    }
                }
            } catch (\Exception $ex) {
                // Si falla el cálculo del nombre, usar el genérico y no romper todo
                \Log::warning('Error calculando nombre examen: ' . $ex->getMessage());
            }

            return response()->json([
                'pisos' => $pisos,
                'docentes' => $docentes,
                'titulo_examen' => $tituloExamen
            ]);

        } catch (\Exception $e) {
            \Log::error('Error en getEdificioData: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function generarDistribucionAleatoria(Request $request)
    {
        try {
            $cicloActivo = Ciclo::where('es_activo', true)->first();
            if (!$cicloActivo) return response()->json(['error' => 'No hay ciclo activo'], 404);

            \DB::beginTransaction();

            // 1. Obtener estudiantes aptos
            $inscripciones = Inscripcion::where('ciclo_id', $cicloActivo->id)
                ->where('estado_inscripcion', 'activo')
                ->whereHas('estudiante.postulaciones', function ($query) use ($cicloActivo) {
                    $query->where('ciclo_id', $cicloActivo->id)->where('estado', 'aprobado');
                })
                ->get();

            if ($inscripciones->isEmpty()) {
                return response()->json(['error' => 'No hay estudiantes aptos para distribuir'], 400);
            }

            // 2. Obtener Aulas Disponibles (Filtrar solo las que tienen capacidad > 0)
            $aulas = \App\Models\Aula::where('estado', true)
                ->where('capacidad', '>', 0)
                ->get()
                ->values();

            if ($aulas->isEmpty()) {
                return response()->json(['error' => 'No hay aulas disponibles con capacidad'], 400);
            }

            // 3. Barajar Estudiantes
            $inscripcionesBarajadas = $inscripciones->shuffle();

            // 4. Limpiar asignaciones previas
            Inscripcion::where('ciclo_id', $cicloActivo->id)->update(['aula_id' => null]);

            // 5. Distribuir
            $aulaIndex = 0;
            $estudiantesAsignados = 0;
            
            $conteoPorAula = [];

            foreach ($inscripcionesBarajadas as $inscripcion) {
                $aula = null;
                $intentos = 0;
                
                while ($intentos < $aulas->count()) {
                    $candidate = $aulas[$aulaIndex];
                    $ocupados = $conteoPorAula[$candidate->id] ?? 0;
                    
                    if ($ocupados < $candidate->capacidad) {
                        $aula = $candidate;
                        break;
                    }
                    
                    $aulaIndex = ($aulaIndex + 1) % $aulas->count();
                    $intentos++;
                }

                if (!$aula) {
                    \DB::rollBack();
                    return response()->json(['error' => 'No hay suficiente capacidad en las aulas (Faltan asientos).'], 400);
                }

                $inscripcion->aula_id = $aula->id;
                $inscripcion->save();

                if (!isset($conteoPorAula[$aula->id])) $conteoPorAula[$aula->id] = 0;
                $conteoPorAula[$aula->id]++;
                $estudiantesAsignados++;
            }

            // 6. Generar registros en ExamenDistribucion
            $temas = ['P', 'Q', 'R'];
            
            foreach ($aulas as $index => $aula) {
                $cantidad = $conteoPorAula[$aula->id] ?? 0;
                if ($cantidad > 0) {
                    $tema = $temas[$index % 3];
                    $grupo = ($tema === 'P') ? 'A' : (($tema === 'Q') ? 'B' : 'C');

                    \App\Models\ExamenDistribucion::updateOrCreate(
                        ['ciclo_id' => $cicloActivo->id, 'aula_id' => $aula->id],
                        [
                            'tema' => $tema,
                            'grupo' => $grupo,
                            'cantidad_estudiantes' => $cantidad
                        ]
                    );
                } else {
                     \App\Models\ExamenDistribucion::where('ciclo_id', $cicloActivo->id)
                        ->where('aula_id', $aula->id)
                        ->delete();
                }
            }

            \DB::commit();
            return response()->json(['message' => "Distribución completada. $estudiantesAsignados estudiantes asignados."]);

        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Error en generarDistribucionAleatoria: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function guardarDistribucionDocente(Request $request)
    {
        try {
            $cicloActivo = Ciclo::where('es_activo', true)->first();
            if (!$cicloActivo) return response()->json(['error' => 'No hay ciclo activo'], 404);

            $aulaId = $request->input('aula_id');
            $valor = $request->input('docente_valor'); // Puede ser ID o Nombre

            $distribucion = \App\Models\ExamenDistribucion::firstOrNew([
                'ciclo_id' => $cicloActivo->id,
                'aula_id' => $aulaId
            ]);

            // Verificar si es ID numérico (Usuario DB) o Texto (Invitado)
            if (is_numeric($valor)) {
                $distribucion->docente_id = $valor;
                $distribucion->docente_invitado = null;
            } else {
                $distribucion->docente_id = null;
                $distribucion->docente_invitado = $valor;
            }
            
            if (!$distribucion->exists) {
                $distribucion->tema = 'R'; 
                $distribucion->grupo = 'C'; 
                $distribucion->cantidad_estudiantes = 0; 
            }
            
            $distribucion->save();

            return response()->json(['message' => 'Docente asignado correctamente']);

        } catch (\Exception $e) {
            \Log::error('Error en guardarDistribucionDocente: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getAulaDetalle($id)
    {
        try {
            $cicloActivo = Ciclo::where('es_activo', true)->first();
            $aula = \App\Models\Aula::findOrFail($id);
            
            $estudiantes = Inscripcion::where('aula_id', $id)
                ->where('ciclo_id', $cicloActivo->id)
                ->where('estado_inscripcion', 'activo')
                ->with(['estudiante', 'estudiante.postulaciones' => function($q) use ($cicloActivo) {
                    $q->where('ciclo_id', $cicloActivo->id);
                }])
                ->get()
                ->map(function($inscripcion) {
                    $postulacion = $inscripcion->estudiante->postulaciones->first();
                    return [
                        'id' => $inscripcion->estudiante->id,
                        'nombre_completo' => $inscripcion->estudiante->nombre_completo,
                        'foto' => $postulacion && $postulacion->foto_path ? asset('storage/' . $postulacion->foto_path) : null,
                        'codigo' => $postulacion ? $postulacion->codigo_postulante : '---'
                    ];
                });

            return response()->json([
                'aula' => $aula,
                'estudiantes' => $estudiantes
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function guardarAula(Request $request)
    {
        try {
            $data = $request->validate([
                'nombre' => 'required|string',
                'piso' => 'required|integer',
                'capacidad' => 'required|integer|min:1'
            ]);

            $aula = \App\Models\Aula::updateOrCreate(
                ['nombre' => $data['nombre']], // Buscar por nombre
                [
                    'piso' => $data['piso'],
                    'capacidad' => $data['capacidad'],
                    'estado' => true,
                    'tipo' => 'Aula', // Default
                    'codigo' => 'A-' . $data['nombre'] // Generar código simple
                ]
            );

            return response()->json(['message' => 'Aula guardada', 'aula' => $aula]);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function guardarPisoCompleto(Request $request)
    {
        try {
            $data = $request->validate([
                'piso' => 'required|integer|min:1',
                'cantidad_aulas' => 'required|integer|min:1|max:20',
                'capacidad_default' => 'required|integer|min:1',
                'inicio_numeracion' => 'required|integer' // Ej. 1 para 201, 202...
            ]);

            $piso = $data['piso'];
            $creadas = 0;

            for ($i = 0; $i < $data['cantidad_aulas']; $i++) {
                $numero = $data['inicio_numeracion'] + $i;
                // Formato: Piso + Numero (Ej: 2 + 01 = 201)
                $nombreAula = $piso . str_pad($numero, 2, '0', STR_PAD_LEFT);
                
                \App\Models\Aula::firstOrCreate(
                    ['nombre' => $nombreAula],
                    [
                        'piso' => $piso,
                        'capacidad' => $data['capacidad_default'],
                        'estado' => true,
                        'tipo' => 'Aula',
                        'codigo' => 'A-' . $nombreAula
                    ]
                );
                $creadas++;
            }

            return response()->json(['message' => "$creadas aulas creadas para el Piso $piso"]);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function eliminarAula($id)
    {
        try {
            $aula = \App\Models\Aula::findOrFail($id);
            // Soft delete lógico poniendo estado false, o delete físico si no tiene relaciones críticas
            // Para este caso, mejor estado false
            $aula->estado = false;
            $aula->save();
            
            return response()->json(['message' => 'Aula desactivada']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}