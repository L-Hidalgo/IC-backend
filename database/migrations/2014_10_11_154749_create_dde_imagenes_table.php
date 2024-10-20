<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dde_imagenes', function (Blueprint $table) {
            $table->integer('id_imagen')->unsigned()->autoIncrement();
            $table->longText('base64_imagen')->nullable();
            $table->string('tipo_mime_imagen'); 
            $table->integer('persona_id')->unsigned();
            $table->timestamps();
            $table->timestamp('fecha_inicio')->nullable()->default(null);
            $table->timestamp('fecha_fin')->nullable()->default(null);

            $table->foreign('persona_id')->references('id_persona')->on('dde_personas')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dde_imagenes');
    }
};