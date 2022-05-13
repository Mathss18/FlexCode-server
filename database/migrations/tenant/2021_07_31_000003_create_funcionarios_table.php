<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFuncionariosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('funcionarios', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('situacao');
            $table->string('nome')->unique();
            $table->string('cpf')->nullable()->unique();
            $table->string('rg')->nullable()->unique();
            $table->string('dataNascimento')->nullable();
            $table->string('sexo');
            $table->double('comissao', 8, 2);
            $table->string('email')->nullable()->unique();
            $table->string('foto')->nullable();
            $table->string('rua')->nullable();
            $table->string('cidade')->nullable();
            $table->string('numero')->nullable();
            $table->string('cep')->nullable();
            $table->string('bairro')->nullable();
            $table->string('estado')->nullable();
            $table->string('telefone')->nullable();
            $table->string('celular')->nullable();
            $table->foreignId('grupo_id')->nullable()->constrained('grupos');
            $table->foreignId('usuario_id')->nullable()->constrained('usuarios');
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
        Schema::dropIfExists('funcionarios');
    }
}
