<?php

namespace App\Console\Commands;

use App\Http\Resources\Product\SingleResource;
use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;

class UpdateProductCache extends Command
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
    public function handle()
    {
        // TODO вынести всё в отдельный сервис
        $prefix = config('database.redis.options.prefix');
        foreach (Redis::keys('product:*') as $key) {
            $key = ltrim($key, $prefix);
            Redis::del($key);
        }
        foreach (Redis::keys('product_id:*') as $key) {
            $key = ltrim($key, $prefix);
            Redis::del($key);
        }

        Redis::del('products_first_page');

        $lastProduct = Product::max('id');

        for ($id = 1; $id <= $lastProduct; $id++) {
            $product = new SingleResource(Product::findOrFail($id));
            Redis::set("product:$id", $product);
            Redis::set("product_id:{$product->slug}", $id);
        }
    }
}
