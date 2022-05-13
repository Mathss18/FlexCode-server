<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePorcentagensLucrosGruposProdutosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('porcentagens_lucros_grupos_produtos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('porcentagem_lucro_id')->nullable()->constrained('porcentagens_lucros');
            $table->foreignId('grupo_produto_id')->nullable()->constrained('grupos_produtos');

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
        Schema::dropIfExists('porcentagens_lucros_grupos_produtos');
    }
}
