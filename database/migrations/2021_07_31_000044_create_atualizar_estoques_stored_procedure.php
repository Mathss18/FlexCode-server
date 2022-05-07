<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateAtualizarEstoquesStoredProcedure extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $script = <<<SQL
        DROP PROCEDURE IF EXISTS `SP_atualizar_estoques`;
        CREATE PROCEDURE `SP_atualizar_estoques`( `prod_id` int, `qtde` decimal(8,4))
            BEGIN
                declare contador int(11);

                SELECT count(*) into contador FROM estoques WHERE produto_id = prod_id;

                IF contador > 0 THEN
                    UPDATE estoques SET quantidade=quantidade + qtde WHERE produto_id = prod_id;
                    UPDATE produtos SET quantidadeAtual=quantidadeAtual + qtde WHERE id = prod_id;
                ELSE
                    INSERT INTO estoques (produto_id, quantidade) values (prod_id, qtde);
                END IF;
            END ;
        SQL;

        DB::unprepared($script);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $script = "DROP PROCEDURE IF EXISTS `SP_atualizar_estoques`";

        DB::unprepared($script);
    }
}
