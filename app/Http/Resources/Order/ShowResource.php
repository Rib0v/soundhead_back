<?php

namespace App\Http\Resources\Order;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShowResource extends JsonResource
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
            'total' => $this->total,
            'name' => $this->name,
            'phone' => $this->phone,
            'email' => $this->email,
            'address' => $this->address,
            'comment' => $this->comment,
            'status' => $this->status->name,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'products' => $this->getProducts(),
        ];
    }

    private function getProducts(): array
    {
        $products = [];

        foreach ($this->orderProducts as $product) {
            $products[] = [
                'id' => $product->id,
                'name' => $product->product->name,
                'slug' => $product->product->slug,
                'image' => getBaseUrl() . '/storage/photos/products' . $product->product->image,
                'count' => $product->count,
                'price' => $product->price,
            ];
        }

        return $products;
    }
}
