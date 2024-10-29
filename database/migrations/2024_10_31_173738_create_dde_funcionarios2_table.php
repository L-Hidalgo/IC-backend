<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('dde_funcionarios2', function (Blueprint $table) {
            $table->integer('id_funcionario')->unsigned()->autoIncrement();
            $table->unsignedBigInteger('persona_id');
            $table->unsignedBigInteger('puesto_id');
            $table->string('codigo_file_funcionario', 50)->nullable();
            $table->date('fch_inicio_sin_funcionario')->nullable();
            $table->date('fch_fin_sin_funcionario')->nullable();
            $table->date('fch_inicio_puesto_funcionario')->nullable();
            $table->date('fch_fin_puesto_funcionario')->nullable();
            $table->timestamps();
            $table->foreign('persona_id')->references('id_persona')->on('dde_personas')->onDelete('cascade');
            $table->foreign('puesto_id')->references('id_puesto')->on('dde_puestos')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dde_funcionarios2');
    }
};
