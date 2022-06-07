<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateComprasParcelasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('compras_parcelas', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('compra_id')->constrained('compras');
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
        Schema::dropIfExists('compras_parcelas');
    }
}
