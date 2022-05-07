<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateEntradasProdutosTrigger extends Migration
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
            CREATE TRIGGER `TRG_entradas_produtos_after_insert` AFTER INSERT ON `entradas_produtos`
            FOR EACH ROW
            BEGIN
              CALL SP_atualizar_estoques (new.produto_id, new.quantidade);
            END;
        SQL;

        $script2 =
            <<<SQL
            CREATE TRIGGER `TRG_entradas_produtos_after_update` AFTER UPDATE ON `entradas_produtos`
            FOR EACH ROW
            BEGIN
                CALL SP_atualizar_estoques (new.produto_id, new.quantidade - old.quantidade);
            END;
        SQL;

        $script3 =
            <<<SQL
            CREATE TRIGGER `TRG_entradas_produtos_after_delete` AFTER DELETE ON `entradas_produtos`
            FOR EACH ROW
            BEGIN
                CALL SP_atualizar_estoques (old.produto_id, old.quantidade * -1);
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
        $script1 = "DROP TRIGGER IF EXISTS `TRG_entradas_produtos_after_insert`";
        $script2 = "DROP TRIGGER IF EXISTS `TRG_entradas_produtos_after_update`";
        $script3 = "DROP TRIGGER IF EXISTS `TRG_entradas_produtos_after_delete`";

        DB::unprepared($script1);
        DB::unprepared($script2);
        DB::unprepared($script3);
    }
}
