<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdensServicosServicosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ordens_servicos_servicos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('servico_id')->nullable()->constrained('servicos');
            $table->foreignId('ordem_servico_id')->nullable()->constrained('ordens_servicos');
            $table->double('quantidade', 8, 2);
            $table->double('preco', 8, 2);
            $table->double('total', 8, 2);
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
        Schema::dropIfExists('ordens_servicos_servicos');
    }
}
