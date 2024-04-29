<?php

namespace App\Models;

use App\Utilities\Utils;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

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

    // public function productValues()
    // {
    //     return $this->hasMany(ProductValue::class);
    // }

    public function photos(): HasMany
    {
        return $this->hasMany(Photo::class);
    }

    public function values()
    {
        return $this->belongsToMany(Value::class, 'product_values');
    }

    public function scopeFiltered(Builder $query)
    {
        $query->whereIn('products.id', [1, 2]);
    }

    public function scopeFilterOptions(Builder $query, $request)
    {
        // TODO Категории меняются редко, нужно будет закешировать
        $whitelist = Attribute::pluck('slug')->toArray();

        $allowedOptions = Utils::strParamsToArr($request->only($whitelist));

        foreach ($allowedOptions as $value) {

            $query->whereHas('values', function ($query) use ($value) {
                $query->whereIn('values.id', $value);
            });
        }
    }

    public function scopeFilterRanges(Builder $query, $request)
    {
        $applyRange = function (string $slug, string $columnName) use ($query, $request): void {
            if ($request->has("min$slug")) {
                $query->where($columnName, '>=', $request->query("min$slug"));
            }
            if ($request->has("max$slug")) {
                $query->where($columnName, '<=', $request->query("max$slug"));
            }
        };

        $applyRange('price', 'price');
        $applyRange('sens', 'sensitivity');
        $applyRange('minfreq', 'min_frequency');
        $applyRange('maxfreq', 'max_frequency');
    }


    public function scopeSort(Builder $query, $request)
    {
        if ($request->has('sort')) {
            switch ($request->query('sort')) {
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
}
