<?php

/**
 * PRODUCT SYNC VERIFICATION SCRIPT
 *
 * Run this script to verify the product sync setup is working correctly.
 *
 * Usage: php artisan tinker < VERIFY_SYNC_SETUP.php
 * Or copy and paste into tinker session
 */

use Illuminate\Support\Facades\DB;
use App\Services\ProductSyncService;

echo "\n";
echo "=============================================================\n";
echo "PRODUCT SYNC SYSTEM - VERIFICATION\n";
echo "=============================================================\n\n";

$passed = 0;
$failed = 0;

// Test 1: Check database connections
echo "TEST 1: Database Connections\n";
echo "-----------------------------\n";

try {
    DB::connection('flexmol')->getPdo();
    echo "✓ FlexMol connection: OK\n";
    $passed++;
} catch (Exception $e) {
    echo "✗ FlexMol connection: FAILED - " . $e->getMessage() . "\n";
    $failed++;
}

try {
    DB::connection('metalflex')->getPdo();
    echo "✓ MetalFlex connection: OK\n";
    $passed++;
} catch (Exception $e) {
    echo "✗ MetalFlex connection: FAILED - " . $e->getMessage() . "\n";
    $failed++;
}

echo "\n";

// Test 2: Check ProductSyncService exists
echo "TEST 2: ProductSyncService Class\n";
echo "--------------------------------\n";

try {
    $syncService = new ProductSyncService();
    echo "✓ ProductSyncService instantiated: OK\n";
    $passed++;
} catch (Exception $e) {
    echo "✗ ProductSyncService: FAILED - " . $e->getMessage() . "\n";
    $failed++;
}

echo "\n";

// Test 3: Check database has produtos table
echo "TEST 3: Database Tables\n";
echo "----------------------\n";

try {
    $flexmolProducts = DB::connection('flexmol')->table('produtos')->count();
    echo "✓ FlexMol produtos table: OK ({$flexmolProducts} products)\n";
    $passed++;
} catch (Exception $e) {
    echo "✗ FlexMol produtos table: FAILED - " . $e->getMessage() . "\n";
    $failed++;
}

try {
    $metalflexProducts = DB::connection('metalflex')->table('produtos')->count();
    echo "✓ MetalFlex produtos table: OK ({$metalflexProducts} products)\n";
    $passed++;
} catch (Exception $e) {
    echo "✗ MetalFlex produtos table: FAILED - " . $e->getMessage() . "\n";
    $failed++;
}

echo "\n";

// Test 4: Check related tables
echo "TEST 4: Related Tables\n";
echo "---------------------\n";

$tables = ['grupos_produtos', 'unidades_produtos', 'clientes', 'fornecedores', 'produtos_fornecedores'];

foreach ($tables as $table) {
    try {
        $count = DB::connection('flexmol')->table($table)->count();
        echo "✓ FlexMol {$table}: OK ({$count} records)\n";
        $passed++;
    } catch (Exception $e) {
        echo "✗ FlexMol {$table}: FAILED\n";
        $failed++;
    }
}

echo "\n";

// Test 5: Check service methods
echo "TEST 5: Service Methods\n";
echo "----------------------\n";

try {
    $syncService = new ProductSyncService();

    if (method_exists($syncService, 'syncProduct')) {
        echo "✓ syncProduct method: OK\n";
        $passed++;
    } else {
        echo "✗ syncProduct method: NOT FOUND\n";
        $failed++;
    }

    if (method_exists($syncService, 'syncAllProducts')) {
        echo "✓ syncAllProducts method: OK\n";
        $passed++;
    } else {
        echo "✗ syncAllProducts method: NOT FOUND\n";
        $failed++;
    }

    if (method_exists($syncService, 'getCurrentDatabaseName')) {
        echo "✓ getCurrentDatabaseName method: OK\n";
        $passed++;
    } else {
        echo "✗ getCurrentDatabaseName method: NOT FOUND\n";
        $failed++;
    }
} catch (Exception $e) {
    echo "✗ Service methods check: FAILED - " . $e->getMessage() . "\n";
    $failed += 3;
}

echo "\n";

// Test 6: Check command registration
echo "TEST 6: Artisan Command\n";
echo "----------------------\n";

try {
    $commands = Artisan::all();
    if (isset($commands['products:sync'])) {
        echo "✓ products:sync command: REGISTERED\n";
        $passed++;
    } else {
        echo "✗ products:sync command: NOT REGISTERED\n";
        $failed++;
    }
} catch (Exception $e) {
    echo "✗ Command check: FAILED - " . $e->getMessage() . "\n";
    $failed++;
}

echo "\n";

// Test 7: Check sample data
echo "TEST 7: Sample Data Check\n";
echo "------------------------\n";

try {
    $flexmolSample = DB::connection('flexmol')
        ->table('produtos')
        ->orderBy('id', 'desc')
        ->first();

    if ($flexmolSample) {
        echo "✓ FlexMol has products\n";
        echo "  Latest: ID {$flexmolSample->id}, Código: {$flexmolSample->codigoInterno}\n";
        $passed++;
    } else {
        echo "⚠ FlexMol has no products (database is empty)\n";
    }
} catch (Exception $e) {
    echo "✗ FlexMol sample check: FAILED\n";
    $failed++;
}

try {
    $metalflexSample = DB::connection('metalflex')
        ->table('produtos')
        ->orderBy('id', 'desc')
        ->first();

    if ($metalflexSample) {
        echo "✓ MetalFlex has products\n";
        echo "  Latest: ID {$metalflexSample->id}, Código: {$metalflexSample->codigoInterno}\n";
        $passed++;
    } else {
        echo "⚠ MetalFlex has no products (database is empty)\n";
    }
} catch (Exception $e) {
    echo "✗ MetalFlex sample check: FAILED\n";
    $failed++;
}

echo "\n";

// Summary
echo "=============================================================\n";
echo "VERIFICATION SUMMARY\n";
echo "=============================================================\n";
echo "Passed: {$passed}\n";
echo "Failed: {$failed}\n";

if ($failed === 0) {
    echo "\n✓ ALL TESTS PASSED! Product sync system is ready to use.\n";
} else {
    echo "\n✗ SOME TESTS FAILED. Please check the errors above.\n";
}

echo "\n";
echo "NEXT STEPS:\n";
echo "-----------\n";
echo "1. Test manual sync:\n";
echo "   php artisan products:sync flexmol --product-id=1\n";
echo "\n";
echo "2. Test full sync:\n";
echo "   php artisan products:sync flexmol --all\n";
echo "\n";
echo "3. Create/update a product via API and check if it syncs\n";
echo "\n";
echo "4. Monitor logs:\n";
echo "   tail -f storage/logs/laravel.log | grep 'Product sync'\n";
echo "\n";

echo "=============================================================\n";
