<?php

/**
 * PRODUCT SYNC SYSTEM - EXAMPLES & TESTS
 *
 * This file contains examples of how to use the product sync system.
 * DO NOT run this file directly - these are just examples.
 */

// ============================================================================
// EXAMPLE 1: Manual Product Sync via Service
// ============================================================================

use App\Services\ProductSyncService;

// Sync a specific product from flexmol to metalflex
$syncService = new ProductSyncService();
$syncService->syncProduct(123, 'flexmol'); // Product ID 123

// Sync all products from metalflex to flexmol
$result = $syncService->syncAllProducts('metalflex');
/*
Result:
[
    'synced' => 148,
    'skipped' => 0,
    'errors' => 2,
    'total' => 150
]
*/

// ============================================================================
// EXAMPLE 2: Artisan Commands
// ============================================================================

/*
# Sync a specific product
php artisan products:sync flexmol --product-id=123
php artisan products:sync metalflex --product-id=456

# Sync all products
php artisan products:sync flexmol --all
php artisan products:sync metalflex --all
*/

// ============================================================================
// EXAMPLE 3: Automatic Sync (Already Implemented)
// ============================================================================

// When you create or update a product via API, it automatically syncs!

// Example POST request to create product:
/*
POST /produto
{
    "nome": "Arame para molas",
    "codigoInterno": "123456789",
    "grupo_produto_id": 1,
    "unidade_produto_id": 2,
    // ... other fields
}

Response:
{
    "success": true,
    "code": 200,
    "message": "Produto cadastrado com sucesso",
    "data": { ... }
}

// Product is automatically synced to the other database in the background!
*/

// ============================================================================
// EXAMPLE 4: Testing Sync Logic
// ============================================================================

use Illuminate\Support\Facades\DB;

// Check if product exists in both databases
$flexmolProduct = DB::connection('flexmol')
    ->table('produtos')
    ->where('codigoInterno', '123456789')
    ->first();

$metalflexProduct = DB::connection('metalflex')
    ->table('produtos')
    ->where('codigoInterno', '123456789')
    ->first();

// Compare timestamps
if ($flexmolProduct && $metalflexProduct) {
    echo "FlexMol updated_at: " . $flexmolProduct->updated_at . "\n";
    echo "MetalFlex updated_at: " . $metalflexProduct->updated_at . "\n";

    if (strtotime($flexmolProduct->updated_at) > strtotime($metalflexProduct->updated_at)) {
        echo "FlexMol is newer\n";
    } else {
        echo "MetalFlex is newer or same\n";
    }
}

// ============================================================================
// EXAMPLE 5: Check Sync Status
// ============================================================================

// Get current database name
$syncService = new ProductSyncService();
$currentDb = $syncService->getCurrentDatabaseName();

if ($currentDb === 'flexmol') {
    echo "Currently connected to FlexMol database\n";
    echo "Products will sync to MetalFlex\n";
} elseif ($currentDb === 'metalflex') {
    echo "Currently connected to MetalFlex database\n";
    echo "Products will sync to FlexMol\n";
} else {
    echo "Not connected to a syncable database\n";
}

// ============================================================================
// EXAMPLE 6: Monitor Sync Logs
// ============================================================================

/*
# Watch logs in real-time
tail -f storage/logs/laravel.log | grep "Product sync"

# View recent sync events
grep "Product sync" storage/logs/laravel.log | tail -20

# Count sync errors
grep "Product sync failed" storage/logs/laravel.log | wc -l

# View successful syncs
grep "synced from" storage/logs/laravel.log | tail -10
*/

// ============================================================================
// EXAMPLE 7: Debugging Sync Issues
// ============================================================================

// Check if related entities exist
$grupoProduto = DB::connection('flexmol')
    ->table('grupos_produtos')
    ->where('id', 1)
    ->first();

echo "Grupo Produto: " . $grupoProduto->nome . "\n";

// Check if it exists in target database
$targetGrupo = DB::connection('metalflex')
    ->table('grupos_produtos')
    ->where('nome', $grupoProduto->nome)
    ->first();

if ($targetGrupo) {
    echo "Grupo exists in MetalFlex with ID: " . $targetGrupo->id . "\n";
} else {
    echo "Grupo does NOT exist in MetalFlex - will be created during sync\n";
}

// ============================================================================
// EXAMPLE 8: Count Products in Both Databases
// ============================================================================

$flexmolCount = DB::connection('flexmol')->table('produtos')->count();
$metalflexCount = DB::connection('metalflex')->table('produtos')->count();

echo "FlexMol products: {$flexmolCount}\n";
echo "MetalFlex products: {$metalflexCount}\n";
echo "Difference: " . abs($flexmolCount - $metalflexCount) . "\n";

// ============================================================================
// EXAMPLE 9: Test Database Connections
// ============================================================================

try {
    $flexmolConnection = DB::connection('flexmol')->getPdo();
    echo "✓ FlexMol connection successful\n";
} catch (Exception $e) {
    echo "✗ FlexMol connection failed: " . $e->getMessage() . "\n";
}

try {
    $metalflexConnection = DB::connection('metalflex')->getPdo();
    echo "✓ MetalFlex connection successful\n";
} catch (Exception $e) {
    echo "✗ MetalFlex connection failed: " . $e->getMessage() . "\n";
}

// ============================================================================
// EXAMPLE 10: Sync Data Validation
// ============================================================================

// Get a product from both databases and compare
$codigoInterno = '123456789';

$p1 = DB::connection('flexmol')
    ->table('produtos')
    ->where('codigoInterno', $codigoInterno)
    ->first();

$p2 = DB::connection('metalflex')
    ->table('produtos')
    ->where('codigoInterno', $codigoInterno)
    ->first();

if ($p1 && $p2) {
    $differences = [];

    $fields = ['nome', 'valorCusto', 'custoFinal', 'quantidadeAtual'];

    foreach ($fields as $field) {
        if ($p1->$field != $p2->$field) {
            $differences[] = [
                'field' => $field,
                'flexmol' => $p1->$field,
                'metalflex' => $p2->$field,
            ];
        }
    }

    if (empty($differences)) {
        echo "✓ Products are in sync!\n";
    } else {
        echo "✗ Products have differences:\n";
        print_r($differences);
    }
}

// ============================================================================
// NOTES
// ============================================================================

/*
IMPORTANT THINGS TO REMEMBER:

1. Sync is AUTOMATIC when creating/updating products via API
2. Use artisan command for manual syncs or bulk operations
3. Sync uses codigoInterno as the unique identifier
4. Related entities are matched by:
   - grupos_produtos: nome
   - unidades_produtos: nome
   - clientes: cpfCnpj
   - fornecedores: cpfCnpj
5. Conflict resolution uses updated_at (latest wins)
6. Photos are NOT synced
7. Errors are logged but don't fail the request
8. Sync is BIDIRECTIONAL (flexmol ⟷ metalflex)
*/
