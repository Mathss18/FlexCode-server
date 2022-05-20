<?php

namespace Database\Factories;

use App\Models\Configuracao;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ConfiguracaoFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Configuracao::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'situacao' => true,
            'crt' => '1',
            'nNF' => 1,
            'serie' => 1,
            'ambienteNfe' => 2,
            'aliquota' => 0.00,
            'quantidadeCasasDecimaisValor' => 2,
            'quantidadeCasasDecimaisQuantidade' => 2,
            'registrosPorPagina' => 10,
        ];
    }
}


