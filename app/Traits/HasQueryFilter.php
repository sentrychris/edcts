<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;

trait HasQueryFilter
{
    /**
     * Builds a filter query based on model attributes.
     * 
     * @param Builder $builder The query builder
     * @param array $options The filter option 
     * @param string|array $attribute The model attribute(s) to filter
     * @return Builder $builder The query builder
     */
    public function buildFilterQuery(Builder $builder, array $options, string|array $attribute, string $operand): Builder
    {
        if (is_array($attribute)) {
            foreach ($attribute as $attr) {
                if (Arr::exists($options, $attr) && $options[$attr]) {
                    $value = explode(',', $options[$attr]);
                    if ($value) {
                        if ($operand === 'like') {
                            $builder->where($attr, 'RLIKE', $value);
                        } else {
                            $builder->whereIn($attr, $value);
                        }
                    }
                }
            }
        } else {
            if (Arr::exists($attribute, $attribute) && $options[$attribute]) {
                $value = explode(',', $options[$attribute]);
                if ($value) {
                    if ($operand === 'like') {
                        $builder->where($attribute, 'RLIKE', $value);
                    } else {
                        $builder->whereIn($attribute, $value);
                    }
                }
            }
        }
        
        return $builder;
    }
}