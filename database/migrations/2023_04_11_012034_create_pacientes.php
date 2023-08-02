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
        Schema::create('pacientes', function (Blueprint $table) {
            $table->id();
            $table->integer('id_rol');
            $table->string('nombres');
            $table->string('apellido_p');
            $table->string('apellido_m');
            $table->string('f_nacimiento');
            
            $table->string('nombre_apoderado')->nullable();
            $table->integer('tipo_documento_apoderado')->nullable();
            $table->string('documento_apoderado')->nullable();

            $table->integer('tipo_documento_paciente_odontologo');
            $table->string('numero_documento_paciente_odontologo')->unique();
            $table->integer('celular');
            $table->string('correo');
            $table->string('genero');
            $table->integer('embarazada');
            $table->string('enfermedades')->nullable();
            $table->string('discapacidades')->nullable();
            $table->string('paciente_especial')->nullable();

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
        Schema::dropIfExists('pacientes');
    }
};
