<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateConfiguracoesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('configuracoes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('nome')->nullable();
            $table->string('nomeFantasia')->nullable();
            $table->string('logo')->nullable();
            $table->string('certificadoDigital')->nullable();
            $table->string('senhaCertificadoDigital')->nullable();
            $table->string('inscricaoEstadual')->nullable();
            $table->string('crt')->default('1');
            $table->string('cpfCnpj')->nullable();
            $table->string('rua')->nullable();
            $table->string('numero')->nullable();
            $table->string('bairro')->nullable();
            $table->string('codigoMunicipio')->nullable();
            $table->string('cidade')->nullable();
            $table->string('estado')->nullable();
            $table->string('cep')->nullable();
            $table->string('telefone')->nullable();
            $table->string('celular')->nullable();
            $table->string('tipoEmpresa')->nullable();
            $table->string('email')->nullable();
            $table->string('emailNfe')->nullable();
            $table->integer('nNF')->nullable();
            $table->integer('serie')->nullable();
            $table->integer('ambienteNfe')->default(2);
            $table->double('aliquota', 9, 4);
            $table->string('proxyIp')->nullable();
            $table->string('proxyPort')->nullable();
            $table->string('proxyUser')->nullable();
            $table->string('proxyPass')->nullable();
            $table->string('servidorSmtp')->nullable();
            $table->string('portaSmtp')->nullable();
            // $table->string('emailSmtp')->nullable();
            $table->string('usuarioSmtp')->nullable();
            $table->string('senhaSmtp')->nullable();
            $table->string('encryptionSmtp')->nullable();
            $table->integer('quantidadeCasasDecimaisValor')->default(2);
            $table->integer('quantidadeCasasDecimaisQuantidade')->default(2);
            $table->integer('registrosPorPagina')->default(10);
            $table->boolean('situacao');
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
        Schema::dropIfExists('configuracoes');
    }
}
