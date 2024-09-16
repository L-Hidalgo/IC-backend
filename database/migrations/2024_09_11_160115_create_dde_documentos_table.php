<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dde_documentos', function (Blueprint $table) {
            $table->integer('id_documento')->unsigned()->autoIncrement();
            $table->string('nombre_documento');
            $table->string('ruta_archivo_documento');
            $table->integer('tipo_documento');
            $table->integer('estado_documento'); //1 Activo, 2 Inactivo
            $table->integer('persona_id')->unsigned()->nullable();  
            $table->unsignedBigInteger('created_by_documento')->nullable();
            $table->unsignedBigInteger('modified_by_documento')->nullable();
            $table->timestamps();

            $table->foreign('persona_id')->references('id_persona')->on('dde_personas');
            $table->foreign('created_by_documento')->references('id')->on('users');
            $table->foreign('modified_by_documento')->references('id')->on('users');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dde_documentos');
    }
};
