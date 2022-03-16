<?php

namespace Database\Factories;

use App\Models\Transportadora;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class TransportadoraFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Transportadora::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $tipoTransportadora = $this->faker->randomElement(['pf', 'pj']);
        switch ($tipoTransportadora) {
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
            'tipoTransportadora' => $tipoTransportadora,
            'situacao' => $this->faker->randomElement([1, 0]),
            'tipoContribuinte' => $this->faker->randomElement([1, 2, 9]),
            'inscricaoEstadual' => $this->faker->numerify('############'),
            'nome' => $this->faker->name(),
            'cpfCnpj' => $cpfCnpj,
            'email' => $this->faker->email(),
            'contato' => $this->faker->name(),
            'rua' => $this->faker->word(),
            'cidade' => $this->faker->word(),
            'bairro' => $this->faker->word(),
            'estado' => $this->faker->randomElement(['SP', 'RJ', 'ES', 'MG']),
            'numero' => $this->faker->numberBetween($min = 0, $max = 100),
            'cep' => $this->faker->numerify('#########'),
            'telefone' => $this->faker->numerify('##########'),
            'celular' => $this->faker->numerify('###########'),
            'codigoMunicipio' =>$this->faker->numerify('#######'),
        ];
    }
}
