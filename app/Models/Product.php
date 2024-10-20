<?php

namespace App\Models;

use App\Services\Cache\CacheService;
use App\Services\ProductService;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Redis;

class Product extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'price',
        'description',
        'min_frequency',
        'max_frequency',
        'sensitivity',
        'image',
    ];

    // ============================== EVENTS ==============================

    protected static function booted(): void
    {
        static::saved(function (Product $product) {
            app(ProductService::class)->reCacheProduct($product);
        });
    }

    // ============================== RELATIONS ==============================

    public function photos(): HasMany
    {
        return $this->hasMany(Photo::class);
    }

    public function values(): BelongsToMany
    {
        return $this->belongsToMany(Value::class, 'product_values');
    }

    // ============================== SCOPES ==============================

    /** @param  Builder|\Illuminate\Database\Query\Builder $query */
    public function scopeFilterOptions(Builder $query, $request)
    {
        $whitelist = $this->getProductAttributesWhiteList();
        $allowedOptions = strParamsToArr($request->only($whitelist));

        foreach ($allowedOptions as $value) {
            $query->whereHas('values', function ($query) use ($value) {
                $query->whereIn('values.id', $value);
            });
        }
    }

    protected function getProductAttributesWhiteList(): array
    {
        /** @var CacheService $cache */
        $cache = app(CacheService::class);

        return $cache->cacheAndGet('product_attributes', fn() => Attribute::pluck('slug')->toArray());
    }

    /** @param  Builder|\Illuminate\Database\Query\Builder $query */
    public function scopeFilterRanges(Builder $query, $request)
    {
        $filters = [
            ['price', 'price'],
            ['sens', 'sensitivity'],
            ['minfreq', 'min_frequency'],
            ['maxfreq', 'max_frequency'],
        ];

        foreach ($filters as $filter) {
            [$slug, $columnName] = $filter;

            if ($request->has("min$slug")) {
                $query->where($columnName, '>=', $request->query("min$slug"));
            }

            if ($request->has("max$slug")) {
                $query->where($columnName, '<=', $request->query("max$slug"));
            }
        }
    }


    /** @param  Builder|\Illuminate\Database\Query\Builder $query */
    public function scopeSort(Builder $query, $request)
    {
        switch ($request->query('sort')) {
            case null:
                break;
            case 'lowprice':
                $query->orderBy('price');
                break;
            case 'hiprice':
                $query->orderByDesc('price');
                break;
            case 'older':
                $query->oldest();
                break;
            case 'newer':
                $query->latest();
                break;
        }
    }
}
