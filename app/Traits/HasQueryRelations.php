<?php

namespace App\Traits;

use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

trait HasQueryRelations
{
    private array $queryRelations;

    /**
     * Get the query relations.
     *
     * @return array
     */
    public function getQueryRelations(): array
    {
        return $this->queryRelations;
    }

    /**
     * Set the query relations.
     *
     * @param array
     * @return void
     */
    public function setQueryRelations(array $queryRelations): void
    {
        $this->queryRelations = $queryRelations;
    }

    /**
     * Load relations based on query.
     * 
     * @param array $params
     * @param Model|LengthAwarePaginator|Paginator $data
     * @return Model|LengthAwarePaginator|Paginator $data
     */
    public function loadQueryRelations(
        array $params,
        Model | LengthAwarePaginator | Paginator $data
    ): Model | LengthAwarePaginator | Paginator {
        foreach ($this->queryRelations as $query => $relation) {
            if (array_key_exists($query, $params) && (int)$params[$query] === 1) {
                $data->load($relation);
            }
        }

        return $data;
    }
}