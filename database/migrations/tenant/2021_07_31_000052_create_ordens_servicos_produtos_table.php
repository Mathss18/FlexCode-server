<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdensServicosProdutosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ordens_servicos_produtos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('produto_id')->nullable()->constrained('produtos');
            $table->foreignId('ordem_servico_id')->nullable()->constrained('ordens_servicos');
            $table->double('quantidade', 8, 4);
            $table->double('preco', 8, 4);
            $table->double('total', 8, 4);
            $table->json('situacao')->nullable();
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
        Schema::dropIfExists('ordens_servicos_produtos');
    }
}
