<?php

namespace App\Traits;

use App\Models\SystemBody;
use App\Models\SystemInformation;
use App\Models\SystemStation;
use App\Services\EdsmApiService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

trait HasValidatedRelations
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

    /**
     * Load validated relations based on query.
     * 
     * TODO refactor this
     * 
     * @param array $validated
     * @param Model|LengthAwarePaginator $data
     * 
     * @return Model|LengthAwarePaginator $data
     */
    public function loadValidatedRelationsForSystem(array $validated, Model | LengthAwarePaginator $model): Model|LengthAwarePaginator
    {
        $allowed = [
            'withInformation' => 'information',
            'withBodies' => 'bodies',
            'withStations' => 'stations',
            'withDepartures' => 'departures.destination',
            'withArrivals' => 'arrivals.departure'
        ];

        foreach ($allowed as $query => $relation) {
            if (array_key_exists($query, $validated) && (int)$validated[$query] === 1) {
                if ($model instanceof Model && $relation === 'bodies') {
                    SystemBody::retrieveBy($model);
                }

                if ($model instanceof Model && $relation === 'information') {
                    SystemInformation::retrieveBy($model);
                }

                if ($model instanceof Model && $relation === 'stations') {
                    SystemStation::retrieveBy($model);
                }

                $model->load($relation);
            }
        }

        return $model;
    }
}