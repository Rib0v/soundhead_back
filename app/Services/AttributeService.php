<?php

namespace App\Services;

use App\Models\Attribute;
use Illuminate\Database\Eloquent\Collection;

class AttributeService
{
    public function getFilters(): Collection
    {
        /**
         * Сделал таким странным способом просто чтобы не забывалось,
         * как пишутся нативные запросы в SQL, и потренироваться ещё раз
         * с преобразованием массивов. А так логичнее конечно было бы
         * сделать через resource и laravel-овские отношения, как во всех
         * остальных контроллерах.
         */
        return Attribute::query()
            ->select(
                'attributes.id AS attribute_id',
                'attributes.name AS attribute',
                'attributes.slug AS slug',
                'values.id as value_id',
                'values.name AS value'
            )
            ->join('values', 'values.attribute_id', '=', 'attributes.id')
            ->get();;
    }

    /**
     * Группирует все возможные
     * значения атрибутов (values)
     * по категориям-атрибутам (attributes)
     * 
     * @param \Illuminate\Database\Eloquent\Collection $filters
     * 
     * @return array
     */
    public function formatFilters(Collection $filters): array
    {
        $formattedFilters = [];

        foreach ($filters as $value) {
            if (!isset($formattedFilters[$value->attribute_id])) {
                $formattedFilters[$value->attribute_id] = [
                    'id' => $value->attribute_id,
                    'name' => $value->attribute,
                    'slug' => $value->slug,
                    'vals' => [],
                ];
            }

            $formattedFilters[$value->attribute_id]['vals'][] = [
                'id' => $value->value_id,
                'name' => $value->value,
            ];
        }

        // преобразование ассоциативного массива в индексный
        $formattedFilters = array_values($formattedFilters);

        return $formattedFilters;
    }
}
