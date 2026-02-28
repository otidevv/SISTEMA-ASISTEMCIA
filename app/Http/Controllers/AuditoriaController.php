<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;
use Yajra\DataTables\Facades\DataTables;

class AuditoriaController extends Controller
{
    public function index(Request $request)
    {
        // Validación de permisos
        if (!auth()->user()->hasPermission('auditoria.view') && !auth()->user()->hasPermission('users.view')) {
            abort(403);
        }

        if ($request->ajax()) {
            $activities = Activity::with(['causer', 'subject'])->latest();
            
            return DataTables::of($activities)
                ->addColumn('fecha', function ($row) {
                    return $row->created_at->format('d/m/Y H:i:s');
                })
                ->addColumn('responsable', function ($row) {
                    if ($row->causer) {
                        return $row->causer->nombre . ' ' . $row->causer->apellido_paterno;
                    }
                    return 'Sistema (Automático)';
                })
                ->addColumn('accion_badge', function ($row) {
                    $badges = [
                        'created' => '<span class="badge" style="background-color: rgba(67, 211, 158, 0.18); color: #43d39e;"><i class="uil uil-plus-circle"></i> Creación</span>',
                        'updated' => '<span class="badge" style="background-color: rgba(255, 190, 11, 0.18); color: #ffbe0b;"><i class="uil uil-edit"></i> Modificación</span>',
                        'deleted' => '<span class="badge" style="background-color: rgba(255, 92, 117, 0.18); color: #ff5c75;"><i class="uil uil-trash-alt"></i> Eliminación</span>'
                    ];
                    return $badges[$row->event] ?? '<span class="badge bg-info">' . ucfirst($row->event) . '</span>';
                })
                ->addColumn('modulo_legible', function ($row) {
                    $modelClass = class_basename($row->subject_type); // De "App\Models\User" a "User"
                    $nombreModulo = preg_replace('/(?<!^)([A-Z])/', ' $1', $modelClass); // "User Session"
                    
                    // Traducciones amigables para los modales más comunes
                    $traducciones = [
                        'User' => 'Usuario',
                        'Role' => 'Roles y Permisos',
                        'Inscripcion' => 'Inscripción de Estudiante',
                        'Ciclo' => 'Ciclo Académico',
                        'Aula' => 'Aula',
                        'Curso' => 'Curso',
                        'Pago Docente' => 'Pago de Docente',
                        'Asistencia Docente' => 'Registro Asistencia',
                        'Material Academico' => 'Material Académico',
                        'Anuncio' => 'Anuncio'
                    ];

                    $nombreFinal = $traducciones[$nombreModulo] ?? $nombreModulo;

                    // Intentar obtener un nombre descriptivo del sujeto modificado
                    $detalleSujeto = "ID Registro: {$row->subject_id}";
                    if ($row->subject) {
                        // Casos específicos por modelo
                        if (isset($row->subject->codigo)) {
                            $detalleSujeto = "Código: " . $row->subject->codigo;
                        } elseif (isset($row->subject->nombre) && isset($row->subject->apellido_paterno)) {
                            $detalleSujeto = $row->subject->nombre . " " . $row->subject->apellido_paterno;
                        } elseif (isset($row->subject->nombre)) {
                            $detalleSujeto = $row->subject->nombre;
                        } elseif (isset($row->subject->titulo)) {
                            $detalleSujeto = $row->subject->titulo;
                        }
                    }

                    return "<b>" . htmlspecialchars($nombreFinal) . "</b><br><small class='text-muted'>{$detalleSujeto}</small>";
                })
                ->addColumn('detalles_html', function ($row) {
                    $props = $row->properties;
                    $old = $props->has('old') ? $props['old'] : [];
                    $new = $props->has('attributes') ? $props['attributes'] : [];
                    
                    if (empty($old) && empty($new)) {
                        return '<div class="alert alert-info py-2 mb-0 border-0"><small>Sin detalles registrados (solo evento de ' . $row->event . ')</small></div>';
                    }

                    $html = '<div class="table-responsive"><table class="table table-sm table-bordered text-start fs-13 mb-0">';
                    $html .= '<thead class="table-light"><tr><th style="width: 30%">Campo</th><th style="width: 35%">Valor Anterior</th><th style="width: 35%">Nuevo Valor</th></tr></thead><tbody>';
                    
                    $allKeys = array_unique(array_merge(array_keys($old), array_keys($new)));
                    $hasChanges = false;

                    foreach ($allKeys as $key) {
                        // Omitir timestamps ruidosos
                        if (in_array($key, ['updated_at', 'created_at', 'deleted_at'])) continue;
                        
                        $oldVal = array_key_exists($key, $old) ? $old[$key] : null;
                        $newVal = array_key_exists($key, $new) ? $new[$key] : null;
                        
                        // Si son exactamente iguales, los obviamos (o si ambos son nulos/vacios de la misma forma)
                        if ($oldVal === $newVal) continue; 
                        
                        $hasChanges = true;

                        // Función helper para formatear visualmente
                        $formatValue = function($val) {
                            if (is_null($val)) return '<em class="text-muted">(Nulo)</em>';
                            if ($val === '') return '<em class="text-muted">(Vacío)</em>';
                            if (is_bool($val)) return $val ? 'Sí' : 'No';
                            
                            // Detectar fechas ISO8601 o formato típico de dateTime (2026-01-20T21:52:06... / 2026-01-20 21:52:06)
                            if (is_string($val) && preg_match('/^\d{4}-\d{2}-\d{2}(T|\s)\d{2}:\d{2}:\d{2}/', $val)) {
                                try {
                                    return '<span class="text-primary"><i class="uil uil-clock"></i> ' . \Carbon\Carbon::parse($val)->setTimezone('America/Lima')->format('d/m/Y - h:i:s A') . '</span>';
                                } catch (\Exception $e) {
                                    return htmlspecialchars((string) $val);
                                }
                            }

                            return htmlspecialchars((string) $val);
                        };

                        $oldDisplay = $formatValue($oldVal);
                        $newDisplay = $formatValue($newVal);
                        
                        // Resaltar nombres de campos (traducir comunes)
                        $campoLegible = ucwords(str_replace('_', ' ', $key));

                        $html .= "<tr>";
                        $html .= "<td class='fw-bold text-dark'>{$campoLegible}</td>";
                        $html .= "<td class='text-danger' style='word-break: break-all;'>{$oldDisplay}</td>";
                        $html .= "<td class='text-success' style='word-break: break-all;'>{$newDisplay}</td>";
                        $html .= "</tr>";
                    }
                    $html .= '</tbody></table></div>';

                    if (!$hasChanges) {
                        return '<div class="alert alert-info py-2 mb-0 border-0"><small>No se detectaron variaciones en campos clave.</small></div>';
                    }

                    return $html;
                })
                ->rawColumns(['accion_badge', 'modulo_legible', 'detalles_html'])
                ->make(true);
        }

        return view('auditoria.index');
    }
}
