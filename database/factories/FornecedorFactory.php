<?php

namespace Database\Factories;

use App\Models\Fornecedor;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class FornecedorFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Fornecedor::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $tipoFornecedor = $this->faker->randomElement(['pf', 'pj']);
        switch ($tipoFornecedor) {
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
            'tipoFornecedor' => $tipoFornecedor,
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
