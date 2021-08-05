<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGruposTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('grupos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('nome');
            
            $table->boolean('domingo');
            $table->boolean('segunda');
            $table->boolean('terca');
            $table->boolean('quarta');
            $table->boolean('quinta');
            $table->boolean('sexta');
            $table->boolean('sabado');

            $table->string('horaInicio');
            $table->string('horaFim');

            $table->string('clientes');
            $table->string('fornecedores');
            $table->string('grupos');
            $table->string('transportadoras');
            $table->string('usuarios');
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
        Schema::dropIfExists('grupos');
    }
}
