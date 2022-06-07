<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNotasFiscaisTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notas_fiscais', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('nNF');                             // Número da Nota Fiscal
            $table->string('tpNF');                             // Tipo da Nota Fiscal (0=Entrada, 1=Saída)
            $table->string('finNFe');                           // Finalidade da emissão da Nota Fiscal (1=NF-e normal, 2=NF-e complementar, 3=NF-e de ajuste, 4=Devolução/Retorno)
            $table->integer('natOp_value');                     // Natureza da Operação (CFOP - Código)
            $table->string('natOp_label');                      // Natureza da Operação (CFOP - Descrição)
            $table->integer('favorecido_id');                   // Código do Favorecido
            $table->string('favorecido_nome');                  // Nome do Favorecido
            $table->string('tipoFavorecido');                   // Tipo do Favorecido ('clientes' ou 'fornecedores')
            $table->string('chaveNF');                          // Chave da Nota Fiscal
            $table->string('protocolo');                        // Protocolo da Nota Fiscal
            $table->double('totalFinal', 9, 4);                 // Valor da Nota Fiscal
            $table->double('totalProdutos', 9, 4);              // Valor da Nota Fiscal
            $table->double('desconto', 9, 4)->nullable();       // Desconto da Nota Fiscal
            $table->double('frete', 9, 4)->nullable();          // Frete da Nota Fiscal
            $table->double('pesoL', 9, 4)->nullable();          // Peso Líquido da Nota Fiscal
            $table->double('pesoB', 9, 4)->nullable();          // Peso Bruto da Nota Fiscal
            $table->double('qVol', 9, 4)->nullable();           // Peso Bruto da Nota Fiscal
            $table->integer('modFrete')->nullable();
            $table->integer('quantidadeParcelas')->nullable();
            $table->string('tipoFormaPagamento')->nullable();
            $table->string('situacao');
            $table->foreignId('forma_pagamento_id')->nullable()->constrained('formas_pagamentos');
            $table->foreignId('transportadora_id')->nullable()->constrained('transportadoras');
            $table->foreignId('venda_id')->nullable()->constrained('vendas');
            $table->string('nome_usuario')->nullable();
            $table->text('infAdFisco')->nullable();
            $table->text('infCpl')->nullable();
            $table->integer('nSeqEvento')->nullable()->default(0);

            $table->string('xml')->nullable();
            $table->string('danfe')->nullable();

            $table->string('correcaoXml')->nullable();
            $table->string('correcaoPdf')->nullable();

            $table->string('cancelamentoXml')->nullable();
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
        Schema::dropIfExists('notas_fiscais');
    }
}
