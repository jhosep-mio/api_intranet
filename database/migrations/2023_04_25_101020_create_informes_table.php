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
        Schema::create('informes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_orden');
            $table->unsignedBigInteger('id_servicio');
            $table->string('informe');

            $table->foreign('id_orden')->references('id')->on('ordenes');
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
        Schema::dropIfExists('informes');
    }
};
