<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSaidasProdutosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('saidas_produtos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('produto_id')->constrained('produtos');
            $table->foreignId('cliente_id')->nullable()->constrained('clientes');
            $table->foreignId('fornecedor_id')->nullable()->constrained('fornecedores');
            $table->foreignId('usuario_id')->nullable()->constrained('usuarios');
            $table->double('quantidade', 8, 4);
            $table->double('quantidadeMomento', 8, 4);
            $table->double('preco', 8, 4);
            $table->string('nome_usuario')->nullable();
            $table->string('observacao')->nullable();
            $table->string('tipo')->default('saida');
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
        Schema::dropIfExists('saidas_produtos');
    }
}
