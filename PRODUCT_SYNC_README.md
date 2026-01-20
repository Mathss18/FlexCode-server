# Product Synchronization System

## Overview

This system provides bidirectional product synchronization between the `flexmol` and `metalflex` databases. When a product is created or updated in one database, it automatically syncs to the other database.

## Features

- ✅ **Bidirectional Sync**: Products sync from flexmol → metalflex and metalflex → flexmol
- ✅ **Real-time Sync**: Automatic sync when products are created or updated via API
- ✅ **Related Entities**: Automatically syncs related entities (grupos_produtos, unidades_produtos, clientes, fornecedores)
- ✅ **Conflict Resolution**: Uses `updated_at` timestamp (latest version wins)
- ✅ **Manual Sync**: Artisan command for manual product synchronization
- ✅ **Error Handling**: Syncs don't fail API requests; errors are logged

## Architecture

### Components

1. **ProductSyncService** (`app/Services/ProductSyncService.php`)
   - Core sync logic
   - Handles product and related entity synchronization
   - Manages database connections

2. **ProdutoController** (`app/Http/Controllers/ProdutoController.php`)
   - Triggers sync after product create/update
   - Non-blocking sync execution

3. **SyncProducts Command** (`app/Console/Commands/SyncProducts.php`)
   - Manual sync command for maintenance

4. **Database Connections** (`config/database.php`)
   - Dedicated connections for flexmol and metalflex databases

## How It Works

### Automatic Sync (Real-time)

When a product is created or updated through the API:

1. Product is saved to the current database
2. `ProductSyncService` is invoked
3. Current database is detected (flexmol or metalflex)
4. Product data is read from current database
5. Related entities are synced to target database:
   - **grupo_produto**: Matched by `nome`
   - **unidade_produto**: Matched by `nome`
   - **cliente**: Matched by `cpfCnpj`
   - **fornecedor**: Matched by `cpfCnpj`
6. Product is created/updated in target database
7. Products are matched by `codigoInterno`

### Sync Logic

```
Source Product (flexmol)
    ↓
Check if exists in target (metalflex) by codigoInterno
    ↓
    ├─ Exists? → Compare updated_at timestamps
    │             ├─ Source newer? → Update target
    │             └─ Target newer? → Skip
    │
    └─ Doesn't exist? → Create in target
```

### Related Entity Sync

Before syncing a product, all related entities are synced:

| Entity | Match Field | Fields Copied |
|--------|-------------|---------------|
| grupos_produtos | nome | nome, grupoPai |
| unidades_produtos | nome | nome, sigla, padrao |
| clientes | cpfCnpj | All fields (20+ fields) |
| fornecedores | cpfCnpj | All fields (20+ fields) |
| produtos_fornecedores | - | Relationship table |

## Usage

### Automatic Sync

No action needed! Products automatically sync when:
- Creating a product via API
- Updating a product via API

### Manual Sync

#### Sync a specific product:

```bash
php artisan products:sync flexmol --product-id=123
```

```bash
php artisan products:sync metalflex --product-id=456
```

#### Sync all products:

```bash
php artisan products:sync flexmol --all
```

```bash
php artisan products:sync metalflex --all
```

### Command Options

| Argument/Option | Description | Required |
|-----------------|-------------|----------|
| `source` | Source database (flexmol or metalflex) | Yes |
| `--product-id=ID` | Sync a specific product | One required |
| `--all` | Sync all products | One required |

## Database Configuration

Connections are configured in `config/database.php`:

```php
'flexmol' => [
    'driver' => 'mysql',
    'host' => '127.0.0.1',
    'port' => '3306',
    'database' => 'allmac88_flexmol',
    'username' => 'allmac88',
    'password' => '88121747Ma1@',
    // ...
],

'metalflex' => [
    'driver' => 'mysql',
    'host' => '127.0.0.1',
    'port' => '3306',
    'database' => 'allmac88_metalflex',
    'username' => 'allmac88',
    'password' => '88121747Ma1@',
    // ...
],
```

## What Gets Synced

