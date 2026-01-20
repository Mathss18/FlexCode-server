# 🚀 Quick Start Guide - Product Sync

## Setup Complete! ✅

Your bidirectional product synchronization system is ready to use.

---

## ⚡ Quick Test (5 minutes)

### Step 1: Verify Setup
```bash
cd /home/matheus/projects/FlexCode-server
php artisan tinker < VERIFY_SYNC_SETUP.php
```

Expected: All tests should pass ✓

### Step 2: Test Manual Sync (Optional)
```bash
# Sync a single product from flexmol to metalflex
php artisan products:sync flexmol --product-id=1

# Or sync all products
php artisan products:sync flexmol --all
```

### Step 3: Test Automatic Sync
Create or update a product via your API, then check the logs:

```bash
tail -f storage/logs/laravel.log | grep "Product sync"
```

You should see something like:
```
[2026-01-20 ...] Product 123 synced from flexmol to other database
```

---

## 📋 How It Works

### Automatic Sync (Real-time)
When you create or update a product via API:

```
POST /produto    →  Product saved to FlexMol  →  Automatically synced to MetalFlex ✓
PUT  /produto    →  Product updated in MetalFlex  →  Automatically synced to FlexMol ✓
```

### Manual Sync (On-demand)
Use these commands when you need to manually sync:

```bash
# Sync specific product
php artisan products:sync flexmol --product-id=123
php artisan products:sync metalflex --product-id=456

# Sync all products (useful for initial setup)
php artisan products:sync flexmol --all
php artisan products:sync metalflex --all
```

---

## 🎯 What Gets Synced

| Item | Synced? | Match Field |
|------|---------|-------------|
| Product data (29 fields) | ✅ Yes | codigoInterno |
| grupos_produtos | ✅ Yes | nome |
| unidades_produtos | ✅ Yes | nome |
| clientes | ✅ Yes | cpfCnpj |
| fornecedores | ✅ Yes | cpfCnpj |
| produtos_fornecedores | ✅ Yes | - |
| Photos/Attachments | ❌ No | - |

---

## 🔍 Monitoring

### Watch Sync Activity
```bash
# Real-time monitoring
tail -f storage/logs/laravel.log | grep "Product sync"

# Count syncs today
grep "synced from" storage/logs/laravel.log | grep "$(date +%Y-%m-%d)" | wc -l

# Check for errors
grep "Product sync failed" storage/logs/laravel.log
```

### Verify Data Consistency
```bash
# Count products in each database
mysql -u allmac88 -p'88121747Ma1@' -e "SELECT COUNT(*) AS flexmol_products FROM allmac88_flexmol.produtos;"
mysql -u allmac88 -p'88121747Ma1@' -e "SELECT COUNT(*) AS metalflex_products FROM allmac88_metalflex.produtos;"
```

---

## ⚙️ Configuration

Database connections are configured in [`config/database.php`](config/database.php):

```php
'flexmol' => [
    'database' => 'allmac88_flexmol',
    // ...
],

'metalflex' => [
    'database' => 'allmac88_metalflex',
    // ...
],
```

---

## 🆘 Troubleshooting

### Issue: Product not syncing

**Check:**
1. Is the database name correct? (`allmac88_flexmol` or `allmac88_metalflex`)
2. Are database connections working? Run: `php artisan tinker < VERIFY_SYNC_SETUP.php`
3. Check logs: `grep "Product sync" storage/logs/laravel.log`

**Fix:**
- Verify database credentials in `config/database.php`
- Ensure product has unique `codigoInterno`

### Issue: Related entity not found

**Check:**
1. Does the entity exist in source database?
2. Check the match field values (nome, cpfCnpj)

**Fix:**
- Ensure grupos_produtos.nome is consistent
- Ensure clientes/fornecedores have cpfCnpj set

### Issue: Sync errors in logs

**Check:**
```bash
grep "Product sync failed" storage/logs/laravel.log | tail -20
```

**Common causes:**
- Duplicate codigoInterno
- Missing required fields
- Database connection timeout

---

## 📚 Documentation

Full documentation available in:

- **[PRODUCT_SYNC_README.md](PRODUCT_SYNC_README.md)** - Complete guide
- **[PRODUCT_SYNC_EXAMPLES.php](PRODUCT_SYNC_EXAMPLES.php)** - Code examples
- **[IMPLEMENTATION_SUMMARY.md](IMPLEMENTATION_SUMMARY.md)** - Implementation details
- **[VERIFY_SYNC_SETUP.php](VERIFY_SYNC_SETUP.php)** - Setup verification

---

## 🎉 You're Ready!

Your product sync system is now active. Products will automatically sync between FlexMol and MetalFlex databases.

**Need help?** Check the logs first:
```bash
tail -f storage/logs/laravel.log | grep "Product sync"
```

---

## 📞 Support Checklist

Before asking for help, please provide:

1. ✅ Output of verification script
   ```bash
   php artisan tinker < VERIFY_SYNC_SETUP.php
   ```

2. ✅ Recent log entries
   ```bash
   grep "Product sync" storage/logs/laravel.log | tail -50
   ```

3. ✅ Product counts in both databases
   ```bash
   php artisan tinker
   >>> DB::connection('flexmol')->table('produtos')->count();
   >>> DB::connection('metalflex')->table('produtos')->count();
   ```

4. ✅ Specific error messages (if any)

---

**That's it! Happy syncing! 🚀**
