<?php

namespace Database\Seeders;

use App\Models\Cliente;
use App\Models\Fornecedor;
use App\Models\Funcionario;
use App\Models\Transportadora;
use App\Models\Grupo;
use App\Models\PorcentagemLucro;
use App\Models\UnidadeProduto;
use App\Models\Usuario;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        Usuario::factory(1)->create();
        Cliente::factory(20)->create();
        Fornecedor::factory(20)->create();
        Transportadora::factory(20)->create();
        Grupo::factory(1)->create();
        Funcionario::factory(5)->create();
        UnidadeProduto::factory(1)->create();
        PorcentagemLucro::factory(1)->create();
    }
}
