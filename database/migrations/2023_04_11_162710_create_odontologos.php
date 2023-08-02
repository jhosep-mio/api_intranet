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
        Schema::create('odontologos', function (Blueprint $table) {
            $table->id();

            $table->integer('id_rol');
            $table->unsignedBigInteger('clinica');
            $table->integer('cop')->unique();
            $table->string('c_bancaria')->nullable();
            $table->string('cci')->nullable();
            $table->string('nombre_banco')->nullable();
            $table->string('nombres');
            $table->string('apellido_p');
            $table->string('apellido_m');
            $table->string('f_nacimiento');
            $table->integer('tipo_documento_paciente_odontologo');
            $table->string('numero_documento_paciente_odontologo')->unique();
            $table->integer('celular');
            $table->string('correo');
            $table->string('genero');

            $table->foreign('clinica')->references('id')->on('clinicas');

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
        Schema::dropIfExists('odontologos');
    }
};
