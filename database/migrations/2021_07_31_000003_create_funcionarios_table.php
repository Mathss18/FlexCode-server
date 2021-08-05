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
            $table->string('nome');
            $table->string('cpf');
            $table->string('rg');
            $table->string('dataNascimento');
            $table->string('sexo');
            $table->double('comissao', 8, 2);
            $table->string('email');
            $table->string('foto');
            $table->string('rua');
            $table->string('cidade');
            $table->string('numero');
            $table->string('cep');
            $table->string('bairro');
            $table->string('estado');
            $table->string('telefone');
            $table->string('celular');
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
