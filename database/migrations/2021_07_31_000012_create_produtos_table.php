<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProdutosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('produtos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('nome');
            $table->string('codigoInterno');
            $table->foreignId('grupo_produto_id')->nullable()->constrained('grupos_produtos');
            $table->boolean('movimentaEstoque');
            $table->boolean('habilitaNotaFiscal');
            $table->boolean('possuiVariacoes');
            $table->double('peso', 8, 4);
            $table->double('largura', 8, 4);
            $table->double('altura', 8, 4);
            $table->double('comprimento', 8, 4);
            $table->double('comissao', 8, 2);
            $table->string('descricao');

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
        Schema::dropIfExists('nomes_variacoes_produtos');
    }
}
