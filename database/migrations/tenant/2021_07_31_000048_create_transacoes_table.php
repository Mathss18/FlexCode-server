<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransacoesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transacoes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('data');
            $table->string('title');
            $table->string('observacao')->nullable();
            $table->double('valor',9 ,2);
            $table->string('tipo');
            $table->string('situacao');
            $table->timestamp('dataTransacaoRegistrada')->nullable();
            $table->integer('favorecido_id');
            $table->string('favorecido_nome');
            $table->string('tipoFavorecido');
            $table->foreignId('conta_bancaria_id')->constrained('contas_bancarias');
            $table->foreignId('compra_id')->nullable()->constrained('compras');
            $table->foreignId('venda_id')->nullable()->constrained('vendas');
            $table->integer('transacao_id')->nullable();
            $table->boolean('isTransferencia')->nullable()->default(false);
            $table->string('nome_usuario')->nullable();


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
        Schema::dropIfExists('transacoes');
    }
}
