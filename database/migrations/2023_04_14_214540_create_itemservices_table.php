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
        Schema::create('itemservices', function (Blueprint $table) {
            $table->id();
            
            $table->unsignedBigInteger('id_servicio');
            $table->string('nombre');
            $table->decimal('precio_impresion',8,2);
            $table->decimal('precio_digital',8,2);
            $table->decimal('comision_impreso',8,2);
            $table->decimal('comision_digital',8,2);
            $table->decimal('insumos1',8,2)->nullable();
            $table->decimal('insumos2',8,2)->nullable();
            $table->decimal('insumos3',8,2)->nullable();
            $table->decimal('insumos4',8,2)->nullable();
            $table->foreign('id_servicio')->references('id')->on('catservicios');
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
        Schema::dropIfExists('itemservices');
    }
};
