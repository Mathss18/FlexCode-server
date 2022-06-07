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
            $table->string('codigoInterno')->unique();
            $table->string('fotoPrincipal')->nullable();
            $table->foreignId('grupo_produto_id')->constrained('grupos_produtos');
            $table->foreignId('unidade_produto_id')->nullable()->constrained('unidades_produtos');
            $table->foreignId('cliente_id')->nullable()->constrained('clientes');
            $table->boolean('movimentaEstoque');
            $table->boolean('habilitaNotaFiscal');
            $table->string('codigoBarras')->nullable()->unique();
            $table->double('peso', 9, 4)->nullable();
            $table->double('largura', 9, 4)->nullable();
            $table->double('altura', 9, 4)->nullable();
            $table->double('comprimento', 9, 4)->nullable();
            $table->double('comissao', 9, 4);
            $table->string('descricao')->nullable();
            $table->double('valorCusto', 9, 4);
            $table->double('despesasAdicionais', 9, 4);
            $table->double('outrasDespesas', 9, 4);
            $table->double('custoFinal', 9, 4);
            $table->double('estoqueMinimo', 9, 4)->nullable();
            $table->double('estoqueMaximo', 9, 4)->nullable();
            $table->double('quantidadeAtual', 9, 4)->nullable();
            $table->string('ncm')->nullable();
            $table->string('cest')->nullable();
            $table->string('cfop')->nullable();
            $table->double('pesoLiquido', 9, 4)->nullable();
            $table->double('pesoBruto', 9, 4)->nullable();
            $table->double('numeroFci', 9, 4)->nullable();
            $table->double('valorAproxTribut', 9, 4)->nullable();
            $table->double('valorPixoPis', 9, 4)->nullable();
            $table->double('valorFixoPisSt', 9, 4)->nullable();
            $table->double('valorFixoCofins', 9, 4)->nullable();
            $table->double('valorFixoCofinsSt', 9, 4)->nullable();

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
