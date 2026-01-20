<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class ProductSyncService
{
    const FLEXMOL_DB = 'flexmol';
    const METALFLEX_DB = 'metalflex';

    protected $sourceDb;
    protected $targetDb;
    protected $syncingInProgress = false;

    /**
     * Sync a product from source to target database
     *
     * @param int $productId Product ID in source database
     * @param string $sourceDatabase Source database name (flexmol or metalflex)
     * @param string|null $oldCodigoInterno Old codigoInterno (if it was changed during update)
     */
    public function syncProduct($productId, $sourceDatabase, $oldCodigoInterno = null)
    {
        // Prevent recursive syncing
        if ($this->syncingInProgress) {
            return;
        }

        $this->syncingInProgress = true;

        try {
            $this->sourceDb = $sourceDatabase;
            $this->targetDb = $this->getTargetDatabase($sourceDatabase);

            // Get the product from source database with relationships
            $sourceProduct = DB::connection($this->sourceDb)
                ->table('produtos')
                ->where('id', $productId)
                ->first();

            if (!$sourceProduct) {
                Log::warning("Product with ID {$productId} not found in {$this->sourceDb}");
                return;
            }

            // Check if product already exists in target by codigoInterno
            // If oldCodigoInterno is provided, use it to find the product (codigoInterno was changed)
            $searchCodigoInterno = $oldCodigoInterno ?? $sourceProduct->codigoInterno;
            $targetProduct = DB::connection($this->targetDb)
                ->table('produtos')
                ->where('codigoInterno', $searchCodigoInterno)
                ->first();

            // If we searched by old code and found it, log that we're updating the code
            if ($oldCodigoInterno && $targetProduct) {
                Log::info("Product codigoInterno changed from '{$oldCodigoInterno}' to '{$sourceProduct->codigoInterno}' - updating in {$this->targetDb}");
            }

            DB::connection($this->targetDb)->beginTransaction();

            try {
                // Sync related entities and get their IDs
                $grupoProdutoId = $this->syncGrupoProduto($sourceProduct->grupo_produto_id);
                $unidadeProdutoId = $sourceProduct->unidade_produto_id
                    ? $this->syncUnidadeProduto($sourceProduct->unidade_produto_id)
                    : null;
                $clienteId = $sourceProduct->cliente_id
                    ? $this->syncCliente($sourceProduct->cliente_id)
                    : null;

                // Prepare product data (excluding photos and auto-generated fields)
                $productData = [
                    'nome' => $sourceProduct->nome,
                    'codigoInterno' => $sourceProduct->codigoInterno,
                    'grupo_produto_id' => $grupoProdutoId,
                    'unidade_produto_id' => $unidadeProdutoId,
                    'cliente_id' => $clienteId,
                    'movimentaEstoque' => $sourceProduct->movimentaEstoque,
                    'habilitaNotaFiscal' => $sourceProduct->habilitaNotaFiscal,
                    'codigoBarras' => $sourceProduct->codigoBarras,
                    'peso' => $sourceProduct->peso,
                    'largura' => $sourceProduct->largura,
                    'altura' => $sourceProduct->altura,
                    'comprimento' => $sourceProduct->comprimento,
                    'comissao' => $sourceProduct->comissao,
                    'descricao' => $sourceProduct->descricao,
                    'valorCusto' => $sourceProduct->valorCusto,
                    'despesasAdicionais' => $sourceProduct->despesasAdicionais,
                    'outrasDespesas' => $sourceProduct->outrasDespesas,
                    'custoFinal' => $sourceProduct->custoFinal,
                    'estoqueMinimo' => $sourceProduct->estoqueMinimo,
                    'estoqueMaximo' => $sourceProduct->estoqueMaximo,
                    'quantidadeAtual' => $sourceProduct->quantidadeAtual,
                    'ncm' => $sourceProduct->ncm,
                    'cest' => $sourceProduct->cest,
                    'cfop' => $sourceProduct->cfop,
                    'pesoLiquido' => $sourceProduct->pesoLiquido,
                    'pesoBruto' => $sourceProduct->pesoBruto,
                    'numeroFci' => $sourceProduct->numeroFci,
                    'valorAproxTribut' => $sourceProduct->valorAproxTribut,
                    'valorPixoPis' => $sourceProduct->valorPixoPis,
                    'valorFixoPisSt' => $sourceProduct->valorFixoPisSt,
                    'valorFixoCofins' => $sourceProduct->valorFixoCofins,
                    'valorFixoCofinsSt' => $sourceProduct->valorFixoCofinsSt,
                    'updated_at' => now(),
                ];

                if ($targetProduct) {
                    // Update existing product if source is newer
                    if (strtotime($sourceProduct->updated_at) > strtotime($targetProduct->updated_at)) {
                        DB::connection($this->targetDb)
                            ->table('produtos')
                            ->where('id', $targetProduct->id)
                            ->update($productData);

                        $targetProductId = $targetProduct->id;
                        Log::info("Updated product {$sourceProduct->codigoInterno} in {$this->targetDb}");
                    } else {
                        // Target is newer, don't update
                        Log::info("Product {$sourceProduct->codigoInterno} in {$this->targetDb} is newer, skipping update");
                        $targetProductId = $targetProduct->id;
                    }
                } else {
                    // Insert new product
                    $productData['created_at'] = now();
                    $productData['fotoPrincipal'] = ''; // Don't sync photos

                    $targetProductId = DB::connection($this->targetDb)
                        ->table('produtos')
                        ->insertGetId($productData);

                    Log::info("Created new product {$sourceProduct->codigoInterno} in {$this->targetDb}");
                }

                // Sync fornecedores relationship
                $this->syncProdutoFornecedores($sourceProduct->id, $targetProductId);

                DB::connection($this->targetDb)->commit();

            } catch (Exception $e) {
                DB::connection($this->targetDb)->rollBack();
                Log::error("Error syncing product: " . $e->getMessage());
                throw $e;
            }

        } finally {
            $this->syncingInProgress = false;
        }
    }

    /**
     * Sync grupo_produto from source to target
     */
    protected function syncGrupoProduto($grupoProdutoId)
    {
        $sourceGrupo = DB::connection($this->sourceDb)
            ->table('grupos_produtos')
            ->where('id', $grupoProdutoId)
            ->first();

        if (!$sourceGrupo) {
            throw new Exception("Grupo produto with ID {$grupoProdutoId} not found in source database");
        }

        // Check if exists by nome
        $targetGrupo = DB::connection($this->targetDb)
            ->table('grupos_produtos')
            ->where('nome', $sourceGrupo->nome)
            ->first();

        if ($targetGrupo) {
            return $targetGrupo->id;
        }

        // Create new grupo_produto
        $grupoData = [
            'nome' => $sourceGrupo->nome,
            'grupoPai' => $sourceGrupo->grupoPai,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        $newId = DB::connection($this->targetDb)
            ->table('grupos_produtos')
            ->insertGetId($grupoData);

        Log::info("Created grupo_produto '{$sourceGrupo->nome}' in {$this->targetDb}");

        return $newId;
    }

    /**
     * Sync unidade_produto from source to target
     */
    protected function syncUnidadeProduto($unidadeProdutoId)
    {
        $sourceUnidade = DB::connection($this->sourceDb)
            ->table('unidades_produtos')
            ->where('id', $unidadeProdutoId)
            ->first();

        if (!$sourceUnidade) {
            throw new Exception("Unidade produto with ID {$unidadeProdutoId} not found in source database");
        }

        // Check if exists by nome
        $targetUnidade = DB::connection($this->targetDb)
            ->table('unidades_produtos')
            ->where('nome', $sourceUnidade->nome)
            ->first();

        if ($targetUnidade) {
            return $targetUnidade->id;
        }

        // Create new unidade_produto
        $unidadeData = [
            'nome' => $sourceUnidade->nome,
            'sigla' => $sourceUnidade->sigla,
            'padrao' => $sourceUnidade->padrao,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        $newId = DB::connection($this->targetDb)
            ->table('unidades_produtos')
            ->insertGetId($unidadeData);

        Log::info("Created unidade_produto '{$sourceUnidade->nome}' in {$this->targetDb}");

        return $newId;
    }

    /**
     * Sync cliente from source to target
     */
    protected function syncCliente($clienteId)
    {
        $sourceCliente = DB::connection($this->sourceDb)
            ->table('clientes')
            ->where('id', $clienteId)
            ->first();

        if (!$sourceCliente) {
            throw new Exception("Cliente with ID {$clienteId} not found in source database");
        }

        // Check if exists by cpfCnpj
        $targetCliente = DB::connection($this->targetDb)
            ->table('clientes')
            ->where('cpfCnpj', $sourceCliente->cpfCnpj)
            ->first();

        if ($targetCliente) {
            return $targetCliente->id;
        }

        // Create new cliente
        $clienteData = [
            'tipoCliente' => $sourceCliente->tipoCliente,
            'situacao' => $sourceCliente->situacao,
            'tipoContribuinte' => $sourceCliente->tipoContribuinte,
            'inscricaoEstadual' => $sourceCliente->inscricaoEstadual,
            'nome' => $sourceCliente->nome,
            'cpfCnpj' => $sourceCliente->cpfCnpj,
            'email' => $sourceCliente->email,
            'emailDocumento' => $sourceCliente->emailDocumento,
            'observacao' => $sourceCliente->observacao,
            'contato' => $sourceCliente->contato,
            'rua' => $sourceCliente->rua,
            'cidade' => $sourceCliente->cidade,
            'numero' => $sourceCliente->numero,
            'cep' => $sourceCliente->cep,
            'bairro' => $sourceCliente->bairro,
            'estado' => $sourceCliente->estado,
            'complemento' => $sourceCliente->complemento,
            'telefone' => $sourceCliente->telefone,
            'celular' => $sourceCliente->celular,
            'codigoMunicipio' => $sourceCliente->codigoMunicipio,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        $newId = DB::connection($this->targetDb)
            ->table('clientes')
            ->insertGetId($clienteData);

        Log::info("Created cliente '{$sourceCliente->nome}' in {$this->targetDb}");

        return $newId;
    }

    /**
     * Sync fornecedor from source to target
     */
    protected function syncFornecedor($fornecedorId)
    {
        $sourceFornecedor = DB::connection($this->sourceDb)
            ->table('fornecedores')
            ->where('id', $fornecedorId)
            ->first();

        if (!$sourceFornecedor) {
            throw new Exception("Fornecedor with ID {$fornecedorId} not found in source database");
        }

        // Check if exists by cpfCnpj
        $targetFornecedor = DB::connection($this->targetDb)
            ->table('fornecedores')
            ->where('cpfCnpj', $sourceFornecedor->cpfCnpj)
            ->first();

        if ($targetFornecedor) {
            return $targetFornecedor->id;
        }

        // Create new fornecedor
        $fornecedorData = [
            'tipoFornecedor' => $sourceFornecedor->tipoFornecedor,
            'situacao' => $sourceFornecedor->situacao,
            'tipoContribuinte' => $sourceFornecedor->tipoContribuinte,
            'inscricaoEstadual' => $sourceFornecedor->inscricaoEstadual,
            'nome' => $sourceFornecedor->nome,
            'cpfCnpj' => $sourceFornecedor->cpfCnpj,
            'email' => $sourceFornecedor->email,
            'emailDocumento' => $sourceFornecedor->emailDocumento,
            'observacao' => $sourceFornecedor->observacao,
            'contato' => $sourceFornecedor->contato,
            'rua' => $sourceFornecedor->rua,
            'cidade' => $sourceFornecedor->cidade,
            'numero' => $sourceFornecedor->numero,
            'cep' => $sourceFornecedor->cep,
            'bairro' => $sourceFornecedor->bairro,
            'estado' => $sourceFornecedor->estado,
            'complemento' => $sourceFornecedor->complemento,
            'telefone' => $sourceFornecedor->telefone,
            'celular' => $sourceFornecedor->celular,
            'codigoMunicipio' => $sourceFornecedor->codigoMunicipio,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        $newId = DB::connection($this->targetDb)
            ->table('fornecedores')
            ->insertGetId($fornecedorData);

        Log::info("Created fornecedor '{$sourceFornecedor->nome}' in {$this->targetDb}");

        return $newId;
    }

    /**
     * Sync produtos_fornecedores relationship
     */
    protected function syncProdutoFornecedores($sourceProductId, $targetProductId)
    {
        // Get all fornecedores for the source product
        $sourceProdutoFornecedores = DB::connection($this->sourceDb)
            ->table('produtos_fornecedores')
            ->where('produto_id', $sourceProductId)
            ->get();

        // Delete existing relationships for target product
        DB::connection($this->targetDb)
            ->table('produtos_fornecedores')
            ->where('produto_id', $targetProductId)
            ->delete();

        // Create new relationships
        foreach ($sourceProdutoFornecedores as $pf) {
            if ($pf->fornecedor_id) {
                $targetFornecedorId = $this->syncFornecedor($pf->fornecedor_id);

                DB::connection($this->targetDb)
                    ->table('produtos_fornecedores')
                    ->insert([
                        'fornecedor_id' => $targetFornecedorId,
                        'produto_id' => $targetProductId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
            }
        }
    }

    /**
     * Get target database based on source
     */
    protected function getTargetDatabase($sourceDatabase)
    {
        return $sourceDatabase === self::FLEXMOL_DB ? self::METALFLEX_DB : self::FLEXMOL_DB;
    }

    /**
     * Get current database name from connection
     */
    public function getCurrentDatabaseName()
    {
        $currentDbName = DB::connection('tenant')->getDatabaseName();

        if ($currentDbName === 'allmac88_flexmol') {
            return self::FLEXMOL_DB;
        } elseif ($currentDbName === 'allmac88_metalflex') {
            return self::METALFLEX_DB;
        }

        return null;
    }

    /**
     * Sync all products from one database to another
     */
    public function syncAllProducts($sourceDatabase)
    {
        $this->sourceDb = $sourceDatabase;
        $this->targetDb = $this->getTargetDatabase($sourceDatabase);

        $products = DB::connection($this->sourceDb)
            ->table('produtos')
            ->orderBy('updated_at', 'desc')
            ->get();

        $synced = 0;
        $skipped = 0;
        $errors = 0;

        foreach ($products as $product) {
            try {
                $this->syncProduct($product->id, $sourceDatabase);
                $synced++;
            } catch (Exception $e) {
                $errors++;
                Log::error("Failed to sync product {$product->id}: " . $e->getMessage());
            }
        }

        return [
            'synced' => $synced,
            'skipped' => $skipped,
            'errors' => $errors,
            'total' => count($products),
        ];
    }
}
