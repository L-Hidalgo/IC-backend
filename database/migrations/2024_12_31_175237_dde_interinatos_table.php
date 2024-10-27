<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('dde_interinatos', function (Blueprint $table) {
            $table->id('id_interinato');
            $table->unsignedBigInteger('puesto_nuevo_id')->nullable();
            $table->unsignedBigInteger('puesto_actual_id')->nullable();
            $table->unsignedBigInteger('persona_id')->nullable();
            $table->date('fch_inicio_interinato')->nullable();
            $table->date('fch_fin_interinato')->nullable();
            $table->tinyInteger('estado_interinato'); // 1: Nuevo, 2: finalizado, 3: finalizado

            $table->unsignedBigInteger('titular_puesto_nuevo_id')->nullable();
            $table->unsignedBigInteger('titular_puesto_actual_id')->nullable();

            $table->string('cite_informe_instruccion_interinato')->nullable();
            $table->date('fch_informe_instruccion_interinato')->nullable();
            $table->string('proveido_interinato')->nullable();
            $table->string('num_tramite_hp_interinato')->nullable();
            $table->string('cite_informe_interinato')->nullable();
            $table->date('fch_cite_informe_interinato')->nullable();
            $table->integer('num_fojas_informe_interinato')->nullable();

            $table->string('cite_rap_interinato')->nullable();
            $table->string('codigo_rap_interinato')->nullable();
            $table->integer('num_fojas_rap_interinato')->nullable();

            $table->string('cite_mem_interinato')->nullable();
            $table->string('codigo_mem_interinato')->nullable();
            $table->string('codigo_file_interinato')->nullable();
            $table->date('fch_memorandum_rap_interinato')->nullable();

            $table->string('cite_suspencion_interinato')->nullable();
            $table->string('codigo_suspencion_interinato')->nullable();
            $table->string('fch_suspencion_interinato')->nullable();
            $table->string('codigo_file_suspencion_interinato')->nullable();

            $table->unsignedBigInteger('created_interinato')->nullable();
            $table->unsignedBigInteger('modified_interinato')->nullable();

            $table->foreign('persona_id')->references('id_persona')->on('dde_personas');
            $table->foreign('puesto_nuevo_id')->references('id_puesto')->on('dde_puestos');
            $table->foreign('puesto_actual_id')->references('id_puesto')->on('dde_puestos');
            $table->foreign('created_interinato')->references('id')->on('users');
            $table->foreign('modified_interinato')->references('id')->on('users');

            $table->foreign('titular_puesto_nuevo_id')->references('id_persona')->on('dde_personas'); //borrar 
            $table->foreign('titular_puesto_actual_id')->references('id_persona')->on('dde_personas'); //borrar
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('dde_interinatos');
    }
};
