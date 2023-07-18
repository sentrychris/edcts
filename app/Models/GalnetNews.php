<?php

namespace App\Models;

use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GalnetNews extends Model
{
    use HasFactory, SoftDeletes, Sluggable;

    protected $table = 'galnet_news';

    protected $guarded = [];

    public $timestamps = false;

    /**
     * Boot model
     */
    protected static function boot(): void
    {
        parent::boot();
        static::addGlobalScope('order', function(Builder $builder) {
            $builder->orderBy('id', 'asc');
        });
    }

    /**
     * Configure slug
     */
    public function sluggable(): array
    {
        return [
            'title' => [
                'source' => 'title',
                'separator' => '-'
            ]
        ];
    }
}
