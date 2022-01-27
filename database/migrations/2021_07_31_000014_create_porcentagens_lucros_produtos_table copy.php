<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePorcentagensLucrosProdutosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('porcentages_lucros_produtos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('porcentagem_lucro_id')->nullable()->constrained('porcentages_lucros');
            $table->foreignId('produto_id')->nullable()->constrained('produtos');

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
        Schema::dropIfExists('porcentages_lucros_produtos');
    }
}
