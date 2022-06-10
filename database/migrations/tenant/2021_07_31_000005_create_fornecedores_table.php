<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFornecedoresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fornecedores', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('tipoFornecedor');
            $table->integer('situacao');
            $table->string('tipoContribuinte')->nullable();
            $table->string('inscricaoEstadual')->nullable();
            $table->string('nome')->unique();
            $table->string('cpfCnpj')->unique()->nullable();
            $table->string('email')->nullable();
            $table->text('emailDocumento')->nullable();
            $table->text('observacao')->nullable();
            $table->string('contato')->nullable();
            $table->string('rua')->nullable();
            $table->string('cidade')->nullable();
            $table->string('numero')->nullable();
            $table->string('cep')->nullable();
            $table->string('bairro')->nullable();
            $table->string('estado')->nullable();
            $table->string('complemento')->nullable();
            $table->string('telefone')->nullable();
            $table->string('celular')->nullable();
            $table->string('codigoMunicipio')->nullable();
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
        Schema::dropIfExists('fornecedores');
    }
}
