<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePersonasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('personas', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('tipo_persona_id');
            $table->foreign('tipo_persona_id')->references('id')->on('tipos_personas')->onDelete('cascade');

            $table->unsignedBigInteger('barrio_id')->nullable();;
            $table->foreign('barrio_id')->references('id')->on('barrios')->onDelete('cascade');

            $table->string("numerodocumento");
            $table->string("nombrecompleto");
            $table->string("telefono")->nullable();;

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
        Schema::dropIfExists('personas');
    }
}
