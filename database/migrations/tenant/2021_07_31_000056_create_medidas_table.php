<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMedidasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('medidas', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('ordem_servico_id')->nullable()->constrained('ordens_servicos');
            $table->string('material')->nullable();
            $table->string('acabamento')->nullable();
            $table->string('tipo')->nullable();
            $table->string('codigo')->nullable();
            $table->double('arame', 9, 4)->nullable();
            $table->double('interno', 9, 4)->nullable();
            $table->double('externo', 9, 4)->nullable();
            $table->double('passo', 9, 4)->nullable();
            $table->double('comprimento_corpo', 9, 4)->nullable();
            $table->double('comprimento_total', 9, 4)->nullable();
            $table->integer('quantidade')->nullable();
            $table->double('espiras', 9, 4)->nullable();
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
        Schema::dropIfExists('medidas');
    }
}
