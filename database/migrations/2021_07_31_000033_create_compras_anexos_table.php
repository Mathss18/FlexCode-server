<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePedidosComprasProdutosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pedidos_compras_produtos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('produto_id')->nullable()->constrained('produtos');
            $table->foreignId('pedido_compra_id')->nullable()->constrained('pedidos_compras');
            $table->double('quantidade', 8, 2);
            $table->double('preco', 8, 2);
            $table->double('total', 8, 2);
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
        Schema::dropIfExists('pedidos_compras_produtos');
    }
}
