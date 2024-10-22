<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('dde_plantillas', function (Blueprint $table) {
            $table->integer('id_plantilla')->unsigned()->autoIncrement();
            $table->string('nombre_plantilla');
            $table->string('version_plantilla')->nullable();
            $table->integer('tipo_plantilla'); //1: incorporacion, 2: interinato
            $table->string('ruta_plantilla');
            $table->unsignedBigInteger('created_plantilla')->nullable();
            $table->unsignedBigInteger('modified_plantilla')->nullable();
            $table->timestamps();

            $table->foreign('created_plantilla')->references('id')->on('users');
            $table->foreign('modified_plantilla')->references('id')->on('users');
        });
    }

    public function down()
    {
        Schema::dropIfExists('dde_plantillas');
    }
};
