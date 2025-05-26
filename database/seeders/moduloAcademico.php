<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class moduloAcademico extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Módulo de Ciclos Académicos
        DB::table('permissions')->insert([
            [
                'nombre' => 'Ver Ciclos',
                'codigo' => 'ciclos.view',
                'descripcion' => 'Permite ver la lista de ciclos académicos',
                'modulo' => 'ciclos'
            ],
            [
                'nombre' => 'Crear Ciclo',
                'codigo' => 'ciclos.create',
                'descripcion' => 'Permite crear nuevos ciclos académicos',
                'modulo' => 'ciclos'
            ],
            [
                'nombre' => 'Editar Ciclo',
                'codigo' => 'ciclos.edit',
                'descripcion' => 'Permite editar ciclos académicos',
                'modulo' => 'ciclos'
            ],
            [
                'nombre' => 'Eliminar Ciclo',
                'codigo' => 'ciclos.delete',
                'descripcion' => 'Permite eliminar ciclos académicos',
                'modulo' => 'ciclos'
            ],
            [
                'nombre' => 'Activar Ciclo',
                'codigo' => 'ciclos.activate',
                'descripcion' => 'Permite activar/desactivar ciclos académicos',
                'modulo' => 'ciclos'
            ]
        ]);

        // Módulo de Carreras
        DB::table('permissions')->insert([
            [
                'nombre' => 'Ver Carreras',
                'codigo' => 'carreras.view',
                'descripcion' => 'Permite ver la lista de carreras',
                'modulo' => 'carreras'
            ],
            [
                'nombre' => 'Crear Carrera',
                'codigo' => 'carreras.create',
                'descripcion' => 'Permite crear nuevas carreras',
                'modulo' => 'carreras'
            ],
            [
                'nombre' => 'Editar Carrera',
                'codigo' => 'carreras.edit',
                'descripcion' => 'Permite editar carreras',
                'modulo' => 'carreras'
            ],
            [
                'nombre' => 'Eliminar Carrera',
                'codigo' => 'carreras.delete',
                'descripcion' => 'Permite eliminar carreras',
                'modulo' => 'carreras'
            ],
            [
                'nombre' => 'Cambiar Estado Carrera',
                'codigo' => 'carreras.change_status',
                'descripcion' => 'Permite activar/desactivar carreras',
                'modulo' => 'carreras'
            ]
        ]);

        // Módulo de Turnos
        DB::table('permissions')->insert([
            [
                'nombre' => 'Ver Turnos',
                'codigo' => 'turnos.view',
                'descripcion' => 'Permite ver la lista de turnos',
                'modulo' => 'turnos'
            ],
            [
                'nombre' => 'Crear Turno',
                'codigo' => 'turnos.create',
                'descripcion' => 'Permite crear nuevos turnos',
                'modulo' => 'turnos'
            ],
            [
                'nombre' => 'Editar Turno',
                'codigo' => 'turnos.edit',
                'descripcion' => 'Permite editar turnos',
                'modulo' => 'turnos'
            ],
            [
                'nombre' => 'Eliminar Turno',
                'codigo' => 'turnos.delete',
                'descripcion' => 'Permite eliminar turnos',
                'modulo' => 'turnos'
            ],
            [
                'nombre' => 'Cambiar Estado Turno',
                'codigo' => 'turnos.change_status',
                'descripcion' => 'Permite activar/desactivar turnos',
                'modulo' => 'turnos'
            ]
        ]);

        // Módulo de Aulas
        DB::table('permissions')->insert([
            [
                'nombre' => 'Ver Aulas',
                'codigo' => 'aulas.view',
                'descripcion' => 'Permite ver la lista de aulas',
                'modulo' => 'aulas'
            ],
            [
                'nombre' => 'Crear Aula',
                'codigo' => 'aulas.create',
                'descripcion' => 'Permite crear nuevas aulas',
                'modulo' => 'aulas'
            ],
            [
                'nombre' => 'Editar Aula',
                'codigo' => 'aulas.edit',
                'descripcion' => 'Permite editar aulas',
                'modulo' => 'aulas'
            ],
            [
                'nombre' => 'Eliminar Aula',
                'codigo' => 'aulas.delete',
                'descripcion' => 'Permite eliminar aulas',
                'modulo' => 'aulas'
            ],
            [
                'nombre' => 'Cambiar Estado Aula',
                'codigo' => 'aulas.change_status',
                'descripcion' => 'Permite activar/desactivar aulas',
                'modulo' => 'aulas'
            ],
            [
                'nombre' => 'Ver Disponibilidad Aulas',
                'codigo' => 'aulas.availability',
                'descripcion' => 'Permite ver la disponibilidad de aulas',
                'modulo' => 'aulas'
            ]
        ]);

        // Módulo de Inscripciones
        DB::table('permissions')->insert([
            [
                'nombre' => 'Ver Inscripciones',
                'codigo' => 'inscripciones.view',
                'descripcion' => 'Permite ver la lista de inscripciones',
                'modulo' => 'inscripciones'
            ],
            [
                'nombre' => 'Crear Inscripción',
                'codigo' => 'inscripciones.create',
                'descripcion' => 'Permite crear nuevas inscripciones',
                'modulo' => 'inscripciones'
            ],
            [
                'nombre' => 'Editar Inscripción',
                'codigo' => 'inscripciones.edit',
                'descripcion' => 'Permite editar inscripciones',
                'modulo' => 'inscripciones'
            ],
            [
                'nombre' => 'Eliminar Inscripción',
                'codigo' => 'inscripciones.delete',
                'descripcion' => 'Permite eliminar inscripciones',
                'modulo' => 'inscripciones'
            ],
            [
                'nombre' => 'Cambiar Estado Inscripción',
                'codigo' => 'inscripciones.change_status',
                'descripcion' => 'Permite cambiar el estado de las inscripciones',
                'modulo' => 'inscripciones'
            ],
            [
                'nombre' => 'Ver Reportes de Inscripciones',
                'codigo' => 'inscripciones.reports',
                'descripcion' => 'Permite ver reportes de inscripciones',
                'modulo' => 'inscripciones'
            ],
            [
                'nombre' => 'Exportar Inscripciones',
                'codigo' => 'inscripciones.export',
                'descripcion' => 'Permite exportar datos de inscripciones',
                'modulo' => 'inscripciones'
            ]
        ]);
    }
}
