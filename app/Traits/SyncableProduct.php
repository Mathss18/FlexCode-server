<?php

namespace App\Traits;

use App\Services\ProductSyncService;
use Illuminate\Support\Facades\Log;
use Exception;

trait SyncableProduct
{
    /**
     * Trigger product sync after create
     */
    public static function bootSyncableProduct()
    {
        // After a product is created, sync it
        static::created(function ($product) {
            static::triggerSync($product);
        });

        // After a product is updated, sync it
        static::updated(function ($product) {
            static::triggerSync($product);
        });
    }

    /**
     * Trigger the product sync
     */
    protected static function triggerSync($product)
    {
        try {
            $syncService = new ProductSyncService();
            $currentDb = $syncService->getCurrentDatabaseName();

            if ($currentDb) {
                $syncService->syncProduct($product->id, $currentDb);
            }
        } catch (Exception $e) {
            // Log error but don't fail the request
            Log::error("Product sync failed for product {$product->id}: " . $e->getMessage());
        }
    }
}
