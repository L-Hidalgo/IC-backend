<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::dropIfExists('dde_instituciones'); 
        
        Schema::create('dde_instituciones', function (Blueprint $table) {
            $table->integer('id_institucion')->unsigned()->autoIncrement();
            $table->string('nombre_institucion');
            $table->timestamps();
            $table->timestamp('fecha_inicio')->nullable()->default(null);
            $table->timestamp('fecha_fin')->nullable()->default(null);
        });

        DB::table('dde_instituciones')->insert([
            //['nombre' => 'Instituto Técnico Boliviano Japonés (INBOLJAP)'],
            //['nombre' => 'Instituto Técnico Boliviano Suizo (TBS)'],
            //['nombre' => 'Instituto Técnico Naci_personaonal de Comerci_personao (INCOS)'],
            /*['nombre' => 'Instituto Técnologico INFOCAL'],
            ['nombre' => 'Universidad Andina Simón Bolívar'],*/
            ['nombre_institucion' => 'Universidad Autónoma del Beni José Ballivián'],
            ['nombre_institucion' => 'Univ. Autónoma Gabriel Rene Moreno (UAGRM)'],
            ['nombre_institucion' => 'Universidad Autónoma Juan Misael Saracho (UAJMS)'],
            ['nombre_institucion' => 'Universidad Autónoma Tomás Frías (UATF)'],
            /*['nombre' => 'Universidad de Aquino Bolivia '],
            ['nombre' => 'Universidad Bolivia de Informática'],
            ['nombre' => 'Universidad Católica Boliviana San Pablo'],
            ['nombre' => 'Universidad Cristiana de Bolivia (UCEBOL)'],
            ['nombre' => 'Universidad de la Amazonía Boliviana'],
            ['nombre' => 'Universidad de los Andes (UDELOSANDES)'],
            ['nombre' => 'Universidad Indígena Boliviana Aymara Túpac Katari'],
            ['nombre' => 'Universidad La Salle (ULS)'],
            ['nombre' => 'Universidad Loyola '],*/
            ['nombre_institucion' => 'Universidad Mayor de San Andrés (UMSA) '],
            ['nombre_institucion' => 'Universidad Mayor de San Simón'],
            ['nombre_institucion' => 'Universidad Mayor Real y Pontificia San Francisco Xavier de Chuquisaca'],
            /*['nombre' => 'Universidad Naci_personaonal del Oriente (UNO) '],
            ['nombre' => 'Universidad Naci_personaonal Siglo XX (UNSXX)'],*/
            ['nombre_institucion' => 'Universidad Nuestra Señora de La Paz (UNSLP)'],
            /*['nombre' => 'Universidad Simón I. Patiño'],
            ['nombre' => 'Universidad Pedagógica'],
            ['nombre' => 'Universidad Privada Boliviana (UPB)'],
            ['nombre' => 'Universidad Privada del Valle (UNIVALLE)'],
            ['nombre' => 'Universidad Privada De Oruro (UNIOR)'],*/
            ['nombre_institucion' => 'Universidad Privada Domingo Savio'],
            ['nombre_institucion' => 'Universidad Privada Franz Tamayo (UNIFRANZ)'],
           // ['nombre' => 'Univ. Priv. de Sta. Cruz de la Sierra (UPSA)'],
            ['nombre_institucion' => 'Universidad Privada San Francisco de Asis (USFA)'],
            ['nombre_institucion' => 'Universidad Pública de El Alto (UPEA)'],
            ['nombre_institucion' => 'Univ. Salesiana de Bolivia (USALESIANA) '],
            //['nombre' => 'Universidad Técnica de Oruro (UTO)'],
            //['nombre' => 'Universidad Tecnológica Boliviana (UTB)'],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('dde_instituciones');
    }
};
