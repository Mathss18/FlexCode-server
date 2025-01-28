<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Schema::table('vendas', function (Blueprint $table) {
        //     $table->decimal('total', 12, 4)->change();
        // });

        // Schema::table('compras', function (Blueprint $table) {
        //     $table->decimal('total', 12, 4)->change();
        // });

        // Schema::table('orcamentos', function (Blueprint $table) {
        //     $table->decimal('total', 12, 4)->change();
        // });

        // Schema::table('notas_fiscais', function (Blueprint $table) {
        //     $table->decimal('totalFinal', 12, 4)->change();
        //     $table->decimal('totalProdutos', 12, 4)->change();
        // });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('vendas', function (Blueprint $table) {
            $table->decimal('total', 9, 4)->change();
        });

        Schema::table('compras', function (Blueprint $table) {
            $table->decimal('total', 9, 4)->change();
        });

        Schema::table('orcamentos', function (Blueprint $table) {
            $table->decimal('total', 9, 4)->change();
        });

        Schema::table('notas_fiscais', function (Blueprint $table) {
            $table->decimal('totalFinal', 9, 4)->change();
            $table->decimal('totalProdutos', 9, 4)->change();
        });
    }
};
