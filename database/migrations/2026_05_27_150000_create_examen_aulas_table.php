<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('examen_aulas', function (Blueprint $table) {
            $table->id();
            $table->string('codigo')->unique();
            $table->string('nombre');
            $table->integer('capacidad');
            $table->integer('piso');
            $table->boolean('estado')->default(true);
            $table->timestamps();
        });

        // Copiar aulas activas existentes para que el usuario no empiece de cero
        try {
            $aulas = DB::table('aulas')->get();
            foreach ($aulas as $aula) {
                // Asegurarse de que el código sea único
                $codigo = $aula->codigo ?: ('A-' . $aula->nombre);
                
                // Si ya existe en examen_aulas, omitir
                $exists = DB::table('examen_aulas')->where('codigo', $codigo)->exists();
                if ($exists) {
                    continue;
                }

                DB::table('examen_aulas')->insert([
                    'codigo' => $codigo,
                    'nombre' => $aula->nombre,
                    'capacidad' => $aula->capacidad,
                    'piso' => $aula->piso ?? 1,
                    'estado' => $aula->estado,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        } catch (\Exception $e) {
            // Silenciar si hay algún inconveniente durante el seeding
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('examen_aulas');
    }
};
