<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('dde_departamentos', function (Blueprint $table) {
            $table->integer('id_departamento')->unsigned()->autoIncrement();
            $table->string('nombre_departamento', 255)->nullable();
            $table->unsignedInteger('gerencia_id');
            $table->timestamps();
            $table->timestamp('fecha_inicio')->nullable()->default(null);
            $table->timestamp('fecha_fin')->nullable()->default(null);

            $table->foreign('gerencia_id')->references('id_gerencia')->on('dde_gerencias');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dde_departamentos');
    }
}
;
