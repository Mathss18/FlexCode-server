<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVendasAnexosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vendas_anexos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('url');
            $table->string('nome');
            $table->string('tamanho');
            $table->foreignId('venda_id')->constrained('vendas');
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
        Schema::dropIfExists('vendas_anexos');
    }
}
