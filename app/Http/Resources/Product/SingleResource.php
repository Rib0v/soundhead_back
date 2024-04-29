<?php

namespace App\Http\Resources\Product;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SingleResource extends JsonResource
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
            'attributes' => $this->getAttributes(),
            'photos' => $this->getPhotos($baseUrl),
        ];
    }

    private function getAttributes(): array
    {
        $options = [];

        foreach ($this->values as $value) {
            // $options[$value->attribute->name] = $value->name;
            $options[] = [
                'attribute_id' => $value->attribute->id,
                'attribute_name' => $value->attribute->name,
                'attribute_slug' => $value->attribute->slug,
                'value_id' => $value->id,
                'value_name' => $value->name,
            ];
        }

        return $options;
    }

    private function getPhotos(string $baseUrl): array
    {
        return array_map(fn ($photo) => $baseUrl . '/storage/photos/products' . $photo['url'], $this->photos->toArray());
    }
}
