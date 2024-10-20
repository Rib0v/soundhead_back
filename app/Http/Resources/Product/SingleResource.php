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
        return $this->getArray();
    }

    public function getArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'price' => $this->price,
            'image' => getBaseUrl() . '/storage/photos/products' . $this->image,
            'description' => $this->description,
            'attributes' => $this->getAttributes(),
            'photos' => $this->getPhotos(),
        ];
    }

    private function getAttributes(): array
    {
        $options = [];

        foreach ($this->values as $value) {
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

    private function getPhotos(): array
    {
        return array_map(fn($photo) => getBaseUrl() . '/storage/photos/products' . $photo['url'], $this->photos->toArray());
    }
}
