<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClientesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('clientes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('tipoCliente');
            $table->integer('situacao');
            $table->string('tipoContribuinte');
            $table->string('inscricaoEstadual');
            $table->string('nome');
            $table->string('cpfCnpj');
            $table->string('email');
            $table->string('contato');
            $table->string('rua');
            $table->string('cidade');
            $table->string('numero');
            $table->string('cep');
            $table->string('bairro');
            $table->string('estado');
            $table->string('telefone');
            $table->string('celular');
            $table->string('codigoMunicipio');
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
        Schema::dropIfExists('clientes');
    }
}
