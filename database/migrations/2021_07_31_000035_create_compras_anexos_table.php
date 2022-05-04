<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateComprasAnexosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('compras_anexos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('anexo');
            $table->string('nome');
            $table->string('tamanho');
            $table->foreignId('compra_id')->constrained('compras');
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
        Schema::dropIfExists('compras_anexos');
    }
}
