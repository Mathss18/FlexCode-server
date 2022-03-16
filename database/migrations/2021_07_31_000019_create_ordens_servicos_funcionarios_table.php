<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdensServicosFuncionariosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ordens_servicos_funcionarios', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('funcionario_id')->nullable()->constrained('funcionarios');
            $table->foreignId('ordem_servico_id')->nullable()->constrained('ordens_servicos');
            $table->boolean('finalizado')->default(false);
            $table->string('dataFinalizado')->nullable();
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
        Schema::dropIfExists('ordens_servicos_funcionarios');
    }
}
