<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrcamentosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orcamentos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('numero')->unique();
            $table->foreignId('cliente_id')->nullable()->constrained('clientes');
            $table->foreignId('transportadora_id')->nullable()->constrained('transportadoras');
            $table->integer('situacao');
            $table->string('dataEntrada');
            $table->double('frete', 9, 4);
            $table->double('outros', 9, 4);
            $table->double('desconto', 9, 4);
            $table->double('total', 9, 4);
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
        Schema::dropIfExists('orcamentos');
    }
}
