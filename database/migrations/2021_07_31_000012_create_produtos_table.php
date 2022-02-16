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
            $table->string('fotoPrincipal');
            $table->foreignId('grupo_produto_id')->nullable()->constrained('grupos_produtos');
            $table->foreignId('unidade_produto_id')->nullable()->constrained('unidades_produtos');
            $table->foreignId('cliente_id')->nullable()->constrained('clientes');
            $table->boolean('movimentaEstoque');
            $table->boolean('habilitaNotaFiscal');
            $table->string('codigoBarras');
            $table->double('peso', 8, 4);
            $table->double('largura', 8, 4);
            $table->double('altura', 8, 4);
            $table->double('comprimento', 8, 4);
            $table->double('comissao', 8, 2);
            $table->string('descricao');
            $table->double('valorCusto', 8, 2);
            $table->double('despesasAdicionais', 8, 2);
            $table->double('outrasDespesas', 8, 2);
            $table->double('custoFinal', 8, 2);
            $table->double('estoqueMinimo', 8, 4);
            $table->double('estoqueMaximo', 8, 4);
            $table->double('quantidadeAtual', 8, 4);
            $table->string('ncm');
            $table->string('cest');
            $table->string('origem');
            $table->double('pesoLiquido', 8, 4);
            $table->double('pesoBruto', 8, 4);
            $table->double('numeroFci', 8, 4);
            $table->double('valorAproxTribut', 8, 4);
            $table->double('valorPixoPis', 8, 4);
            $table->double('valorFixoPisSt', 8, 4);
            $table->double('valorFixoCofins', 8, 4);
            $table->double('valorFixoCofinsSt', 8, 4);

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
        Schema::dropIfExists('produtos');
    }
}
