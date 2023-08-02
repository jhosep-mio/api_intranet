<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ordenes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_paciente');
            $table->unsignedBigInteger('id_odontologo');

            $table->string('consulta');

            $table->boolean('box18');
            $table->boolean('box17');
            $table->boolean('box16');
            $table->boolean('box15');
            $table->boolean('box14');
            $table->boolean('box13');
            $table->boolean('box12');
            $table->boolean('box11');

            $table->boolean('box21');
            $table->boolean('box22');
            $table->boolean('box23');
            $table->boolean('box24');
            $table->boolean('box25');
            $table->boolean('box26');
            $table->boolean('box27');
            $table->boolean('box28');

            $table->boolean('box48');
            $table->boolean('box47');
            $table->boolean('box46');
            $table->boolean('box45');
            $table->boolean('box44');
            $table->boolean('box43');
            $table->boolean('box42');
            $table->boolean('box41');

            $table->boolean('box31');
            $table->boolean('box32');
            $table->boolean('box33');
            $table->boolean('box34');
            $table->boolean('box35');
            $table->boolean('box36');
            $table->boolean('box37');
            $table->boolean('box38');
            
            $table->boolean('siConGuias');
            $table->boolean('noConGuias');

            $table->string('listaServicios')->nullable();
            $table->string('impresionServicios')->nullable();
            $table->string('arryServicios')->nullable();
            $table->string('listaItems');
            $table->integer('metodoPago')->nullable();
            $table->decimal('precio_final',8,2);

            $table->string('otrosAnalisis')->nullable();;
            $table->integer('estado');
            $table->integer('activeComision');

            $table->foreign('id_paciente')->references('id')->on('pacientes');
            $table->foreign('id_odontologo')->references('id')->on('odontologos');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ordenes');
    }
};