### Product Fields (29 fields)
- nome, codigoInterno, grupo_produto_id, unidade_produto_id, cliente_id
- movimentaEstoque, habilitaNotaFiscal, codigoBarras
- peso, largura, altura, comprimento, comissao
- descricao, valorCusto, despesasAdicionais, outrasDespesas, custoFinal
- estoqueMinimo, estoqueMaximo, quantidadeAtual
- ncm, cest, cfop, pesoLiquido, pesoBruto
- numeroFci, valorAproxTribut, valorPixoPis, valorFixoPisSt
- valorFixoCofins, valorFixoCofinsSt

### What Doesn't Get Synced
- ❌ Photos (fotoPrincipal, foto_produto table)
- ❌ Attachments
- ❌ Stock movements (estoques table)

## Error Handling

- Sync errors are logged to Laravel logs
- API requests never fail due to sync errors
- Use logs to monitor sync health:

```bash
tail -f storage/logs/laravel.log | grep "Product sync"
```

## Conflict Resolution

When a product exists in both databases:

1. Compare `updated_at` timestamps
2. If source is newer → Update target
3. If target is newer → Skip update
4. Logs action taken

## Example Scenarios

### Scenario 1: New Product in FlexMol
```
1. User creates product in FlexMol via API
2. Product saved to allmac88_flexmol
3. Sync triggered automatically
4. Related entities checked/created in allmac88_metalflex
5. Product created in allmac88_metalflex
6. Success response to user
```

### Scenario 2: Update Product in MetalFlex
```
1. User updates product in MetalFlex via API
2. Product updated in allmac88_metalflex
3. Sync triggered automatically
4. Product found in allmac88_flexmol by codigoInterno
5. Timestamps compared
6. Product updated in allmac88_flexmol (if newer)
7. Success response to user
```

### Scenario 3: Initial Full Sync
```bash
# Sync all products from flexmol to metalflex
php artisan products:sync flexmol --all

# Output:
# Sync completed!
# +------------------+-------+
# | Metric           | Count |
# +------------------+-------+
# | Total Products   | 150   |
# | Synced          | 148   |
# | Skipped         | 0     |
# | Errors          | 2     |
# +------------------+-------+
```

## Troubleshooting

### Check if sync is working:

```bash
# Monitor logs in real-time
tail -f storage/logs/laravel.log

# Search for sync events
grep "Product sync" storage/logs/laravel.log
```

### Common Issues:

**Issue**: Product not syncing
- Check current database name matches 'allmac88_flexmol' or 'allmac88_metalflex'
- Verify database connections in config/database.php
- Check logs for errors

**Issue**: Related entity not found
- Ensure entity exists in source database
- Check match fields (nome for grupos/unidades, cpfCnpj for clientes/fornecedores)

**Issue**: Duplicate key errors
- Product with same codigoInterno already exists
- Check for data conflicts

## Monitoring

### Key Metrics to Monitor:

1. **Sync Success Rate**: Count successful vs failed syncs
2. **Sync Latency**: Time taken to sync a product
3. **Error Rate**: Number of sync errors per day
4. **Data Consistency**: Products count in both databases

### Log Messages:

```
✓ "Updated product {codigoInterno} in {database}"
✓ "Created new product {codigoInterno} in {database}"
✓ "Product {codigoInterno} in {database} is newer, skipping update"
✓ "Product {productId} synced from {source} to other database"

✗ "Product sync failed for product {productId}: {error}"
✗ "Failed to sync product {productId}: {error}"
```

## Future Enhancements

Potential improvements for the sync system:

- [ ] Queue-based async syncing
- [ ] Retry mechanism for failed syncs
- [ ] Sync status dashboard
- [ ] Webhook notifications for sync events
- [ ] Batch sync optimization
- [ ] Photo synchronization
- [ ] Stock movement sync
- [ ] Conflict resolution UI

## Support

For issues or questions about the sync system, check:
1. Laravel logs: `storage/logs/laravel.log`
2. Database connections: `config/database.php`
3. Sync service: `app/Services/ProductSyncService.php`
