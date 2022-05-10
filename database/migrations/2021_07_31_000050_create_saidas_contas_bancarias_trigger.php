<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateSaidasContasBancariasTrigger extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $script1 =
            <<<SQL
            CREATE TRIGGER `TRG_transacoes_after_insert` AFTER INSERT ON `transacoes`
            FOR EACH ROW
            BEGIN
                IF(new.situacao = 'registrada') THEN
                    IF(new.tipo = 'rendimento') THEN
                        CALL SP_atualizar_contas_bancarias (new.conta_bancaria_id, new.valor);
                    ELSEIF(new.tipo = 'despesa') THEN
                        CALL SP_atualizar_contas_bancarias (new.conta_bancaria_id, new.valor * -1);
                    END IF;
                END IF;

            END;
        SQL;

        $script2 =
            <<<SQL
            CREATE TRIGGER `TRG_transacoes_after_update` AFTER UPDATE ON `transacoes`
            FOR EACH ROW
            BEGIN
            IF(old.situacao = 'aberta' AND new.situacao = 'registrada') THEN -- se a transação estava aberta e foi registrada
                IF(new.tipo = 'rendimento') THEN
                    CALL SP_atualizar_contas_bancarias (new.conta_bancaria_id, new.valor);
                ELSEIF(new.tipo = 'despesa') THEN
                        CALL SP_atualizar_contas_bancarias (new.conta_bancaria_id, new.valor * -1);
                END IF;
            ELSEIF(old.situacao = 'registrada' AND new.situacao = 'aberta') THEN -- se a transação estava registrada e foi aberta
                IF(old.tipo = 'rendimento') THEN
                    CALL SP_atualizar_contas_bancarias (old.conta_bancaria_id, old.valor * -1);
                ELSEIF(old.tipo = 'despesa') THEN
                        CALL SP_atualizar_contas_bancarias (old.conta_bancaria_id, old.valor);
                END IF;
            ELSEIF(old.situacao = 'registrada' AND new.situacao = 'registrada' AND old.conta_bancaria_id <> new.conta_bancaria_id) THEN -- se a transação estava registrada e foi alterada para outra conta bancária
                IF(old.tipo = 'rendimento') THEN
                    CALL SP_atualizar_contas_bancarias (old.conta_bancaria_id, old.valor * -1);
                    CALL SP_atualizar_contas_bancarias (new.conta_bancaria_id, new.valor);
                ELSEIF(old.tipo = 'despesa') THEN
                    CALL SP_atualizar_contas_bancarias (old.conta_bancaria_id, old.valor);
                    CALL SP_atualizar_contas_bancarias (new.conta_bancaria_id, new.valor * -1);
                END IF;
            ELSEIF(old.valor <> new.valor) THEN -- se a transação foi alterada para outro valor
                IF(old.tipo = 'rendimento') THEN
                    CALL SP_atualizar_contas_bancarias (old.conta_bancaria_id, old.valor * -1);
                    CALL SP_atualizar_contas_bancarias (new.conta_bancaria_id, new.valor);
                ELSEIF(old.tipo = 'despesa') THEN
                    CALL SP_atualizar_contas_bancarias (old.conta_bancaria_id, old.valor);
                    CALL SP_atualizar_contas_bancarias (new.conta_bancaria_id, new.valor * -1);
                END IF;
            END IF;
            END;
        SQL;

        $script3 =
            <<<SQL
            CREATE TRIGGER `TRG_transacoes_after_delete` AFTER DELETE ON `transacoes`
            FOR EACH ROW
            BEGIN
            IF(old.situacao = 'registrada') THEN
                IF(old.tipo = 'rendimento') THEN
                    CALL SP_atualizar_contas_bancarias (old.conta_bancaria_id, old.valor * -1);
                ELSEIF(old.tipo = 'despesa') THEN
                    CALL SP_atualizar_contas_bancarias (old.conta_bancaria_id, old.valor);
                END IF;
            END IF;
            END;

        SQL;

        DB::unprepared($script1);
        DB::unprepared($script2);
        DB::unprepared($script3);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $script1 = "DROP TRIGGER IF EXISTS `TRG_transacoes_after_insert`";
        $script2 = "DROP TRIGGER IF EXISTS `TRG_transacoes_after_update`";
        $script3 = "DROP TRIGGER IF EXISTS `TRG_transacoes_after_delete`";

        DB::unprepared($script1);
        DB::unprepared($script2);
        DB::unprepared($script3);
    }
}
