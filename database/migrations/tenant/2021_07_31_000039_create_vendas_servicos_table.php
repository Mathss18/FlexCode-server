<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVendasServicosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vendas_servicos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('servico_id')->constrained('servicos');
            $table->foreignId('venda_id')->constrained('vendas');
            $table->double('quantidade', 8, 4);
            $table->double('preco', 8, 4);
            $table->double('total', 8, 4);
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
        Schema::dropIfExists('vendas_servicos');
    }
}
