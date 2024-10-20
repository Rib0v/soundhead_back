<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttributeResource extends JsonResource
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
            'vals' => $this->getValues(),
        ];
    }

    protected function getValues(): array
    {
        $values = [];

        foreach ($this->values as $value) {
            $values[] = [
                'id' => $value->id,
                'name' => $value->name,
            ];
        }

        return $values;
    }
}
