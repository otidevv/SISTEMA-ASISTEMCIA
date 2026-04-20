<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('anuncios', function (Blueprint $table) {
            $table->id();
            $table->string('titulo', 255);
            $table->text('contenido');
            $table->text('descripcion')->nullable();
            $table->boolean('es_activo')->default(true);
            $table->datetime('fecha_inicio')->nullable();
            $table->datetime('fecha_fin')->nullable();
            
            // También agregamos las columnas que está buscando la consulta actual
            $table->datetime('fecha_publicacion')->nullable();
            $table->datetime('fecha_expiracion')->nullable();
            
            $table->integer('prioridad')->default(1)->comment('1=Baja, 2=Media, 3=Alta, 4=Crítica');
            $table->enum('tipo', ['informativo', 'importante', 'urgente', 'mantenimiento', 'evento'])->default('informativo');
            $table->unsignedBigInteger('creado_por')->nullable();
            
            // Multimedia y Notificaciones (Campos Unificados)
            $table->string('imagen')->nullable();
            $table->string('archivo_adjunto')->nullable();
            $table->string('tipo_archivo', 50)->nullable();
            $table->boolean('enviar_push')->default(false);
            $table->string('firebase_message_id')->nullable();
            
            $table->timestamps();

            // Índices para mejor rendimiento
            $table->index(['es_activo', 'fecha_inicio', 'fecha_fin']);
            $table->index(['es_activo', 'fecha_publicacion', 'fecha_expiracion']);
            $table->index(['prioridad', 'created_at']);

            // Clave foránea si existe la tabla users
            $table->foreign('creado_por')->references('id')->on('users')->onDelete('set null');
        });

        // Tabla Pivote para Roles Dinámicos (Integrada)
        Schema::create('anuncio_roles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('anuncio_id')->constrained('anuncios')->onDelete('cascade');
            $table->foreignId('role_id')->constrained('roles')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('anuncio_roles');
        Schema::dropIfExists('anuncios');
    }
};