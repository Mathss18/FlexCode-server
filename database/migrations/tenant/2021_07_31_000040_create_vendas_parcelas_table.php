<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVendasParcelasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vendas_parcelas', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('venda_id')->constrained('vendas');
            $table->string('dataVencimento');
            $table->double('valorParcela', 9, 4);
            $table->foreignId('forma_pagamento_id')->constrained('formas_pagamentos');
            $table->string('observacao')->nullable();
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
        Schema::dropIfExists('vendas_parcelas');
    }
}
