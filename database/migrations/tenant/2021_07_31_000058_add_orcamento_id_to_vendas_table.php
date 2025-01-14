<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOrcamentoIdToVendasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('vendas', function (Blueprint $table) {
            $table->unsignedBigInteger('orcamento_id')->nullable(); // Add the column
            $table->foreign('orcamento_id')->references('id')->on('orcamentos')->onDelete('set null'); // Define FK
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('vendas', function (Blueprint $table) {
            $table->dropForeign(['orcamento_id']); // Drop the foreign key
            $table->dropColumn('orcamento_id'); // Drop the column
        });
    }
}
