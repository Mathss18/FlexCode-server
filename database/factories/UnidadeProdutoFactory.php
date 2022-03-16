<?php

namespace Database\Factories;

use App\Models\UnidadeProduto;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class UnidadeProdutoFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = UnidadeProduto::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {

        return [
            "nome"=> "Unidade",
            "sigla"=> "un",
            "padrao"=> 1
        ];
    }
}
