<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AttendanceViewPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Insertar el nuevo permiso para ver asistencia en tiempo real
        DB::table('permissions')->insert([
            'nombre' => 'Ver Asistencia en Tiempo Real',
            'codigo' => 'attendance.realtime',
            'descripcion' => 'Permite ver actualizaciones de asistencia en tiempo real',
            'modulo' => 'asistencia'
        ]);
    }
}