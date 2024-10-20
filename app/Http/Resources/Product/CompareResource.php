<?php

namespace App\Http\Resources\Product;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CompareResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'price' => $this->price,
            'image' => getBaseUrl() . '/storage/photos/products' . $this->image,
            'description' => $this->description,
            'attributes' => $this->getAttributes()
        ];
    }

    private function getAttributes()
    {
        $options = [];

        foreach ($this->values as $value) {
            $options[$value->attribute->slug] = [
                'name' => $value->attribute->name,
                'value' => $value->name
            ];
        }

        return $options;
    }
}
