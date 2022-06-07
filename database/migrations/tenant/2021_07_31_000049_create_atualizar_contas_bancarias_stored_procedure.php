<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateAtualizarContasBancariasStoredProcedure extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $script = <<<SQL
        DROP PROCEDURE IF EXISTS `SP_atualizar_contas_bancarias`;
        CREATE PROCEDURE `SP_atualizar_contas_bancarias`( `conta_bancaria_id` int, `valor` decimal(9,2))
            BEGIN
                declare contador int(11);

                SELECT count(*) into contador FROM contas_bancarias WHERE id = conta_bancaria_id;

                IF contador > 0 THEN
                    UPDATE contas_bancarias SET saldo=saldo + valor WHERE id = conta_bancaria_id;
                ELSE
                    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = '[Erro Stored Procedure] - Banco não encontrado';
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
        $script = "DROP PROCEDURE IF EXISTS `SP_atualizar_contas_bancarias`";

        DB::unprepared($script);
    }
}
