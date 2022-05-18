<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVendasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vendas', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('numero')->unique();
            $table->foreignId('cliente_id')->constrained('clientes');
            $table->string('dataEntrada');
            $table->integer('situacao');

            $table->foreignId('transportadora_id')->nullable()->constrained('transportadoras');
            $table->boolean('somarFreteAoTotal')->default(true);

            $table->double('frete', 8, 2);
            $table->double('impostos', 8, 2);
            $table->double('desconto', 8, 2);
            $table->double('total', 8, 2);

            $table->foreignId('forma_pagamento_id')->constrained('formas_pagamentos');
            $table->string('tipoFormaPagamento'); // 0 - A VISTA, 1 - A PRAZO
            $table->integer('intervaloParcelas');
            $table->integer('quantidadeParcelas');
            $table->string('dataPrimeiraParcela');

            $table->string('observacao')->nullable();
            $table->string('observacaoInterna')->nullable();
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
        Schema::dropIfExists('vendas');
    }
}