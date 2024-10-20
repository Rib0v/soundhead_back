<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Services\Cache\CacheService;
use App\Services\ProductService;
use Illuminate\Console\Command;

class UpdateProductCacheCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'product:recache';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update products cache';

    /**
     * Execute the console command.
     */
    public function handle(CacheService $cacheService, ProductService $productService)
    {
        if (!config('cache.enabled')) {
            $this->warn('Caching is disabled in config. Please change settings and try again.');
            return 1;
        }

        $this->clearProductCache($productService);
        $this->cacheSingleProductPages($productService);
        $this->cacheProductListPages($productService);
    }

    protected function clearProductCache(ProductService $productService): void
    {
        $count = $productService->clearProductCache();

        $this->info("Removed $count records from cache");
    }

    protected function cacheSingleProductPages(ProductService $productService): void
    {
        $count = $productService->cacheSingleProductPages(
            firstId: 1,
            lastId: Product::max('id')
        );

        $this->info("Cached $count single product pages");
    }

    protected function cacheProductListPages(ProductService $productService): void
    {
        $perPageDefault = config('app.products_per_page_default');

        $count = $productService->cacheProductListPages(
            firstPage: 1,
            lastPage: Product::paginate($perPageDefault)->lastPage(),
        );

        $this->info("Cached $count product list pages");
    }
}
