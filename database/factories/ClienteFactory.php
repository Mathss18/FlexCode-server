<?php

namespace Database\Factories;

use App\Models\Cliente;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ClienteFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Cliente::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $tipoCliente = $this->faker->randomElement(['pf', 'pj']);
        switch ($tipoCliente) {
            case 'pf':
                $cpfCnpj = $this->faker->numerify('###########');
                break;
            case 'pj':
                $cpfCnpj = $this->faker->numerify('##############');
                break;
            default:
                $cpfCnpj = $this->faker->numerify('###########');
                break;
        }
        return [
            'tipoCliente' => $tipoCliente,
            'situacao' => $this->faker->randomElement([1, 0]),
            'tipoContribuinte' => $this->faker->randomElement([1, 2, 9]),
            'inscricaoEstadual' => $this->faker->numerify('############'),
            'nome' => $this->faker->name(),
            'cpfCnpj' => $cpfCnpj,
            'email' => $this->faker->email(),
            'contato' => $this->faker->name(),
            'rua' => Str::random(15),
            'cidade' => Str::random(10),
            'bairro' => Str::random(10),
            'estado' => $this->faker->randomElement(['SP', 'RJ', 'ES', 'MG']),
            'numero' => $this->faker->numberBetween($min = 0, $max = 100),
            'cep' => $this->faker->numerify('#########'),
            'telefone' => $this->faker->numerify('##########'),
            'celular' => $this->faker->numerify('###########'),
            'codigoMunicipio' =>$this->faker->numerify('#######'),
        ];
    }
}
