<?php

namespace App\Http\Resources\Product;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class IndexResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $baseUrl = config('app.env') === 'production'
            ? config('app.url')
            : config('app.url') . ':' . config('app.port');

        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'price' => $this->price,
            'image' => $baseUrl . '/storage/photos/products' . $this->image,
            'description' => $this->description,
            'brand' => $this->values[0]->name, // TODO переделать, сделать хелперы и фильтровать по attribute_id
            'form' => $this->values[1]->name,
        ];
    }
}
