<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVotantesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('votantes', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('tipo_persona_id');
            $table->foreign('tipo_persona_id')->references('id')->on('tipos_personas')->onDelete('cascade');

            $table->unsignedBigInteger('barrio_id')->nullable();;
            $table->foreign('barrio_id')->references('id')->on('barrios')->onDelete('cascade');

            $table->unsignedBigInteger('municipio_id')->nullable();;
            $table->foreign('municipio_id')->references('id')->on('municipios')->onDelete('cascade');

            $table->unsignedBigInteger('user_id')->nullable();;
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->string("numerodocumento");
            $table->string("nombrecompleto");
            $table->string("fecha_expedicion")->nullable();
            $table->string("telefono")->nullable();;
            $table->string("puesto")->nullable();;
            $table->string("mesa")->nullable();
            $table->integer("estado")->default(1);

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
        Schema::dropIfExists('votantes');
    }
}
