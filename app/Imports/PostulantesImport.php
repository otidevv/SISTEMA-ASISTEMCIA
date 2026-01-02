<?php

namespace App\Imports;

use App\Models\User;
use App\Models\Postulacion;
use App\Models\Carrera;
use App\Models\Turno;
use App\Models\Ciclo;
use App\Models\Parentesco;
use App\Models\CentroEducativo;
use App\Models\Role;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PostulantesImport implements ToCollection, WithHeadingRow
{
    public $resultados = ['procesados' => 0, 'creados' => 0, 'errores' => []];
    private $cicloActivo;
    private $carreras;
    private $turnos;
    protected $simulacro;

    public function __construct($simulacro = false)
    {
        $this->simulacro = $simulacro;
        $this->cicloActivo = Ciclo::where('es_activo', true)->first();
        $this->carreras = Carrera::all()->map(function($c) {
            $c->nombre_lower = strtolower($c->nombre);
            return $c;
        })->toArray(); 
        
        $this->turnos = Turno::all()->map(function($t) {
            $t->nombre_lower = strtolower($t->nombre);
            return $t;
        });
    }

    public function collection(Collection $rows)
    {
        if (!$this->cicloActivo) {
            $this->resultados['errores'][] = "No hay un ciclo activo en el sistema.";
            return;
        }

        DB::beginTransaction();

        try {
            $fila = 1;

            foreach ($rows as $index => $row) {
                $fila++; 
                
                $row = $row->mapWithKeys(function ($item, $key) {
                    return [trim($key) => $item];
                });

                // RENIEC Integration
                if (!empty($row['dni']) && empty($row['nombres'])) {
                    try {
                        $dniConsulta = trim($row['dni']);
                        $response = Http::timeout(3)->get('https://apidatos.unamad.edu.pe/api/consulta/' . $dniConsulta);
                        
                        if ($response->successful()) {
                            $apiData = $response->json();
                            if (isset($apiData['DNI'])) {
                                $row['nombres'] = $apiData['NOMBRES'];
                                $row['apellido_paterno'] = $apiData['AP_PAT'];
                                $row['apellido_materno'] = $apiData['AP_MAT'];
                                $row['direccion'] = $apiData['DIRECCION'] ?? ($row['direccion'] ?? '');
                                
                                if (isset($apiData['SEXO']) && empty($row['genero'])) {
                                    $row['genero'] = $apiData['SEXO'] == '2' ? 'F' : 'M';
                                }
                                if (isset($apiData['FECHA_NAC']) && empty($row['fecha_nacimiento'])) {
                                    $row['fecha_nacimiento'] = $apiData['FECHA_NAC']; 
                                }
                            }
                        }
                    } catch (\Throwable $e) { }
                }

                if (empty($row['dni']) || empty($row['nombres']) || empty($row['carrera'])) {
                    $this->resultados['errores'][] = "Fila $fila: Faltan datos (DNI, Nombres o Carrera).";
                    continue;
                }

                try {
                    $dni = trim($row['dni']);
                    
                    // USUARIO (Crear o Actualizar)
                    $usuario = User::firstOrNew(['numero_documento' => $dni]);
                    $esNuevoUsuario = !$usuario->exists;
                    
                    // Actualizar datos básicos (siempre)
                    if ($esNuevoUsuario) {
                        $usuario->username = $dni; 
                        $usuario->password_hash = Hash::make($dni);
                    }
                    
                    $usuario->nombre = strtoupper(trim($row['nombres']));
                    $usuario->apellido_paterno = strtoupper(trim($row['apellido_paterno'] ?? ''));
                    $usuario->apellido_materno = strtoupper(trim($row['apellido_materno'] ?? ''));
                    
                    // Email: solo actualizar si viene en el Excel y no está en uso por otro usuario
                    $emailInput = trim($row['email'] ?? '');
                    if (!empty($emailInput)) {
                        $emailEnUso = User::where('email', $emailInput)
                            ->where('id', '!=', $usuario->id ?? 0)
                            ->exists();
                        if (!$emailEnUso) {
                            $usuario->email = $emailInput;
                        }
                    } elseif ($esNuevoUsuario) {
                        $usuario->email = $dni . '@sistema.local';
                    }

                    // Actualizar otros campos si vienen en el Excel
                    if (!empty($row['telefono'])) {
                        $usuario->telefono = trim($row['telefono']);
                    }
                    if (!empty($row['direccion'])) {
                        $usuario->direccion = $row['direccion'];
                    }
                    
                    // Fecha de nacimiento
                    if (!empty($row['fecha_nacimiento'])) {
                        $val = $row['fecha_nacimiento'];
                        try {
                            if (is_numeric($val)) {
                                $usuario->fecha_nacimiento = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($val);
                            } else {
                                $usuario->fecha_nacimiento = Carbon::parse($val);
                            }
                        } catch (\Throwable $e) { }
                    } elseif ($esNuevoUsuario) {
                        $usuario->fecha_nacimiento = '2000-01-01';
                    }

                    if (!empty($row['genero'])) {
                        $usuario->genero = strtoupper($row['genero']);
                    } elseif ($esNuevoUsuario) {
                        $usuario->genero = 'M';
                    }
                    
                    $usuario->tipo_documento = 'DNI';
                    $usuario->estado = true;
                    $usuario->save();

                    // Asignar rol solo si es nuevo
                    if ($esNuevoUsuario) {
                        $rolPostulante = Role::whereIn('nombre', ['Postulante', 'postulante'])->first();
                        if ($rolPostulante) $usuario->assignRole($rolPostulante->nombre);
                    }

                    // CARRERA
                    $nombreCarrera = trim($row['carrera']);
                    $carreraId = null;
                    foreach ($this->carreras as $c) {
                        if ($c['nombre_lower'] == strtolower($nombreCarrera)) {
                            $carreraId = $c['id'];
                            break;
                        }
                    }
                    if (!$carreraId) {
                        $this->resultados['errores'][] = "Fila $fila: Carrera '$nombreCarrera' no encontrada.";
                        continue;
                    }

                    // TURNO
                    $nombreTurno = trim($row['turno'] ?? 'MAÑANA');
                    $turnoId = null;
                    foreach ($this->turnos as $t) {
                        if (str_contains(strtolower($nombreTurno), $t['nombre_lower'])) {
                            $turnoId = $t['id'];
                            break;
                        }
                    }
                    if (!$turnoId) $turnoId = $this->turnos->first()['id'];

                    // BUSCAR O CREAR POSTULACIÓN
                    $postulacionExistente = Postulacion::where('estudiante_id', $usuario->id)
                        ->where('ciclo_id', $this->cicloActivo->id)
                        ->first();
                    
                    $esNuevaPostulacion = !$postulacionExistente;

                    // COLEGIO (Smart)
                    $colegioId = 1;
                    if (!empty($row['colegio'])) {
                        $nombreColegio = trim($row['colegio']);
                        $col = CentroEducativo::where('cen_edu', 'like', "%{$nombreColegio}%")
                             ->orderByRaw("CASE WHEN d_dpto = 'MADRE DE DIOS' THEN 1 ELSE 2 END")
                             ->first();
                        if ($col) $colegioId = $col->id;
                    }

                    // CÓDIGO POSTULANTE
                    if (!empty($row['codigo_postulante'])) {
                        $nuevoCodigo = trim($row['codigo_postulante']);
                        if (Postulacion::where('codigo_postulante', $nuevoCodigo)->exists()) {
                            $this->resultados['errores'][] = "Fila $fila: Código Postulante '$nuevoCodigo' ya existe.";
                            continue;
                        }
                    } else {
                        $ultimoCodigo = Postulacion::max('codigo_postulante') ?? 100000;
                        $nuevoCodigo = $ultimoCodigo + 1;
                        while (Postulacion::where('codigo_postulante', $nuevoCodigo)->exists()) {
                             $nuevoCodigo++;
                        }
                    }

                    // CREAR O ACTUALIZAR POSTULACIÓN
                    if ($esNuevaPostulacion) {
                        $postulacion = new Postulacion();
                        $postulacion->estudiante_id = $usuario->id;
                        $postulacion->ciclo_id = $this->cicloActivo->id;
                        $postulacion->codigo_postulante = $nuevoCodigo;
                        $postulacion->fecha_postulacion = now();
                        $postulacion->estado = 'aprobado';
                    } else {
                        $postulacion = $postulacionExistente;
                    }
                    
                    // Actualizar campos (siempre)
                    $postulacion->carrera_id = $carreraId;
                    $postulacion->turno_id = $turnoId;
                    $postulacion->tipo_inscripcion = strtolower($row['modalidad'] ?? 'postulante');
                    $postulacion->centro_educativo_id = $colegioId;
                    $postulacion->anio_egreso = $row['anio_egreso'] ?? date('Y');
                    
                    if (!$usuario->hasRole('Postulante')) { 
                         $usuario->assignRole('Postulante'); 
                    }

                    $postulacion->save();

                    // PADRES - Crear si hay nombre o teléfono
                    if (!empty($row['nombre_padre_completo']) || !empty($row['celular_padre'])) {
                        $dniP = !empty($row['dni_padre']) ? trim($row['dni_padre']) : null;
                        $nombrePadre = strtoupper(trim($row['nombre_padre_completo'] ?? 'PADRE'));
                        $celularPadre = trim($row['celular_padre'] ?? '');
                        
                        // Buscar por DNI, username o teléfono
                        if ($dniP) {
                            $padre = User::firstOrNew(['numero_documento' => $dniP]);
                        } elseif ($celularPadre) {
                            // Buscar por username (teléfono) o por campo teléfono
                            $padre = User::where('username', $celularPadre)
                                ->orWhere(function($q) use ($celularPadre) {
                                    $q->where('telefono', $celularPadre)
                                      ->whereHas('roles', function($r) {
                                          $r->whereIn('nombre', ['Padre', 'padre']);
                                      });
                                })
                                ->first();
                            if (!$padre) {
                                $padre = new User();
                            }
                        } else {
                            $padre = new User();
                        }
                        
                        if (!$padre->exists) {
                            // Generar username único
                            if ($dniP) {
                                $padre->username = $dniP;
                                $padre->numero_documento = $dniP;
                                $padre->tipo_documento = 'DNI';
                            } else {
                                // Usar teléfono o generar ID temporal
                                $padre->username = $celularPadre ?: 'padre_' . uniqid();
                                $padre->numero_documento = $celularPadre ?: null;
                                $padre->tipo_documento = $celularPadre ? 'OTRO' : null;
                            }
                            
                            $padre->email = ($dniP ?: $celularPadre ?: uniqid()) . '@padre.temp';
                            $padre->password_hash = Hash::make($dniP ?: $celularPadre ?: '12345678');
                            $padre->nombre = $nombrePadre;
                            $padre->apellido_paterno = ''; 
                            $padre->apellido_materno = '';
                            $padre->telefono = $celularPadre;
                            $padre->estado = true;
                            $padre->save();
                            
                            $rol = Role::whereIn('nombre', ['Padre', 'padre'])->first();
                            if ($rol) $padre->assignRole($rol->nombre);
                        }
                        
                        Parentesco::updateOrCreate(
                            ['estudiante_id' => $usuario->id, 'padre_id' => $padre->id],
                            ['tipo_parentesco' => 'Padre', 'acceso_portal' => true, 'estado' => true]
                        );
                    }

                    // MADRES - Crear si hay nombre o teléfono
                    if (!empty($row['nombre_madre_completo']) || !empty($row['celular_madre'])) {
                        $dniM = !empty($row['dni_madre']) ? trim($row['dni_madre']) : null;
                        $nombreMadre = strtoupper(trim($row['nombre_madre_completo'] ?? 'MADRE'));
                        $celularMadre = trim($row['celular_madre'] ?? '');
                        
                        // Buscar por DNI, username o teléfono
                        if ($dniM) {
                            $madre = User::firstOrNew(['numero_documento' => $dniM]);
                        } elseif ($celularMadre) {
                            // Buscar por username (teléfono) o por campo teléfono
                            $madre = User::where('username', $celularMadre)
                                ->orWhere(function($q) use ($celularMadre) {
                                    $q->where('telefono', $celularMadre)
                                      ->whereHas('roles', function($r) {
                                          $r->whereIn('nombre', ['Madre', 'madre']);
                                      });
                                })
                                ->first();
                            if (!$madre) {
                                $madre = new User();
                            }
                        } else {
                            $madre = new User();
                        }
                        
                        if (!$madre->exists) {
                            // Generar username único
                            if ($dniM) {
                                $madre->username = $dniM;
                                $madre->numero_documento = $dniM;
                                $madre->tipo_documento = 'DNI';
                            } else {
                                // Usar teléfono o generar ID temporal
                                $madre->username = $celularMadre ?: 'madre_' . uniqid();
                                $madre->numero_documento = $celularMadre ?: null;
                                $madre->tipo_documento = $celularMadre ? 'OTRO' : null;
                            }
                            
                            $madre->email = ($dniM ?: $celularMadre ?: uniqid()) . '@madre.temp';
                            $madre->password_hash = Hash::make($dniM ?: $celularMadre ?: '12345678');
                            $madre->nombre = $nombreMadre;
                            $madre->apellido_paterno = '';
                            $madre->apellido_materno = '';
                            $madre->telefono = $celularMadre;
                            $madre->estado = true;
                            $madre->save();

                            $rol = Role::whereIn('nombre', ['Madre', 'madre'])->first();
                            if ($rol) $madre->assignRole($rol->nombre);
                        }
                        
                        Parentesco::updateOrCreate(
                            ['estudiante_id' => $usuario->id, 'padre_id' => $madre->id],
                            ['tipo_parentesco' => 'Madre', 'acceso_portal' => true, 'estado' => true]
                        );
                    }

                    $this->resultados['procesados']++;
                    if ($esNuevoUsuario || $esNuevaPostulacion) {
                        $this->resultados['creados']++;
                    }

                } catch (\Throwable $e) {
                    $this->resultados['errores'][] = "Fila $fila: Error int - " . $e->getMessage();
                }
            } 

            if ($this->simulacro) {
                DB::rollBack();
            } else {
                DB::commit();
            }

        } catch (\Throwable $e) {
            DB::rollBack();
            $this->resultados['errores'][] = "Error Global Import: " . $e->getMessage();
        }
    }
}
