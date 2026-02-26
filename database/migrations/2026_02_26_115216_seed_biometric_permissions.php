<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        \DB::table('permissions')->insert([
            [
                'nombre' => 'Ver Módulo Biométrico',
                'codigo' => 'biometria.view',
                'descripcion' => 'Permite acceder al panel de gestión de dispositivos y lista de enrolamiento.',
                'modulo' => 'Biometría'
            ],
            [
                'nombre' => 'Enrolar Usuarios',
                'codigo' => 'biometria.enroll',
                'descripcion' => 'Permite enviar comandos de registro de huella o rostro a los equipos.',
                'modulo' => 'Biometría'
            ]
        ]);
    }

    public function down(): void
    {
        \DB::table('permissions')->whereIn('codigo', ['biometria.view', 'biometria.enroll'])->delete();
    }
};
