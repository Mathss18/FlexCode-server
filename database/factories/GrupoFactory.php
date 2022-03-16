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
                "nome" => "Admministrador",
                "domingo" => 1,
                "segunda" => 1,
                "terca" => 1,
                "quarta" => 1,
                "quinta" => 1,
                "sexta" => 1,
                "sabado" => 1,
                "horaInicio" => "07:00:00",
                "horaFim" => "17:00:00",
                "clientes" => "1.1.1.1",
                "fornecedores" => "1.1.1.1",
                "grupos" => "1.1.1.1",
                "transportadoras" => "1.1.1.1",
                "usuarios" => "1.1.1.1",
                "funcionarios" => "1.1.1.1"
        ];
    }
}
