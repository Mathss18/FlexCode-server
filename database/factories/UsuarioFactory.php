<?php

namespace Database\Factories;

use App\Models\Usuario;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

class UsuarioFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Usuario::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'nome' =>  'Admin Teste',//$this->faker->name(),
            'email' => 'a@a.com', //$this->faker->unique()->safeEmail(),
            'senha' => Hash::make('1234'),
            'situacao' => 1 // 1 -> ativo, 2 -> inativo
        ];
    }
}
