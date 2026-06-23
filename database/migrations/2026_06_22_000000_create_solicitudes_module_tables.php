<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Módulo de Solicitudes (FUT digital) + catálogo TUSNE.
 * Crea las tablas base: catálogo de precios, tipos de trámite, solicitudes,
 * bitácora de estados, adjuntos/evidencias e inasistencias a justificar.
 */
return new class extends Migration
{
    public function up(): void
    {
        // 1. Catálogo de precios (TUSNE) — administrado por finanzas/contadora
        Schema::create('tusne_conceptos', function (Blueprint $table) {
            $table->id();
            $table->string('codigo')->unique();              // p.ej. 372, 371, 586, 582, 583
            $table->string('nombre');
            $table->text('descripcion')->nullable();
            $table->decimal('costo', 8, 2)->default(0);
            $table->string('categoria')->default('tramite'); // matricula | tramite | constancia | justificacion
            $table->boolean('requiere_pago')->default(true);
            $table->string('anio')->nullable();              // vigencia (ej. 2026)
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        // 2. Tipos de trámite solicitables (qué conceptos del TUSNE se pueden pedir y su formulario)
        Schema::create('solicitud_tipos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tusne_concepto_id')->nullable()->constrained('tusne_conceptos')->nullOnDelete();
            $table->string('codigo')->unique();              // slug: duplicado-carnet, cambio-carrera, ...
            $table->string('nombre');
            $table->text('descripcion')->nullable();
            $table->boolean('requiere_pago')->default(true);
            $table->boolean('permite_adjuntos')->default(true);
            $table->boolean('requiere_adjunto')->default(false);
            $table->string('genera_documento')->nullable();  // carnet | constancia | justificacion | null
            $table->json('campos')->nullable();              // definición de campos del formulario dinámico
            $table->boolean('requiere_vb_director')->default(true); // todos pasan por V°B° del Director
            $table->unsignedBigInteger('rol_responsable_id')->nullable()->index(); // rol que atiende por defecto
            $table->boolean('activo')->default(true);
            $table->unsignedInteger('orden')->default(0);
            $table->timestamps();
        });

        // 3. Solicitudes (el trámite del estudiante)
        Schema::create('solicitudes', function (Blueprint $table) {
            $table->id();
            $table->string('codigo')->unique();              // SOL-2026-000001
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('numero_documento')->index();
            $table->foreignId('solicitud_tipo_id')->constrained('solicitud_tipos');
            $table->unsignedBigInteger('ciclo_id')->nullable()->index();
            $table->string('term_name')->nullable();         // periodo de la API de pagos (ej. 2026-1)
            $table->string('estado')->default('enviada')->index(); // enviada|en_revision|observada|aprobada|atendida|rechazada|pendiente_pago
            $table->json('datos')->nullable();               // campos específicos del trámite
            $table->string('serial_voucher')->nullable()->unique(); // pago ligado (antifraude: único)
            $table->boolean('pago_validado')->default(false);
            $table->decimal('monto', 8, 2)->nullable();
            $table->dateTime('fecha_pago')->nullable();
            $table->text('observacion')->nullable();         // último comentario del admin
            $table->unsignedBigInteger('atendido_por')->nullable();
            $table->dateTime('fecha_atencion')->nullable();
            $table->string('documento_path')->nullable();    // PDF generado (constancia/carnet)
            $table->string('canal')->default('web');         // web | app
            // Visto Bueno del Director y ubicación actual del expediente (persona y/o rol que lo atiende)
            $table->unsignedBigInteger('user_actual_id')->nullable()->index(); // persona responsable actual
            $table->unsignedBigInteger('rol_actual_id')->nullable()->index();  // o rol/área responsable
            $table->unsignedBigInteger('vb_director_por')->nullable();
            $table->dateTime('vb_director_at')->nullable();
            $table->timestamps();
        });

        // 4. Bitácora de cambios de estado (trazabilidad total)
        Schema::create('solicitud_historial', function (Blueprint $table) {
            $table->id();
            $table->foreignId('solicitud_id')->constrained('solicitudes')->cascadeOnDelete();
            $table->string('estado_anterior')->nullable();
            $table->string('estado_nuevo');
            $table->text('comentario')->nullable();
            $table->unsignedBigInteger('user_id')->nullable(); // quién hizo el cambio (admin o sistema)
            $table->timestamps();
        });

        // 5. Adjuntos / evidencias
        Schema::create('solicitud_adjuntos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('solicitud_id')->constrained('solicitudes')->cascadeOnDelete();
            $table->string('tipo')->default('evidencia');    // evidencia | voucher | otro
            $table->string('nombre_original')->nullable();
            $table->string('path');
            $table->string('mime')->nullable();
            $table->unsignedBigInteger('size')->nullable();
            $table->timestamps();
        });

        // 6. Inasistencias a justificar (para regularización automática al aprobar)
        Schema::create('solicitud_inasistencias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('solicitud_id')->constrained('solicitudes')->cascadeOnDelete();
            $table->string('numero_documento')->index();
            $table->date('fecha');
            $table->unsignedBigInteger('ciclo_id')->nullable()->index();
            $table->boolean('justificada')->default(false)->index(); // true cuando la solicitud se aprueba
            $table->timestamps();

            $table->unique(['numero_documento', 'fecha', 'solicitud_id'], 'sol_inasist_unica');
        });

        // 7. Derivaciones (Hoja de Trámite digital): cada "pase" a un rol/usuario
        Schema::create('solicitud_derivaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('solicitud_id')->constrained('solicitudes')->cascadeOnDelete();
            $table->unsignedBigInteger('de_user_id')->nullable();      // quién deriva (ej. Director)
            $table->unsignedBigInteger('rol_destino_id')->nullable()->index(); // rol al que se deriva
            $table->unsignedBigInteger('user_destino_id')->nullable(); // opcional: persona específica
            $table->string('accion')->nullable();                      // "Atención según lo solicitado", etc.
            $table->text('observacion')->nullable();
            $table->boolean('atendida')->default(false);
            $table->timestamps();
        });

        // Firma escaneada (para el V°B° digital en el PDF) — usada por el Director
        if (!Schema::hasColumn('users', 'firma_path')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('firma_path')->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'firma_path')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('firma_path');
            });
        }

        Schema::dropIfExists('solicitud_derivaciones');
        Schema::dropIfExists('solicitud_inasistencias');
        Schema::dropIfExists('solicitud_adjuntos');
        Schema::dropIfExists('solicitud_historial');
        Schema::dropIfExists('solicitudes');
        Schema::dropIfExists('solicitud_tipos');
        Schema::dropIfExists('tusne_conceptos');
    }
};
