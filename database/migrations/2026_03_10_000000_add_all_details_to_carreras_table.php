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
        Schema::table('carreras', function (Blueprint $table) {
            // Check if columns don't exist before adding to avoid issues if partially run
            if (!Schema::hasColumn('carreras', 'slug')) {
                $table->string('slug')->nullable()->unique()->after('nombre');
            }
            if (!Schema::hasColumn('carreras', 'imagen_url')) {
                $table->string('imagen_url')->nullable()->after('descripcion');
            }
            if (!Schema::hasColumn('carreras', 'campo_laboral')) {
                $table->json('campo_laboral')->nullable()->after('imagen_url');
            }
            if (!Schema::hasColumn('carreras', 'grado')) {
                $table->string('grado')->nullable();
            }
            if (!Schema::hasColumn('carreras', 'titulo')) {
                $table->string('titulo')->nullable();
            }
            if (!Schema::hasColumn('carreras', 'duracion')) {
                $table->string('duracion')->nullable();
            }
            if (!Schema::hasColumn('carreras', 'perfil')) {
                $table->text('perfil')->nullable();
            }
            if (!Schema::hasColumn('carreras', 'mision')) {
                $table->text('mision')->nullable();
            }
            if (!Schema::hasColumn('carreras', 'vision')) {
                $table->text('vision')->nullable();
            }
            if (!Schema::hasColumn('carreras', 'objetivos')) {
                $table->text('objetivos')->nullable();
            }
            if (!Schema::hasColumn('carreras', 'resena')) {
                $table->text('resena')->nullable();
            }
            if (!Schema::hasColumn('carreras', 'malla_url')) {
                $table->string('malla_url')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('carreras', function (Blueprint $table) {
            $table->dropColumn([
                'slug', 'imagen_url', 'campo_laboral', 'grado', 'titulo', 
                'duracion', 'perfil', 'mision', 'vision', 'objetivos', 
                'resena', 'malla_url'
            ]);
        });
    }
};
