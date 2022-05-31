<?php

namespace Database\Factories;

use App\Models\Funcionario;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class FuncionarioFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Funcionario::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {

        return [
            "situacao" => 1,
            "nome" => 'Administrador',
            // "cpf" => $this->faker->numerify('###########'),
            // "rg" => $this->faker->numerify('#########'),
            // "dataNascimento" => $this->faker->date($format = 'Y-m-d', $max = 'now'),
            "sexo" => 'masculino',
            "comissao" => 0,//$this->faker->randomFloat(2, 0, 100),
            // "email" => $this->faker->email(),
            // "foto" => $this->faker->imageUrl(640, 480, 'animals', true),
            // "rua" => $this->faker->word(),
            // "cidade" => $this->faker->word(),
            // "numero" => $this->faker->numberBetween($min = 0, $max = 100),
            // "cep" => $this->faker->numerify('########'),
            // "bairro" => $this->faker->word(),
            // "estado" => $this->faker->randomElement(['SP', 'RJ', 'ES', 'MG']),
            // "telefone" => $this->faker->numerify('##########'),
            // "celular" => $this->faker->numerify('##########'),
            "grupo_id" => 1,
            "usuario_id" => 1
        ];
    }
}
