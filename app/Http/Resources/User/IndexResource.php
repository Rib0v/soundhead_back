<?php

namespace App\Http\Resources\User;

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
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'address' => $this->address,
            'phone' => $this->phone,
            'orders' => $this->orders->count(),
            'orders_total' => $this->getOrdersTotal(),
            'permissions' => $this->getPermissions(),
            'created_at' => $this->created_at,
        ];
    }

    private function getOrdersTotal(): int
    {
        return (int)array_reduce($this->orders->toArray(), fn($accumulator, $order) => $accumulator += $order['total']);
    }

    private function getPermissions(): array
    {
        $permissions = [];

        foreach ($this->permissionUsers as $item) {
            $permissions[] = [
                'id' => $item->permission->id,
                'name' => $item->permission->name,
                'description' => $item->permission->description,
            ];
        }
        return $permissions;
    }
}
