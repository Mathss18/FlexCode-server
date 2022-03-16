<?php

namespace Database\Factories;

use App\Models\PorcentagemLucro;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PorcentagemLucroFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = PorcentagemLucro::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {

        return [
            "descricao" => "Varejo",
            "porcentagem" => 100,
            "favorito" => true
        ];
    }
}
