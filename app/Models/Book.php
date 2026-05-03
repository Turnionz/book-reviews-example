<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Book extends Model
{
    use HasFactory;

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }


    /**
     * Local query scope for getting books with $title in their respective title
     *
     * @param Builder $query
     * @param string $title
     * @return Builder
     */
    public function scopeTitle(Builder $query, string $title): Builder
    {
        return $query->where('title', 'LIKE', '%' . $title . '%');
    }

    /**
     * Local query scope for getting most popular books judged by amount of reviews
     * 
     * @param Builder $query
     * @return Builder
     */
    public function scopePopular(Builder $query)
    {
        return $query->withCount('reviews')->orderBy('reviews_count', 'desc');
    }

    /**
     * Local query scope for getting highest rated book
     * 
     * @param Builder $query
     * @return Builder
     */
    public function scopeHighestRated(Builder $query)
    {
        return $query->withAvg('reviews', 'rating')->orderBy('reviews_avg_rating', 'desc');
    }
}
