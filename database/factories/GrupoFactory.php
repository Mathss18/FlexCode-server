<?php

namespace Database\Factories;

use App\Models\Grupo;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class GrupoFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Grupo::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {

        return [
            "nome" => "Administrador",
            "domingo" => 1,
            "segunda" => 1,
            "terca" => 1,
            "quarta" => 1,
            "quinta" => 1,
            "sexta" => 1,
            "sabado" => 1,
            "horaInicio" => "00:00:00",
            "horaFim" => "23:55:00",
            "acessos" => '[{"path":"/","situacao":true},{"path":"/ordens-servicos-funcionarios","situacao":true},{"path":"/clientes","situacao":true},{"path":"/fornecedores","situacao":true},{"path":"/transportadoras","situacao":true},{"path":"/funcionarios","situacao":true},{"path":"/grupos","situacao":true},{"path":"/produtos","situacao":true},{"path":"/grupos-produtos","situacao":true},{"path":"/unidades-produtos","situacao":true},{"path":"/porcentagens-lucros","situacao":true},{"path":"/servicos","situacao":true},{"path":"/money","situacao":true},{"path":"/contas-bancarias","situacao":true},{"path":"/compras","situacao":true},{"path":"/vendas","situacao":true},{"path":"/orcamentos","situacao":true},{"path":"/ordens-servicos","situacao":true},{"path":"/formas-pagamentos","situacao":true},{"path":"/notas-fiscais","situacao":true},{"path":"/estoques","situacao":true},{"path":"/relatorios","situacao":true}]',
        ];
    }
}
