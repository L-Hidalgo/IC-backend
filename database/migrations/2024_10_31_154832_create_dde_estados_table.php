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
        Schema::create('dde_estados', function (Blueprint $table) {
            $table->integer('id_estado')->unsigned()->autoIncrement();
            $table->string('nombre_estado', 50);
            $table->timestamps();
            $table->timestamp('fecha_inicio')->nullable()->default(null);
            $table->timestamp('fecha_fin')->nullable()->default(null);
        });
        
        // Insertar los estados "Acefalo" y "Ocupado"
        DB::table('dde_estados')->insert([
            ['id_estado' => 1, 'nombre_estado' => 'Acéfalo', 'created_at' => now(), 'updated_at' => now()],
            ['id_estado' => 2, 'nombre_estado' => 'Ocupado', 'created_at' => now(), 'updated_at' => now()]
        ]);
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dde_estados');
    }
};
