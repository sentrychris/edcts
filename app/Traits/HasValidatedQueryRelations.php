<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

trait HasValidatedQueryRelations
{
    private array $allowedRelations;

    /**
     * Get the allowed relations.
     *
     * @return array
     */
    public function getAllowedRelations(): array
    {
        return $this->allowedRelations;
    }

    /**
     * Set the allowed relations.
     *
     * @param array
     * @return void
     */
    public function setAllowedRelations(array $allowedRelations): void
    {
        $this->allowedRelations = $allowedRelations;
    }

    /**
     * Load validated relations based on query.
     * 
     * @param array $validated
     * @param Model|LengthAwarePaginator $data
     * 
     * @return Model|LengthAwarePaginator $data
     */
    public function loadValidatedRelations(array $validated, Model | LengthAwarePaginator $data): Model | LengthAwarePaginator
    {
        foreach ($this->allowedRelations as $query => $relation) {
            if (array_key_exists($query, $validated) && (int)$validated[$query] === 1) {
                $data->load($relation);
            }
        }

        return $data;
    }
}