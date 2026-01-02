<?php

namespace App\Imports;

use App\Models\User;
use App\Models\Postulacion;
use App\Models\Carrera;
use App\Models\Turno;
use App\Models\Ciclo;
use App\Models\Parentesco;
use App\Models\CentroEducativo;
use Spatie\Permission\Models\Role;
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
                    
                    // USUARIO
                    $usuario = User::firstOrNew(['numero_documento' => $dni]);
                    if (!$usuario->exists) {
                        $usuario->username = $dni; 
                        $usuario->password_hash = Hash::make($dni);
                        $usuario->nombre = strtoupper(trim($row['nombres']));
                        $usuario->apellido_paterno = strtoupper(trim($row['apellido_paterno'] ?? ''));
                        $usuario->apellido_materno = strtoupper(trim($row['apellido_materno'] ?? ''));
                        
                        $emailInput = trim($row['email'] ?? '');
                        if (!empty($emailInput) && User::where('email', $emailInput)->exists()) {
                            $emailInput = null;
                        }
                        $usuario->email = !empty($emailInput) ? $emailInput : ($dni . '@sistema.local');

                        $usuario->telefono = trim($row['telefono'] ?? null);
                        $usuario->direccion = $row['direccion'] ?? null;
                        
                        $fechaNac = '2000-01-01';
                        if (!empty($row['fecha_nacimiento'])) {
                            $val = $row['fecha_nacimiento'];
                            try {
                                if (is_numeric($val)) {
                                    $fechaNac = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($val);
                                } else {
                                    $fechaNac = Carbon::parse($val);
                                }
                            } catch (\Throwable $e) { }
                        }
                        $usuario->fecha_nacimiento = $fechaNac;

                        $usuario->genero = strtoupper($row['genero'] ?? 'M');
                        $usuario->tipo_documento = 'DNI';
                        $usuario->estado = true;
                        $usuario->save();

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

                    // DUPLICADO
                    $existePostulacion = Postulacion::where('estudiante_id', $usuario->id)
                        ->where('ciclo_id', $this->cicloActivo->id)
                        ->exists();

                    if ($existePostulacion) {
                        $this->resultados['errores'][] = "Fila $fila: El usuario $dni ya tiene postulación.";
                        continue;
                    }

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

                    // POSTULACIÓN
                    $postulacion = new Postulacion();
                    $postulacion->estudiante_id = $usuario->id;
                    $postulacion->ciclo_id = $this->cicloActivo->id;
                    $postulacion->carrera_id = $carreraId;
                    $postulacion->turno_id = $turnoId;
                    $postulacion->modalidad = strtoupper($row['modalidad'] ?? 'POSTULANTE');
                    $postulacion->codigo_postulante = $nuevoCodigo;
                    $postulacion->fecha_postulacion = now();
                    $postulacion->estado = 'aprobado';
                    $postulacion->tipo_inscripcion = 'postulante';
                    $postulacion->centro_educativo_id = $colegioId;
                    $postulacion->anio_egreso = $row['anio_egreso'] ?? date('Y');
                    
                    if (!$usuario->hasRole('Postulante')) { 
                         $usuario->assignRole('Postulante'); 
                    }

                    $postulacion->save();

                    // PADRES
                    if (!empty($row['dni_padre'])) {
                        $dniP = trim($row['dni_padre']);
                        $padre = User::firstOrNew(['numero_documento' => $dniP]);
                        if (!$padre->exists) {
                            $padre->username = $dniP;
                            $padre->email = 'padre_' . $dniP . '@temp.com';
                            $padre->password_hash = Hash::make($dniP);
                            $padre->nombre = strtoupper(trim($row['nombre_padre_completo'] ?? 'PADRE'));
                            $padre->apellido_paterno = ''; 
                            $padre->apellido_materno = '';
                            $padre->telefono = trim($row['celular_padre'] ?? '');
                            $padre->tipo_documento = 'DNI';
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

                    if (!empty($row['dni_madre'])) {
                        $dniM = trim($row['dni_madre']);
                        $madre = User::firstOrNew(['numero_documento' => $dniM]);
                        if (!$madre->exists) {
                            $madre->username = $dniM;
                            $madre->email = 'madre_' . $dniM . '@temp.com';
                            $madre->password_hash = Hash::make($dniM);
                            $madre->nombre = strtoupper(trim($row['nombre_madre_completo'] ?? 'MADRE'));
                            $madre->apellido_paterno = '';
                            $madre->apellido_materno = '';
                            $madre->telefono = trim($row['celular_madre'] ?? '');
                            $madre->tipo_documento = 'DNI';
                            $madre->estado = true;
                            $madre->save();

                            $rol = Role::whereIn('nombre', ['Madre', 'madre'])->first();
                            if ($rol) $madre->assignRole($rol->nombre);
                        }
                        Parentesco::updateOrCreate(
                            ['estudiante_id' => $usuario->id, 'madre_id' => $madre->id],
                            ['tipo_parentesco' => 'Madre', 'acceso_portal' => true, 'estado' => true]
                        );
                    }

                    $this->resultados['procesados']++;
                    $this->resultados['creados']++;

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
