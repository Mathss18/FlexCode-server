# Product Sync System Architecture

## System Overview

```
┌─────────────────────────────────────────────────────────────────────┐
│                    PRODUCT SYNC SYSTEM                               │
│                   FlexMol ⟷ MetalFlex                               │
└─────────────────────────────────────────────────────────────────────┘

┌──────────────────┐                              ┌──────────────────┐
│   FlexMol DB     │                              │  MetalFlex DB    │
│                  │◄─────── SYNC ──────────────►│                  │
│ allmac88_flexmol │     Bidirectional            │allmac88_metalflex│
└──────────────────┘                              └──────────────────┘
```

## Request Flow - Automatic Sync

```
┌─────────┐
│  User   │
└────┬────┘
     │
     │ POST/PUT /produto
     ▼
┌────────────────────┐
│   API Request      │
│  (Create/Update)   │
└─────────┬──────────┘
          │
          ▼
┌─────────────────────────┐
│  ProdutoController      │
│  - Validate request     │
│  - Save to current DB   │
│  - Commit transaction   │
└─────────┬───────────────┘
          │
          │ ✓ Success
          ▼
┌─────────────────────────────────────────────────┐
│  syncProductToOtherDatabase($productId)         │
│  - Detect current DB (flexmol/metalflex)       │
│  - Call ProductSyncService                      │
│  - Non-blocking (doesn't fail on error)        │
└─────────┬───────────────────────────────────────┘
          │
          ▼
┌─────────────────────────────────────────────────┐
│  ProductSyncService::syncProduct()              │
│  1. Get product from source DB                  │
│  2. Check if exists in target by codigoInterno  │
│  3. Sync related entities                       │
│  4. Create/Update product in target             │
│  5. Sync fornecedores relationships             │
│  6. Log result                                  │
└─────────┬───────────────────────────────────────┘
          │
          ▼
┌─────────────────────────┐
│  Response to User       │
│  200 OK (success)       │
│  Product synced!        │
└─────────────────────────┘
```

## Sync Logic Flowchart

```
                   ┌──────────────────────┐
                   │  syncProduct($id)    │
                   └──────────┬───────────┘
                              │
                              ▼
              ┌───────────────────────────────┐
              │ Get product from source DB    │
              └───────────────┬───────────────┘
                              │
                              ▼
              ┌───────────────────────────────┐
              │ Sync Related Entities:        │
              │ - grupo_produto (by nome)     │
              │ - unidade_produto (by nome)   │
              │ - cliente (by cpfCnpj)        │
              │ - fornecedor (by cpfCnpj)     │
              └───────────────┬───────────────┘
                              │
                              ▼
         ┌────────────────────────────────────────┐
         │ Product exists in target DB?           │
         │ (check by codigoInterno)               │
         └────────┬───────────────────────────────┘
                  │
          ┌───────┴───────┐
          │               │
        YES              NO
          │               │
          ▼               ▼
  ┌───────────────┐  ┌──────────────────┐
  │ Compare       │  │ CREATE new       │
  │ updated_at    │  │ product in       │
  │ timestamps    │  │ target DB        │
  └───────┬───────┘  └────────┬─────────┘
          │                   │
    ┌─────┴──────┐            │
    │            │            │
  SOURCE       TARGET         │
  NEWER        NEWER          │
    │            │            │
    ▼            ▼            │
┌─────────┐  ┌────────┐      │
│ UPDATE  │  │  SKIP  │      │
│ target  │  │ update │      │
└────┬────┘  └────┬───┘      │
     │            │           │
     └────────────┴───────────┘
                  │
                  ▼
     ┌────────────────────────┐
     │ Sync fornecedores      │
     │ relationships          │
     │ (produtos_fornecedores)│
     └────────────┬───────────┘
                  │
                  ▼
          ┌──────────────┐
          │   LOG RESULT │
          └──────────────┘
```

## Related Entity Sync

```
┌─────────────────────────────────────────────────────────────┐
│                  RELATED ENTITY SYNC                         │
└─────────────────────────────────────────────────────────────┘

For each related entity:

1. grupos_produtos
   ┌──────────────────┐
   │ Source: ID = 5   │
   │ nome = "Arame"   │
   └────────┬─────────┘
            │
            ▼
   ┌─────────────────────────┐
   │ Check target DB:        │
   │ WHERE nome = "Arame"    │
   └────────┬────────────────┘
            │
     ┌──────┴──────┐
     │             │
   EXISTS      NOT EXISTS
     │             │
     ▼             ▼
 Return ID    Create & Return ID


2. unidades_produtos
   Same logic, match by: nome

3. clientes
   Same logic, match by: cpfCnpj

4. fornecedores
   Same logic, match by: cpfCnpj

5. produtos_fornecedores
   - Delete existing relationships
   - Sync all fornecedores
   - Create new relationships
```

## Database Structure

