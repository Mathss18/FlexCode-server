<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdensServicosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ordens_servicos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('numero')->unique();
            $table->foreignId('cliente_id')->nullable()->constrained('clientes');
            $table->foreignId('venda_id')->nullable()->constrained('vendas');
            $table->integer('situacao');
            $table->string('dataEntrada');
            $table->string('horaEntrada');
            $table->string('dataSaida')->nullable();
            $table->string('horaSaida')->nullable();
            $table->double('frete', 8, 2);
            $table->double('outros', 8, 2);
            $table->double('desconto', 8, 2);
            $table->double('total', 8, 2);
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
        Schema::dropIfExists('ordens_servicos');
    }
}
