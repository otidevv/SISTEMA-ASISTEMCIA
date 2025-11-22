<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('examen_distribucion', function (Blueprint $table) {
            $table->string('docente_invitado')->nullable()->after('docente_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('examen_distribucion', function (Blueprint $table) {
            $table->dropColumn('docente_invitado');
        });
    }
};
