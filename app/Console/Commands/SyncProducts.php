<?php

namespace App\Console\Commands;

use App\Services\ProductSyncService;
use Illuminate\Console\Command;
use Exception;

class SyncProducts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'products:sync
                            {source : Source database (flexmol or metalflex)}
                            {--product-id= : Sync a specific product by ID}
                            {--all : Sync all products}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync products between flexmol and metalflex databases';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $source = $this->argument('source');
        $productId = $this->option('product-id');
        $syncAll = $this->option('all');

        // Validate source database
        if (!in_array($source, ['flexmol', 'metalflex'])) {
            $this->error('Invalid source database. Use "flexmol" or "metalflex"');
            return 1;
        }

        $syncService = new ProductSyncService();
        $target = $source === 'flexmol' ? 'metalflex' : 'flexmol';

        try {
            if ($productId) {
                // Sync specific product
                $this->info("Syncing product ID {$productId} from {$source} to {$target}...");
                $syncService->syncProduct($productId, $source);
                $this->info("✓ Product synced successfully!");
                return 0;

            } elseif ($syncAll) {
                // Sync all products
                $this->info("Syncing all products from {$source} to {$target}...");

                $result = $syncService->syncAllProducts($source);

                $this->info("Sync completed!");
                $this->table(
                    ['Metric', 'Count'],
                    [
                        ['Total Products', $result['total']],
                        ['Synced', $result['synced']],
                        ['Skipped', $result['skipped']],
                        ['Errors', $result['errors']],
                    ]
                );

                return $result['errors'] > 0 ? 1 : 0;

            } else {
                $this->error('Please specify either --product-id or --all');
                return 1;
            }

        } catch (Exception $e) {
            $this->error("Sync failed: " . $e->getMessage());
            return 1;
        }
    }
}
