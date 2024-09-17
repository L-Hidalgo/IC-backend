<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dde_carpetas', function (Blueprint $table) {
            $table->integer('id_carpeta')->unsigned()->autoIncrement();
            $table->string('nombre_carpeta');
            $table->string('ruta_carpeta');
            $table->integer('tipo_carpeta'); // 1 File y 2 Memoradum-RAP
            $table->integer('estado_carpeta'); //1 Activo, 2 Inactivo
            $table->foreignId('padre_id_carpeta')->nullable()->constrained('dde_carpetas')->onDelete('cascade');
            $table->unsignedBigInteger('created_by_carpeta')->nullable();
            $table->unsignedBigInteger('modified_by_carpeta')->nullable();
            $table->timestamps();

            $table->foreign('created_by_documento')->references('id')->on('users');
            $table->foreign('modified_by_documento')->references('id')->on('users');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dde_carpetas');
    }
};
