<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dde_files', function (Blueprint $table) {
            $table->integer('id_file')->unsigned()->autoIncrement();
            $table->string('nombre_file');
            $table->string('ruta_file')->nullable(); 
            $table->integer('tipo_documento_file');  // '1:file' o '2:memo-rap'
            $table->integer('tipo_file'); // '1:carpeta' o '2:documento'
            $table->integer('persona_id')->nullable()->unsigned();
            $table->unsignedBigInteger('parent_id')->nullable(); // ID de la carpeta padre
            $table->integer('estado_file');
            $table->unsignedBigInteger('created_by_file')->nullable();
            $table->unsignedBigInteger('modified_by_file')->nullable();
            $table->timestamps();
            $table->foreign('persona_id')->references('id_persona')->on('dde_personas');
            $table->foreign('created_by_file')->references('id')->on('users');
            $table->foreign('modified_by_file')->references('id')->on('users');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dde_files');
    }
};