```
┌─────────────────────────────────────────────────────────────┐
│  PRODUTOS TABLE (Main Sync Target)                          │
├─────────────────────────────────────────────────────────────┤
│ id                    - Auto increment (different per DB)   │
│ codigoInterno        - UNIQUE IDENTIFIER FOR MATCHING  ⭐   │
│ nome                  - Product name                        │
│ grupo_produto_id     - FK → grupos_produtos               │
│ unidade_produto_id   - FK → unidades_produtos             │
│ cliente_id           - FK → clientes                       │
│ ... 20+ other fields                                        │
│ created_at                                                  │
│ updated_at           - USED FOR CONFLICT RESOLUTION  ⭐    │
└─────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────┐
│  GRUPOS_PRODUTOS (Match by: nome)                           │
├─────────────────────────────────────────────────────────────┤
│ id                                                           │
│ nome                 - MATCH FIELD  ⭐                      │
│ grupoPai                                                     │
└─────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────┐
│  UNIDADES_PRODUTOS (Match by: nome)                         │
├─────────────────────────────────────────────────────────────┤
│ id                                                           │
│ nome                 - MATCH FIELD  ⭐                      │
│ sigla                                                        │
│ padrao                                                       │
└─────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────┐
│  CLIENTES (Match by: cpfCnpj)                               │
├─────────────────────────────────────────────────────────────┤
│ id                                                           │
│ nome                                                         │
│ cpfCnpj              - MATCH FIELD  ⭐                      │
│ ... 15+ other fields                                         │
└─────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────┐
│  FORNECEDORES (Match by: cpfCnpj)                           │
├─────────────────────────────────────────────────────────────┤
│ id                                                           │
│ nome                                                         │
│ cpfCnpj              - MATCH FIELD  ⭐                      │
│ ... 15+ other fields                                         │
└─────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────┐
│  PRODUTOS_FORNECEDORES (Many-to-Many)                       │
├─────────────────────────────────────────────────────────────┤
│ id                                                           │
│ produto_id           - FK → produtos                        │
│ fornecedor_id        - FK → fornecedores                    │
└─────────────────────────────────────────────────────────────┘
```

## Manual Sync Command Flow

```
┌─────────────┐
│  Terminal   │
└──────┬──────┘
       │
       │ php artisan products:sync flexmol --all
       ▼
┌──────────────────────────┐
│  SyncProducts Command    │
│  - Validate args         │
│  - Determine source/target│
└──────────┬───────────────┘
           │
           ▼
┌──────────────────────────────────────┐
│  ProductSyncService::syncAllProducts()│
│  - Get all products from source       │
│  - Loop through each product          │
│  - Call syncProduct() for each        │
│  - Track statistics                   │
└──────────┬───────────────────────────┘
           │
           ▼
┌──────────────────────────┐
│  Display Results         │
│  Total: 150              │
│  Synced: 148             │
│  Errors: 2               │
└──────────────────────────┘
```

## Conflict Resolution Logic

```
Product A exists in both databases

┌──────────────────────────┐       ┌──────────────────────────┐
│  FlexMol                 │       │  MetalFlex               │
│  codigoInterno: "ABC123" │       │  codigoInterno: "ABC123" │
│  updated_at: 2026-01-20  │       │  updated_at: 2026-01-15  │
│          14:30:00        │       │          10:00:00        │
└──────────────────────────┘       └──────────────────────────┘
           │                                    │
           │                                    │
           └────────────┬───────────────────────┘
                        │
                        ▼
              ┌──────────────────┐
              │ Compare dates    │
              └────────┬─────────┘
                       │
           FlexMol > MetalFlex
          (2026-01-20 > 2026-01-15)
                       │
                       ▼
              ┌──────────────────┐
              │ UPDATE MetalFlex │
              │ with FlexMol data│
              └──────────────────┘

Result: FlexMol data copied to MetalFlex ✓
```

## Error Handling

```
┌───────────────────────┐
│  Sync Error Occurs    │
└──────────┬────────────┘
           │
           ▼
┌──────────────────────────────┐
│  Catch Exception             │
│  - Log error message         │
│  - Don't throw/fail request  │
└──────────┬───────────────────┘
           │
           ▼
┌──────────────────────────────┐
│  Continue Normal Flow        │
│  - User gets success response│
│  - Check logs for details    │
└──────────────────────────────┘

Log Entry Example:
[2026-01-20 14:30:00] Product sync failed for product 123: 
  Duplicate entry 'ABC123' for key 'codigoInterno'
```

## Component Interaction

```
┌────────────────────────────────────────────────────────────┐
│                      COMPONENTS                             │
└────────────────────────────────────────────────────────────┘

    ┌──────────────────────┐
    │  ProdutoController   │
    │  (Entry Point)       │
    └──────────┬───────────┘
               │ uses
               ▼
    ┌──────────────────────┐
    │ ProductSyncService   │
    │ (Core Logic)         │
    └──────────┬───────────┘
               │ uses
               ▼
    ┌──────────────────────┐
    │ Database Connections │
    │ - flexmol            │
    │ - metalflex          │
    └──────────────────────┘

Manual Path:

    ┌──────────────────────┐
    │  Artisan Command     │
    │  products:sync       │
    └──────────┬───────────┘
               │ uses
               ▼
    ┌──────────────────────┐
    │ ProductSyncService   │
    │ (Core Logic)         │
    └──────────┬───────────┘
               │ uses
               ▼
    ┌──────────────────────┐
    │ Database Connections │
    └──────────────────────┘
```

---

## Key Design Decisions

| Decision | Rationale |
|----------|-----------|
| Sync triggered from controller | Direct control, easier debugging |
| Non-blocking sync | User experience not affected by sync failures |
| Match by codigoInterno | Unique identifier across databases |
| Latest wins (updated_at) | Simple conflict resolution |
| No photo sync | Avoid large file transfers |
| Sync all related entities | Maintain referential integrity |
| Log but don't fail | Resilient system, issues caught in logs |

---

**Legend:**
- ⭐ = Critical field for sync operation
- FK = Foreign Key
- ✓ = Success/Completed
- ✗ = Failed/Error
