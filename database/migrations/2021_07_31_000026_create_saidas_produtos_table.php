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
            $table->foreignId('produto_id')->nullable()->constrained('produtos');
            $table->double('quantidade', 8, 4);
            $table->double('preco', 8, 4);
            $table->string('nome_usuario')->nullable();
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
        Schema::dropIfExists('saidas_produtos');
    }
}
