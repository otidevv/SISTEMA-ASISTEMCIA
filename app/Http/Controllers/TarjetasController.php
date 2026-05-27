<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Inscripcion;
use App\Models\Ciclo;
use App\Models\Postulacion;
use App\Models\ExamenEstudianteDistribucion;
use App\Models\ExamenDistribucion;
use App\Models\ExamenGrupoConfig;
use App\Helpers\AsistenciaHelper;
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

            $examenNumero = $request->input('examen_numero', 1);
            $inhabilitados = AsistenciaHelper::obtenerDocumentosInhabilitados($cicloActivo, $examenNumero);

            // 1. Verificar si ya existe distribución guardada para este examen
            $distribuciones = ExamenEstudianteDistribucion::where('ciclo_id', $cicloActivo->id)
                ->where('examen_numero', $examenNumero)
                ->with(['inscripcion.estudiante', 'inscripcion.carrera', 'aula'])
                ->get();

            if ($distribuciones->isNotEmpty()) {
                $data = $distribuciones->map(function ($dist) use ($inhabilitados, $cicloActivo) {
                    $inscripcion = $dist->inscripcion;
                    $estudiante = $inscripcion->estudiante;
                    $carrera = $inscripcion->carrera;
                    $aula = $dist->aula;

                    $postulacion = Postulacion::where('estudiante_id', $estudiante->id)
                                              ->where('ciclo_id', $cicloActivo->id)
                                              ->first();

                    $doc = $estudiante->numero_documento ?? '';
                    $isInhabilitado = in_array($doc, $inhabilitados);

                    $aulaNombre = $aula ? $this->limpiarTexto($aula->nombre) : 'POR ASIGNAR';

                    return [
                        'nombres' => $this->limpiarTexto($estudiante->nombre . ' ' . $estudiante->apellido_paterno . ' ' . ($estudiante->apellido_materno ?? '')),
                        'carrera' => $this->limpiarTexto($carrera->nombre ?? 'SIN CARRERA'),
                        'aula' => $aulaNombre,
                        'aula_id' => $aula ? $aula->id : null,
                        'codigo' => $postulacion ? $postulacion->codigo_postulante : $inscripcion->codigo_inscripcion,
                        'grupo' => $dist->grupo ?? '---',
                        'tema' => $dist->tema ?? '---',
                        'foto' => $postulacion && $postulacion->foto_path ? asset('storage/' . $postulacion->foto_path) : null,
                        'foto_path' => $postulacion ? $postulacion->foto_path : null,
                        'inhabilitado' => $isInhabilitado,
                        'asiento' => $dist->numero_asiento,
                        'dni' => $doc,
                    ];
                });
                return response()->json($data);
            }

            // 2. Si no hay distribución guardada, devolvemos los alumnos aptos sin asiento
            $inscripciones = Inscripcion::with(['estudiante', 'carrera', 'aula'])
                ->where('ciclo_id', $cicloActivo->id)
                ->where('estado_inscripcion', 'activo')
                ->whereHas('estudiante.postulaciones', function ($query) use ($cicloActivo) {
                    $query->where('ciclo_id', $cicloActivo->id)->where('estado', 'aprobado');
                })
                ->get();

            if ($inscripciones->isEmpty()) {
                return response()->json([]);
            }

            $data = $inscripciones->map(function ($inscripcion) use ($inhabilitados, $cicloActivo) {
                $estudiante = $inscripcion->estudiante;
                $carrera = $inscripcion->carrera;
                $postulacion = Postulacion::where('estudiante_id', $estudiante->id)
                                          ->where('ciclo_id', $cicloActivo->id)
                                          ->first();

                $doc = $estudiante->numero_documento ?? '';
                $isInhabilitado = in_array($doc, $inhabilitados);

                $grupo = $carrera->grupo ?? 'A';
                $defaultTemas = ['A' => 'P', 'B' => 'Q', 'C' => 'R'];
                $tema = $defaultTemas[$grupo] ?? 'R';

                return [
                    'nombres' => $this->limpiarTexto($estudiante->nombre . ' ' . $estudiante->apellido_paterno . ' ' . ($estudiante->apellido_materno ?? '')),
                    'carrera' => $this->limpiarTexto($carrera->nombre ?? 'SIN CARRERA'),
                    'aula' => 'POR ASIGNAR',
                    'aula_id' => null,
                    'codigo' => $postulacion ? $postulacion->codigo_postulante : $inscripcion->codigo_inscripcion,
                    'grupo' => $grupo,
                    'tema' => $tema,
                    'foto' => $postulacion && $postulacion->foto_path ? asset('storage/' . $postulacion->foto_path) : null,
                    'foto_path' => $postulacion ? $postulacion->foto_path : null,
                    'inhabilitado' => $isInhabilitado,
                    'asiento' => null,
                    'dni' => $doc,
                ];
            });

            return response()->json($data);

        } catch (\Exception $e) {
            \Log::error('Error en TarjetasController@obtenerPostulantes: ' . $e->getMessage());
            return response()->json(['error' => 'Error al obtener los datos. Revise el log del sistema.'], 500);
        }
    }

    public function exportarPDF(Request $request)
    {
        set_time_limit(300);
        ini_set('memory_limit', '1024M');

        $examenNumero = $request->input('examen_numero', 1);
        $cicloActivo = Ciclo::where('es_activo', true)->first();
        if (!$cicloActivo) {
            return response()->json(['error' => 'No hay ciclo activo'], 404);
        }

        $inhabilitados = AsistenciaHelper::obtenerDocumentosInhabilitados($cicloActivo, $examenNumero);

        $distribuciones = ExamenEstudianteDistribucion::where('ciclo_id', $cicloActivo->id)
            ->where('examen_numero', $examenNumero)
            ->with(['inscripcion.estudiante', 'inscripcion.carrera', 'aula'])
            ->get();

        if ($distribuciones->isEmpty()) {
            return redirect()->back()->with('error', 'No hay distribución generada para este examen.');
        }

        // Ordenar por aula y luego por asiento
        $distribuciones = $distribuciones->sortBy(function($dist) {
            return ($dist->aula->nombre ?? '') . '-' . str_pad($dist->numero_asiento, 5, '0', STR_PAD_LEFT);
        });

        $tarjetasData = [];
        $placeholderBase64 = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=';

        foreach ($distribuciones as $dist) {
            $inscripcion = $dist->inscripcion;
            $estudiante = $inscripcion->estudiante;
            $carrera = $inscripcion->carrera;
            
            $doc = $estudiante->numero_documento ?? '';
            $isInhabilitado = in_array($doc, $inhabilitados);

            $fotoSrcPath = null;
            $postulacion = Postulacion::where('estudiante_id', $estudiante->id)
                                      ->where('ciclo_id', $cicloActivo->id)
                                      ->first();
            $fotoPath = $postulacion ? $postulacion->foto_path : null;

            if ($fotoPath) {
                $fullPath = storage_path('app/public/' . $fotoPath);
                if (file_exists($fullPath)) {
                    $fotoSrcPath = $fullPath;
                }
            }

            if (!$fotoSrcPath) {
                $fotoSrcPath = $placeholderBase64;
            }

            $tarjetasData[] = [
                'nombres' => $this->limpiarTexto($estudiante->nombre . ' ' . $estudiante->apellido_paterno . ' ' . ($estudiante->apellido_materno ?? '')),
                'carrera' => $this->limpiarTexto($carrera->nombre ?? 'SIN CARRERA'),
                'aula' => $this->limpiarTexto($dist->aula->nombre ?? 'POR ASIGNAR'),
                'codigo' => $postulacion ? $postulacion->codigo_postulante : $inscripcion->codigo_inscripcion,
                'grupo' => $dist->grupo ?? '---',
                'tema' => $dist->tema ?? '---',
                'foto' => $fotoSrcPath,
                'inhabilitado' => $isInhabilitado,
                'asiento' => $dist->numero_asiento,
                'dni' => $doc,
            ];
        }

        $pdf = Pdf::loadView('tarjetas.pdf', [
            'tarjetas' => $tarjetasData,
            'ciclo_nombre' => $cicloActivo->nombre
        ])->setPaper('a4', 'landscape');

        return $pdf->download('etiquetas_mesa_examen_' . $examenNumero . '_' . date('YmdHis') . '.pdf');
    }

    public function exportarListaPuertaPrincipalPDF(Request $request)
    {
        set_time_limit(300);
        ini_set('memory_limit', '1024M');

        $examenNumero = $request->input('examen_numero', 1);
        $cicloActivo = Ciclo::where('es_activo', true)->first();
        if (!$cicloActivo) {
            return response()->json(['error' => 'No hay ciclo activo'], 404);
        }

        $examenPeriodo = AsistenciaHelper::getExamenPeriodoPorId($cicloActivo, $examenNumero);
        $examenNombre = $examenPeriodo ? $examenPeriodo['nombre'] : 'Examen ' . $examenNumero;

        $inhabilitados = AsistenciaHelper::obtenerDocumentosInhabilitados($cicloActivo, $examenNumero);

        // Fetch all distributions for this exam
        $distribuciones = ExamenEstudianteDistribucion::where('ciclo_id', $cicloActivo->id)
            ->where('examen_numero', $examenNumero)
            ->with(['inscripcion.estudiante', 'inscripcion.carrera', 'aula'])
            ->get();

        // Fetch all supervisors for this exam
        $supervisores = ExamenDistribucion::where('ciclo_id', $cicloActivo->id)
            ->where('examen_numero', $examenNumero)
            ->with(['aula', 'docente'])
            ->get();

        // Group by Aula ID
        $aulasData = [];

        foreach ($distribuciones as $dist) {
            $inscripcion = $dist->inscripcion;
            $estudiante = $inscripcion->estudiante;
            
            $doc = $estudiante->numero_documento ?? '';
            // Excluir inhabilitados
            if (in_array($doc, $inhabilitados)) {
                continue;
            }

            $aulaId = $dist->aula_id;
            if (!$aulaId) continue;

            if (!isset($aulasData[$aulaId])) {
                $aulasData[$aulaId] = [
                    'aula_nombre' => $dist->aula->nombre ?? '---',
                    'piso' => $dist->aula->piso ?? '---',
                    'supervisores' => [],
                    'estudiantes' => [],
                ];
            }

            $postulacion = Postulacion::where('estudiante_id', $estudiante->id)
                                      ->where('ciclo_id', $cicloActivo->id)
                                      ->first();

            $aulasData[$aulaId]['estudiantes'][] = [
                'dni' => $doc,
                'codigo' => $postulacion ? $postulacion->codigo_postulante : $inscripcion->codigo_inscripcion,
                'nombres' => $estudiante->apellido_paterno . ' ' . ($estudiante->apellido_materno ?? '') . ', ' . $estudiante->nombre,
                'asiento' => $dist->numero_asiento,
                'carrera' => $inscripcion->carrera->nombre ?? 'N/A',
            ];
        }

        // Attach supervisors to their respective Aulas
        foreach ($supervisores as $sup) {
            $aulaId = $sup->aula_id;
            if (isset($aulasData[$aulaId])) {
                $docenteNombre = $sup->docente ? ($sup->docente->nombre . ' ' . $sup->docente->apellido_paterno) : 'POR ASIGNAR';
                
                // Count students within this supervisor's range (excluding inhabilitados)
                $estudiantesEnRango = array_filter($aulasData[$aulaId]['estudiantes'], function($est) use ($sup) {
                    return $est['asiento'] >= $sup->rango_inicio && $est['asiento'] <= $sup->rango_fin;
                });
                
                $aulasData[$aulaId]['supervisores'][] = [
                    'nombre' => $docenteNombre,
                    'rango_inicio' => $sup->rango_inicio,
                    'rango_fin' => $sup->rango_fin,
                    'cantidad' => count($estudiantesEnRango)
                ];
            }
        }

        // Sort Aulas by name
        usort($aulasData, function($a, $b) {
            return strnatcmp($a['aula_nombre'], $b['aula_nombre']);
        });

        // Sort students within each Aula alphabetically
        foreach ($aulasData as &$aula) {
            usort($aula['estudiantes'], function ($a, $b) {
                return strcasecmp($a['nombres'], $b['nombres']);
            });
        }

        $pdf = Pdf::loadView('tarjetas.pdf_puerta', [
            'aulas' => $aulasData,
            'examen_nombre' => $examenNombre,
        ])->setPaper('a4', 'landscape');

        return $pdf->download('lista_puerta_examen_' . $examenNumero . '_' . date('YmdHis') . '.pdf');
    }

    public function exportarActaAulaPDF(Request $request)
    {
        set_time_limit(300);
        ini_set('memory_limit', '1024M');

        $examenNumero = $request->input('examen_numero', 1);
        $cicloActivo = Ciclo::where('es_activo', true)->first();
        if (!$cicloActivo) {
            return response()->json(['error' => 'No hay ciclo activo'], 404);
        }

        $examenPeriodo = AsistenciaHelper::getExamenPeriodoPorId($cicloActivo, $examenNumero);
        $examenNombre = $examenPeriodo ? $examenPeriodo['nombre'] : 'Examen ' . $examenNumero;

        $inhabilitados = AsistenciaHelper::obtenerDocumentosInhabilitados($cicloActivo, $examenNumero);

        $supervisores = ExamenDistribucion::where('ciclo_id', $cicloActivo->id)
            ->where('examen_numero', $examenNumero)
            ->with(['aula', 'docente'])
            ->get()
            ->sortBy(function($s) {
                return ($s->aula->nombre ?? '') . '-' . str_pad($s->rango_inicio, 5, '0', STR_PAD_LEFT);
            });

        if ($supervisores->isEmpty()) {
            return redirect()->back()->with('error', 'No hay distribución de supervisores para este examen.');
        }

        $actasData = [];
        $placeholderBase64 = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=';

        foreach ($supervisores as $sup) {
            $distEstudiantes = ExamenEstudianteDistribucion::where('ciclo_id', $cicloActivo->id)
                ->where('examen_numero', $examenNumero)
                ->where('aula_id', $sup->aula_id)
                ->whereBetween('numero_asiento', [$sup->rango_inicio, $sup->rango_fin])
                ->with(['inscripcion.estudiante', 'inscripcion.carrera'])
                ->get()
                ->sortBy('numero_asiento');

            $estudiantesActa = [];
            $gruposAsignados = [];
            $temasAsignados = [];

            foreach ($distEstudiantes as $distEst) {
                $inscripcion = $distEst->inscripcion;
                $estudiante = $inscripcion->estudiante;
                
                $doc = $estudiante->numero_documento ?? '';
                $isInhabilitado = in_array($doc, $inhabilitados);

                $postulacion = Postulacion::where('estudiante_id', $estudiante->id)
                                          ->where('ciclo_id', $cicloActivo->id)
                                          ->first();

                if ($distEst->grupo) {
                    $gruposAsignados[$distEst->grupo] = true;
                }
                if ($distEst->tema) {
                    $temasAsignados[$distEst->tema] = true;
                }

                $fotoSrcPath = null;
                $fotoPath = $postulacion ? $postulacion->foto_path : null;
                if ($fotoPath) {
                    $fullPath = storage_path('app/public/' . $fotoPath);
                    if (file_exists($fullPath)) {
                        $fotoSrcPath = $fullPath;
                    }
                }

                if (!$fotoSrcPath) {
                    $fotoSrcPath = $placeholderBase64;
                }

                $estudiantesActa[] = [
                    'asiento' => $distEst->numero_asiento,
                    'foto' => $fotoSrcPath,
                    'codigo' => $postulacion ? $postulacion->codigo_postulante : $inscripcion->codigo_inscripcion,
                    'dni' => $doc,
                    'nombres' => $estudiante->apellido_paterno . ' ' . ($estudiante->apellido_materno ?? '') . ' ' . $estudiante->nombre,
                    'carrera' => $inscripcion->carrera->nombre ?? '---',
                    'inhabilitado' => $isInhabilitado,
                ];
            }

            $docenteNombre = null;
            if ($sup->docente_id && $sup->docente) {
                $docenteNombre = $sup->docente->nombre . ' ' . $sup->docente->apellido_paterno;
            } elseif ($sup->docente_invitado) {
                $docenteNombre = $sup->docente_invitado;
            }

            $grupoHeader = !empty($gruposAsignados) ? implode(', ', array_keys($gruposAsignados)) : '---';
            $temaHeader = !empty($temasAsignados) ? implode(', ', array_keys($temasAsignados)) : '---';

            $actasData[] = [
                'examen_nombre' => $examenNombre,
                'ciclo_nombre' => $cicloActivo->nombre,
                'aula_nombre' => $sup->aula->nombre ?? '---',
                'piso' => $sup->aula->piso ?? '---',
                'docente_nombre' => $docenteNombre,
                'rango_inicio' => $sup->rango_inicio,
                'rango_fin' => $sup->rango_fin,
                'cantidad_estudiantes' => count($estudiantesActa),
                'grupo' => $grupoHeader,
                'tema' => $temaHeader,
                'estudiantes' => $estudiantesActa,
            ];
        }

        $pdf = Pdf::loadView('tarjetas.pdf_acta', [
            'actas' => $actasData,
        ])->setPaper('a4', 'portrait');

        return $pdf->download('actas_asistencia_examen_' . $examenNumero . '_' . date('YmdHis') . '.pdf');
    }

    public function exportarResumenPDF(Request $request)
    {
        set_time_limit(300);
        ini_set('memory_limit', '1024M');

        $examenNumero = $request->input('examen_numero', 1);
        $cicloActivo = Ciclo::where('es_activo', true)->first();
        if (!$cicloActivo) {
            return response()->json(['error' => 'No hay ciclo activo'], 404);
        }

        $examenPeriodo = AsistenciaHelper::getExamenPeriodoPorId($cicloActivo, $examenNumero);
        $examenNombre = $examenPeriodo ? $examenPeriodo['nombre'] : 'Examen ' . $examenNumero;

        $distribuciones = ExamenEstudianteDistribucion::where('ciclo_id', $cicloActivo->id)
            ->where('examen_numero', $examenNumero)
            ->with(['aula'])
            ->get();

        $supervisores = ExamenDistribucion::where('ciclo_id', $cicloActivo->id)
            ->where('examen_numero', $examenNumero)
            ->with(['aula', 'docente'])
            ->get();

        $resumenAulas = [];

        foreach ($distribuciones as $dist) {
            $aulaId = $dist->aula_id;
            if (!$aulaId) continue;

            $aulaNombre = $dist->aula->nombre ?? '---';
            $key = $aulaId . '-' . $dist->tema . '-' . $dist->grupo;

            if (!isset($resumenAulas[$key])) {
                $resumenAulas[$key] = [
                    'aula_nombre' => $aulaNombre,
                    'tema' => $dist->tema ?? '---',
                    'grupo' => $dist->grupo ?? '---',
                    'cantidad' => 0,
                ];
            }
            $resumenAulas[$key]['cantidad']++;
        }

        foreach ($resumenAulas as $key => &$item) {
            $aulaNombre = $item['aula_nombre'];
            $aulaSups = $supervisores->filter(function($sup) use ($aulaNombre) {
                return ($sup->aula->nombre ?? '') == $aulaNombre;
            });

            $supTexts = [];
            foreach ($aulaSups as $sup) {
                $docenteNombre = null;
                if ($sup->docente_id && $sup->docente) {
                    $docenteNombre = $sup->docente->nombre . ' ' . $sup->docente->apellido_paterno;
                } elseif ($sup->docente_invitado) {
                    $docenteNombre = $sup->docente_invitado;
                } else {
                    $docenteNombre = 'PTE. ASIGNACIÓN';
                }
                $supTexts[] = $docenteNombre . " (Carpetas " . $sup->rango_inicio . "-" . $sup->rango_fin . ")";
            }

            $item['supervisores_texto'] = !empty($supTexts) ? implode(', ', $supTexts) : 'PTE. ASIGNACIÓN';
        }

        usort($resumenAulas, function($a, $b) {
            return strnatcmp($a['aula_nombre'], $b['aula_nombre']);
        });

        $pdf = Pdf::loadView('tarjetas.pdf_resumen_aulas', [
            'resumen' => $resumenAulas,
            'examen_nombre' => $examenNombre,
            'ciclo_nombre' => $cicloActivo->nombre
        ])->setPaper('a4', 'portrait');

        return $pdf->download('resumen_aulas_examen_' . $examenNumero . '_' . date('YmdHis') . '.pdf');
    }

    public function getEdificioData(Request $request)
    {
        try {
            $cicloActivo = Ciclo::where('es_activo', true)->first();
            if (!$cicloActivo) return response()->json(['error' => 'No hay ciclo activo'], 404);

            $examenNumero = $request->input('examen_numero', 1);

            $aulas = \App\Models\Aula::where('estado', true)->get()->map(function($aula) {
                if (!$aula->piso && is_numeric($aula->nombre)) {
                    $aula->piso = substr($aula->nombre, 0, 1);
                }
                return $aula;
            })->values();

            $distribucionesGrouped = ExamenDistribucion::with('docente')
                ->where('ciclo_id', $cicloActivo->id)
                ->where('examen_numero', $examenNumero)
                ->get()
                ->groupBy('aula_id');

            $docentes = \App\Models\User::whereHas('roles', function($q) {
                $q->whereIn('nombre', ['profesor', 'docente', 'admin']); 
            })->select('id', 'nombre', 'apellido_paterno', 'apellido_materno')->get();

            $aulasData = $aulas->map(function($aula) use ($distribucionesGrouped, $cicloActivo, $examenNumero) {
                $aulaDists = $distribucionesGrouped->get($aula->id) ?? collect();
                
                $supervisores = [];
                foreach ($aulaDists as $dist) {
                    $docenteNombre = null;
                    if ($dist->docente_id && $dist->docente) {
                        $docenteNombre = $dist->docente->nombre . ' ' . $dist->docente->apellido_paterno;
                    } elseif ($dist->docente_invitado) {
                        $docenteNombre = $dist->docente_invitado;
                    }

                    $supervisores[] = [
                        'id' => $dist->id,
                        'docente_id' => $dist->docente_id,
                        'docente_invitado' => $dist->docente_invitado,
                        'docente_nombre' => $docenteNombre ?: 'PTE. ASIGNACIÓN',
                        'rango_inicio' => $dist->rango_inicio,
                        'rango_fin' => $dist->rango_fin,
                        'cantidad_estudiantes' => $dist->cantidad_estudiantes
                    ];
                }

                // Ordenar supervisores por rango_inicio
                usort($supervisores, function($a, $b) {
                    return $a['rango_inicio'] - $b['rango_inicio'];
                });

                $cantidadEstudiantes = ExamenEstudianteDistribucion::where('ciclo_id', $cicloActivo->id)
                    ->where('examen_numero', $examenNumero)
                    ->where('aula_id', $aula->id)
                    ->count();

                // Para compatibilidad con frontend anterior si lo requiere
                $primerSupervisor = reset($supervisores);

                return [
                    'id' => $aula->id,
                    'nombre' => $aula->nombre,
                    'piso' => $aula->piso,
                    'capacidad' => $aula->capacidad,
                    'supervisores' => $supervisores,
                    'docente_id' => $primerSupervisor ? $primerSupervisor['docente_id'] : null,
                    'docente_invitado' => $primerSupervisor ? $primerSupervisor['docente_invitado'] : null,
                    'docente_nombre' => $primerSupervisor ? $primerSupervisor['docente_nombre'] : null,
                    'cantidad_estudiantes' => $cantidadEstudiantes,
                    'ocupacion_real' => $cantidadEstudiantes
                ];
            });

            // Calculate floor-level statistics and map floors
            $pisosConMetadata = $aulasData->groupBy('piso')->sortKeysDesc()->map(function($aulasInPiso, $pisoKey) {
                $capacidadTotal = $aulasInPiso->sum('capacidad');
                $estudiantesAsignados = $aulasInPiso->sum('cantidad_estudiantes');
                return [
                    'piso' => $pisoKey,
                    'capacidad_total' => $capacidadTotal,
                    'estudiantes_asignados' => $estudiantesAsignados,
                    'aulas' => $aulasInPiso->values()->toArray()
                ];
            });

            // Calculate overall summaries
            $totalAulasActivas = $aulas->count();
            $totalCapacidadAulas = $aulas->sum('capacidad');

            // Total students eligible (aptos) for the active cycle
            $inhabilitados = AsistenciaHelper::obtenerDocumentosInhabilitados($cicloActivo, $examenNumero);

            // Total already distributed for this exam
            $totalEstudiantesAsignados = ExamenEstudianteDistribucion::where('ciclo_id', $cicloActivo->id)
                ->where('examen_numero', $examenNumero)
                ->count();

            $assignedInscripcionIds = ExamenEstudianteDistribucion::where('ciclo_id', $cicloActivo->id)
                ->where('examen_numero', $examenNumero)
                ->pluck('inscripcion_id')
                ->toArray();

            // Determine if current distribution includes inhabilitados
            $incluyeInhabilitados = false;
            if ($totalEstudiantesAsignados > 0) {
                $assignedDocs = Inscripcion::whereIn('id', $assignedInscripcionIds)
                    ->with('estudiante')
                    ->get()
                    ->pluck('estudiante.numero_documento')
                    ->filter()
                    ->toArray();
                
                $intersect = array_intersect($assignedDocs, $inhabilitados);
                if (count($intersect) > 0) {
                    $incluyeInhabilitados = true;
                }
            } else {
                // Default to excluding them if no distribution yet
                $incluyeInhabilitados = false;
            }

            // Total students eligible (aptos) for the active cycle
            $inscripcionesQuery = Inscripcion::where('ciclo_id', $cicloActivo->id)
                ->where('estado_inscripcion', 'activo')
                ->whereHas('estudiante.postulaciones', function ($query) use ($cicloActivo) {
                    $query->where('ciclo_id', $cicloActivo->id)->where('estado', 'aprobado');
                });

            if ($incluyeInhabilitados) {
                $totalEstudiantesAptos = $inscripcionesQuery->count();
                $notAssignedInscripciones = Inscripcion::where('ciclo_id', $cicloActivo->id)
                    ->where('estado_inscripcion', 'activo')
                    ->whereHas('estudiante.postulaciones', function ($query) use ($cicloActivo) {
                        $query->where('ciclo_id', $cicloActivo->id)->where('estado', 'aprobado');
                    })
                    ->whereNotIn('id', $assignedInscripcionIds)
                    ->with('carrera')
                    ->get();
            } else {
                $inscripcionesList = $inscripcionesQuery->with('estudiante')->get();
                $inscripcionesAptasHabilitadas = $inscripcionesList->filter(function ($insc) use ($inhabilitados) {
                    return !in_array($insc->estudiante->numero_documento, $inhabilitados);
                });

                $totalEstudiantesAptos = $inscripcionesAptasHabilitadas->count();
                
                $notAssignedInscripciones = $inscripcionesAptasHabilitadas
                    ->whereNotIn('id', $assignedInscripcionIds)
                    ->load('carrera');
            }

            $totalEstudiantesFaltantes = max(0, $totalEstudiantesAptos - $totalEstudiantesAsignados);

            // Group by group and career
            $faltantesPorGrupoYCarrera = [];
            foreach ($notAssignedInscripciones as $insc) {
                $grupo = $insc->carrera->grupo ?? 'A';
                $carrera = $insc->carrera->nombre ?? 'N/A';
                
                if (!isset($faltantesPorGrupoYCarrera[$grupo])) {
                    $faltantesPorGrupoYCarrera[$grupo] = [
                        'grupo' => $grupo,
                        'total' => 0,
                        'carreras' => []
                    ];
                }
                $faltantesPorGrupoYCarrera[$grupo]['total']++;
                
                if (!isset($faltantesPorGrupoYCarrera[$grupo]['carreras'][$carrera])) {
                    $faltantesPorGrupoYCarrera[$grupo]['carreras'][$carrera] = 0;
                }
                $faltantesPorGrupoYCarrera[$grupo]['carreras'][$carrera]++;
            }

            // Convert carreras associative array to a indexed list for easy JS handling
            foreach ($faltantesPorGrupoYCarrera as $gKey => $gData) {
                $carrerasList = [];
                foreach ($gData['carreras'] as $cName => $count) {
                    $carrerasList[] = [
                        'nombre' => $cName,
                        'cantidad' => $count
                    ];
                }
                usort($carrerasList, function($a, $b) {
                    return $b['cantidad'] - $a['cantidad']; // Sort desc by quantity
                });
                $faltantesPorGrupoYCarrera[$gKey]['carreras'] = $carrerasList;
            }

            // Sort groups alphabetically
            ksort($faltantesPorGrupoYCarrera);

            $examenPeriodo = AsistenciaHelper::getExamenPeriodoPorId($cicloActivo, $examenNumero);
            $tituloExamen = mb_strtoupper($examenPeriodo ? $examenPeriodo['nombre'] : 'EXAMEN') . " DEL CEPRE-UNAMAD " . ($cicloActivo->nombre ?? 'CICLO ACTUAL');

            return response()->json([
                'pisos' => $pisosConMetadata,
                'docentes' => $docentes,
                'titulo_examen' => $tituloExamen,
                'resumen' => [
                    'total_aulas' => $totalAulasActivas,
                    'capacidad_total' => $totalCapacidadAulas,
                    'total_aptos' => $totalEstudiantesAptos,
                    'total_asignados' => $totalEstudiantesAsignados,
                    'total_faltantes' => $totalEstudiantesFaltantes,
                    'faltantes_detalle' => array_values($faltantesPorGrupoYCarrera)
                ]
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

            $examenNumero = $request->input('examen_numero', 1);

            \DB::beginTransaction();

            // 1. Limpiar asignaciones previas para este examen
            ExamenEstudianteDistribucion::where('ciclo_id', $cicloActivo->id)
                ->where('examen_numero', $examenNumero)
                ->delete();

            ExamenDistribucion::where('ciclo_id', $cicloActivo->id)
                ->where('examen_numero', $examenNumero)
                ->delete();

            // 2. Obtener todos los estudiantes inscritos y aptos en el ciclo
            $inscripciones = Inscripcion::where('ciclo_id', $cicloActivo->id)
                ->where('estado_inscripcion', 'activo')
                ->whereHas('estudiante.postulaciones', function ($query) use ($cicloActivo) {
                    $query->where('ciclo_id', $cicloActivo->id)->where('estado', 'aprobado');
                })
                ->with(['estudiante', 'carrera'])
                ->get();

            $excluirInhabilitados = $request->input('excluir_inhabilitados', 1);
            if ($excluirInhabilitados) {
                $inhabilitados = AsistenciaHelper::obtenerDocumentosInhabilitados($cicloActivo, $examenNumero);
                $inscripciones = $inscripciones->filter(function($inscripcion) use ($inhabilitados) {
                    return !in_array($inscripcion->estudiante->numero_documento, $inhabilitados);
                })->values();
            }

            if ($inscripciones->isEmpty()) {
                return response()->json(['error' => 'No hay estudiantes aptos para distribuir'], 400);
            }

            // 3. Obtener Aulas Disponibles con capacidad > 0
            $aulas = \App\Models\Aula::where('estado', true)
                ->where('capacidad', '>', 0)
                ->orderBy('nombre')
                ->get()
                ->values();

            if ($aulas->isEmpty()) {
                return response()->json(['error' => 'No hay aulas disponibles con capacidad'], 400);
            }

            // 4. Determinar tipo de distribución
            $tipoDistribucion = $request->input('tipo_distribucion', 'ordenada');
            $inscripcionesFinales = null;

            if ($tipoDistribucion === 'aleatoria') {
                $inscripcionesFinales = $inscripciones->shuffle();
            } else {
                // Ordenar estudiantes de forma organizada por Grupo, Carrera y Apellidos/Nombres
                $inscripcionesFinales = $inscripciones->sortBy(function($inscripcion) {
                    $grupo = $inscripcion->carrera->grupo ?? 'A';
                    $carrera = $inscripcion->carrera->nombre ?? '';
                    $estudiante = $inscripcion->estudiante;
                    $nombreCompleto = $estudiante ? ($estudiante->apellido_paterno . ' ' . ($estudiante->apellido_materno ?? '') . ' ' . $estudiante->nombre) : '';
                    return sprintf('%s-%s-%s', $grupo, $carrera, $nombreCompleto);
                })->values();
            }

            // 5. Distribuir estudiantes secuencialmente en las aulas
            $aulaIndex = 0;
            $estudiantesAsignados = 0;
            $conteoPorAula = [];

            // Cargar configs de grupos del ciclo
            $configsRaw = ExamenGrupoConfig::where('ciclo_id', $cicloActivo->id)->get();
            $defaultTemas = ['A' => 'P', 'B' => 'Q', 'C' => 'R'];

            foreach ($inscripcionesFinales as $inscripcion) {
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

                if (!isset($conteoPorAula[$aula->id])) {
                    $conteoPorAula[$aula->id] = 0;
                }
                $conteoPorAula[$aula->id]++;
                $asiento = $conteoPorAula[$aula->id];

                // Determinar grupo y tema
                $grupo = $inscripcion->carrera->grupo ?? 'A';
                $config = $configsRaw->where('grupo', $grupo)->first();
                $tema = $config ? $config->tema : ($defaultTemas[$grupo] ?? 'R');

                ExamenEstudianteDistribucion::create([
                    'ciclo_id' => $cicloActivo->id,
                    'examen_numero' => $examenNumero,
                    'inscripcion_id' => $inscripcion->id,
                    'aula_id' => $aula->id,
                    'numero_asiento' => $asiento,
                    'tema' => $tema,
                    'grupo' => $grupo
                ]);

                $estudiantesAsignados++;
            }

            // 6. Generar registros en ExamenDistribucion (Supervisores)
            foreach ($aulas as $aula) {
                $cantidad = $conteoPorAula[$aula->id] ?? 0;
                if ($cantidad > 0) {
                    ExamenDistribucion::create([
                        'ciclo_id' => $cicloActivo->id,
                        'aula_id' => $aula->id,
                        'examen_numero' => $examenNumero,
                        'docente_id' => null,
                        'docente_invitado' => null,
                        'rango_inicio' => 1,
                        'rango_fin' => $cantidad,
                        'cantidad_estudiantes' => $cantidad
                    ]);
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
            $distribucionId = $request->input('distribucion_id');
            $valor = $request->input('docente_valor'); // ID numérico o nombre de invitado

            $distribucion = ExamenDistribucion::findOrFail($distribucionId);

            if (is_numeric($valor)) {
                $distribucion->docente_id = $valor;
                $distribucion->docente_invitado = null;
            } else {
                $distribucion->docente_id = null;
                $distribucion->docente_invitado = $valor;
            }

            $distribucion->save();

            return response()->json(['message' => 'Docente asignado correctamente']);

        } catch (\Exception $e) {
            \Log::error('Error en guardarDistribucionDocente: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getAulaDetalle(Request $request, $id)
    {
        try {
            $cicloActivo = Ciclo::where('es_activo', true)->first();
            if (!$cicloActivo) return response()->json(['error' => 'No hay ciclo activo'], 404);

            $examenNumero = $request->input('examen_numero', 1);
            $aula = \App\Models\Aula::findOrFail($id);
            
            $estudiantes = ExamenEstudianteDistribucion::where('aula_id', $id)
                ->where('ciclo_id', $cicloActivo->id)
                ->where('examen_numero', $examenNumero)
                ->with(['inscripcion.estudiante', 'inscripcion.estudiante.postulaciones' => function($q) use ($cicloActivo) {
                    $q->where('ciclo_id', $cicloActivo->id);
                }])
                ->get()
                ->map(function($dist) use ($cicloActivo) {
                    $inscripcion = $dist->inscripcion;
                    $estudiante = $inscripcion->estudiante;
                    $postulacion = $estudiante->postulaciones->first();
                    return [
                        'id' => $estudiante->id,
                        'nombre_completo' => $estudiante->nombre_completo,
                        'foto' => $postulacion && $postulacion->foto_path ? asset('storage/' . $postulacion->foto_path) : null,
                        'codigo' => $postulacion ? $postulacion->codigo_postulante : '---',
                        'asiento' => $dist->numero_asiento,
                        'tema' => $dist->tema,
                        'grupo' => $dist->grupo
                    ];
                })
                ->sortBy('asiento')
                ->values();

            return response()->json([
                'aula' => $aula,
                'estudiantes' => $estudiantes
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function dividirSupervisor(Request $request)
    {
        $request->validate([
            'distribucion_id' => 'required|exists:examen_distribucion,id',
            'rango_corte' => 'required|integer'
        ]);

        $dist = ExamenDistribucion::findOrFail($request->distribucion_id);
        $corte = $request->rango_corte;

        if ($corte < $dist->rango_inicio || $corte >= $dist->rango_fin) {
            return response()->json(['error' => 'El asiento de corte debe estar dentro del rango actual (sin incluir el límite final).'], 422);
        }

        \DB::beginTransaction();
        try {
            $finOriginal = $dist->rango_fin;
            
            // Modificar el rango actual
            $dist->rango_fin = $corte;
            
            // Recalcular cantidad de estudiantes para el primer rango
            $cant1 = ExamenEstudianteDistribucion::where('ciclo_id', $dist->ciclo_id)
                ->where('examen_numero', $dist->examen_numero)
                ->where('aula_id', $dist->aula_id)
                ->whereBetween('numero_asiento', [$dist->rango_inicio, $corte])
                ->count();
            $dist->cantidad_estudiantes = $cant1;
            $dist->save();

            // Crear el nuevo supervisor para el segundo rango
            $cant2 = ExamenEstudianteDistribucion::where('ciclo_id', $dist->ciclo_id)
                ->where('examen_numero', $dist->examen_numero)
                ->where('aula_id', $dist->aula_id)
                ->whereBetween('numero_asiento', [$corte + 1, $finOriginal])
                ->count();

            ExamenDistribucion::create([
                'ciclo_id' => $dist->ciclo_id,
                'aula_id' => $dist->aula_id,
                'examen_numero' => $dist->examen_numero,
                'docente_id' => null,
                'docente_invitado' => null,
                'rango_inicio' => $corte + 1,
                'rango_fin' => $finOriginal,
                'cantidad_estudiantes' => $cant2
            ]);

            \DB::commit();
            return response()->json(['message' => 'Supervisor dividido correctamente.']);
        } catch (\Exception $e) {
            \DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function eliminarSupervisor(Request $request)
    {
        $request->validate([
            'distribucion_id' => 'required|exists:examen_distribucion,id'
        ]);

        $dist = ExamenDistribucion::findOrFail($request->distribucion_id);

        \DB::beginTransaction();
        try {
            // Buscar si hay otro supervisor en la misma aula/examen
            $otros = ExamenDistribucion::where('ciclo_id', $dist->ciclo_id)
                ->where('examen_numero', $dist->examen_numero)
                ->where('aula_id', $dist->aula_id)
                ->where('id', '!=', $dist->id)
                ->orderBy('rango_inicio')
                ->get();

            if ($otros->isEmpty()) {
                return response()->json(['error' => 'No se puede eliminar el único supervisor del aula.'], 422);
            }

            // Encontrar el supervisor adyacente para fusionar
            $adyacente = null;
            foreach ($otros as $otro) {
                if ($otro->rango_fin == $dist->rango_inicio - 1) {
                    $adyacente = $otro;
                    $adyacente->rango_fin = $dist->rango_fin;
                    break;
                }
            }

            if (!$adyacente) {
                foreach ($otros as $otro) {
                    if ($otro->rango_inicio == $dist->rango_fin + 1) {
                        $adyacente = $otro;
                        $adyacente->rango_inicio = $dist->rango_inicio;
                        break;
                    }
                }
            }

            if ($adyacente) {
                $cant = ExamenEstudianteDistribucion::where('ciclo_id', $adyacente->ciclo_id)
                    ->where('examen_numero', $adyacente->examen_numero)
                    ->where('aula_id', $adyacente->aula_id)
                    ->whereBetween('numero_asiento', [$adyacente->rango_inicio, $adyacente->rango_fin])
                    ->count();
                $adyacente->cantidad_estudiantes = $cant;
                $adyacente->save();
            } else {
                $primer = $otros->first();
                $primer->rango_inicio = min($primer->rango_inicio, $dist->rango_inicio);
                $primer->rango_fin = max($primer->rango_fin, $dist->rango_fin);
                $cant = ExamenEstudianteDistribucion::where('ciclo_id', $primer->ciclo_id)
                    ->where('examen_numero', $primer->examen_numero)
                    ->where('aula_id', $primer->aula_id)
                    ->whereBetween('numero_asiento', [$primer->rango_inicio, $primer->rango_fin])
                    ->count();
                $primer->cantidad_estudiantes = $cant;
                $primer->save();
            }

            $dist->delete();

            \DB::commit();
            return response()->json(['message' => 'Supervisor eliminado y rango fusionado correctamente.']);
        } catch (\Exception $e) {
            \DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function guardarAula(Request $request)
    {
        try {
            $data = $request->validate([
                'id' => 'nullable|integer',
                'nombre' => 'required|string',
                'piso' => 'required|integer',
                'capacidad' => 'required|integer|min:1'
            ]);

            $codigo = 'A-' . $data['nombre'];

            if (!empty($data['id'])) {
                $aula = \App\Models\Aula::findOrFail($data['id']);
                $aula->update([
                    'nombre' => $data['nombre'],
                    'piso' => $data['piso'],
                    'capacidad' => $data['capacidad'],
                    'codigo' => $codigo
                ]);
            } else {
                $aula = \App\Models\Aula::updateOrCreate(
                    ['codigo' => $codigo], // Buscar por código que es el campo único
                    [
                        'nombre' => $data['nombre'],
                        'piso' => $data['piso'],
                        'capacidad' => $data['capacidad'],
                        'estado' => true,
                        'tipo' => 'Aula'
                    ]
                );
            }

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
                $codigoAula = 'A-' . $nombreAula;
                
                \App\Models\Aula::updateOrCreate(
                    ['codigo' => $codigoAula], // Buscar por código que es el campo único
                    [
                        'nombre' => $nombreAula,
                        'piso' => $piso,
                        'capacidad' => $data['capacidad_default'],
                        'estado' => true,
                        'tipo' => 'Aula'
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
            
            try {
                // Intentar eliminación física
                $aula->delete();
            } catch (\Exception $ex) {
                // Si falla por llaves foráneas, desactivar
                $aula->estado = false;
                $aula->save();
            }
            
            return response()->json(['message' => 'Aula eliminada correctamente']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    private function limpiarTexto($texto)
    {
        if (empty($texto)) return '';
        // Primero, remover emojis y caracteres especiales no soportados usando regex
        $regex = '/[\x{1F600}-\x{1F64F}\x{1F300}-\x{1F5FF}\x{1F680}-\x{1F6FF}\x{2600}-\x{26FF}\x{2700}-\x{27BF}\x{1F1E0}-\x{1F1FF}\x{1F900}-\x{1F9FF}\x{1F018}-\x{1F093}\x{1F100}-\x{1F1FF}\x{1F200}-\x{1F2FF}\x{1F300}-\x{1F5FF}\x{1F600}-\x{1F64F}\x{1F680}-\x{1F6FF}\x{1F700}-\x{1F77F}\x{1F780}-\x{1F7FF}\x{1F800}-\x{1F8FF}\x{1F900}-\x{1F9FF}\x{1FA00}-\x{1FA6F}\x{1FA70}-\x{1FAFF}]/u';
        $texto = preg_replace($regex, '', $texto);
        
        // Quitar signos de interrogación remanentes al inicio
        $texto = ltrim($texto, '? ');
        
        // Quitar cualquier carácter extraño al inicio que no sea letra, número o paréntesis
        $texto = preg_replace('/^[^a-zA-Z0-9áéíóúñÁÉÍÓÚÑüÜ(]+/u', '', $texto);
        
        return trim($texto);
    }
}