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
        if (!Schema::hasTable('dde_interinatos')) {
            Schema::create('dde_interinatos', function (Blueprint $table) {
                $table->integer('id_interinato')->unsigned()->autoIncrement();
                $table->integer('puesto_nuevo_id')->nullable()->unsigned();
                $table->integer('titular_puesto_nuevo_id')->nullable()->unsigned();
                $table->integer('puesto_actual_id')->nullable()->unsigned();
                $table->integer('titular_puesto_actual_id')->nullable()->unsigned();
                //Desingacion
                $table->string('proveido_tramite_interinato', 50)->nullable();
                $table->string('cite_nota_informe_minuta_interinato',)->nullable();
                $table->date('fch_cite_nota_inf_minuta_interinato')->nullable();
                $table->string('cite_informe_interinato', 3)->nullable();
                $table->string('fojas_informe_interinato', 20)->nullable();
                $table->string('cite_memorandum_interinato', 4)->nullable();
                $table->string('codigo_memorandum_interinato', 13)->nullable();
                $table->string('cite_rap_interinato', 3)->nullable();
                $table->string('codigo_rap_interinato', 12)->nullable();
                $table->date('fch_memorandum_rap_interinato')->nullable();
                $table->date('fch_inicio_interinato')->nullable();
                $table->date('fch_fin_interinato')->nullable();
                $table->integer('total_dias_interinato')->nullable();
                $table->string('periodo_interinato')->nullable();
                $table->unsignedBigInteger('created_by_interinato')->nullable();
                $table->unsignedBigInteger('modified_by_interinato')->nullable();
                $table->string('tipo_nota_informe_minuta_interinato', 10)->nullable();
                $table->string('observaciones_interinato')->nullable();
                $table->integer('estado_designacion_interinato')->default(0);
                //Suspencion
                $table->string('proveido_tramite_interinato_suspencion', 50)->nullable();
                $table->date('fch_proveido_tramite_interinato_suspencion')->nullable();
                $table->string('cite_memorandum_interinato_suspencion', 4)->nullable();
                $table->string('codigo_memorandum_interinato_suspencion', 13)->nullable();
                $table->date('fch_memorandum_interinato_suspencion')->nullable();
                $table->date('fch_suspencion')->nullable();
                $table->date('codigo_suspencion')->nullable();
                $table->date('fch_designacion_suspencion')->nullable();
                $table->date('motivo_suspencion')->nullable();

                $table->string('sayri_interinato', 20)->nullable();
                $table->foreign('created_by_interinato')->references('id')->on('users');
                $table->foreign('modified_by_interinato')->references('id')->on('users');

                $table->foreign('puesto_nuevo_id')->references('id_puesto')->on('dde_puestos');
                $table->foreign('titular_puesto_nuevo_id')->references('id_persona')->on('dde_personas');
                $table->foreign('puesto_actual_id')->references('id_puesto')->on('dde_puestos');
                $table->foreign('titular_puesto_actual_id')->references('id_persona')->on('dde_personas');
                $table->timestamps();
                $table->timestamp('fecha_inicio')->nullable()->default(null);
                $table->timestamp('fecha_fin')->nullable()->default(null);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dde_interinatos');
    }
};
