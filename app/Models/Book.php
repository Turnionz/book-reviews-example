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
     * Local query scope for getting most popular books judged by amount of reviews in datetime range of $from to $to
     * 
     * @param Builder $query
     * @param DateTime $from
     * @param DateTime $to
     * @return Builder
     */
    public function scopePopular(Builder $query, $from = null, $to = null)
    {
        return $query->withReviewCount()
            ->orderBy('reviews_count', 'desc');
    }

    /**
     * Local query scope for getting highest rated book in datetime range of $from to $to
     * 
     * @param Builder $query
     * @param DateTime $from
     * @param DateTime $to
     * @return Builder
     */
    public function scopeHighestRated(Builder $query, $from = null, $to = null)
    {
        return $query->withReviewCount()
            ->withAvgRating()
            ->orderBy('reviews_avg_rating', 'desc');
    }

    public function scopeWithReviewCount(Builder $query, $from = null, $to = null)
    {
        return $query->withCount(['reviews' => fn(Builder $q) => $this->dateRangeFilter($q, $from, $to)]);
    }

    public function scopeWithAvgRating(Builder $query, $from = null, $to = null)
    {
        return $query->withAvg(['reviews' => fn(Builder $q) => $this->dateRangeFilter($q, $from, $to)], 'rating');
    }

    public function scopeMinReviews(Builder $query, int $minReviews)
    {
        return $query->having('reviews_count', '>=', $minReviews);
    }

    private function dateRangeFilter(Builder $query, $from = null, $to = null)
    {
        if ($from && !$to) {
            $query->where('created_at', '>=', $from);
        } elseif (!$from && $to) {
            $query->where('created_at', '<=', $to);
        } elseif ($from && $to) {
            $query->whereBetween('created_at', [$from, $to]);
        }
    }

    public function scopePopularLastMonth(Builder $query)
    {
        return $query->popular(now()->subMonth(), now())->minReviews(2);
    }

    public function scopePopularLastSixMonths(Builder $query)
    {
        return $query->popular(now()->subMonths(6), now())->minReviews(5);
    }

    public function scopeHighestRatedLastMonth(Builder $query)
    {
        return $query->highestRated(now()->subMonth(), now())->minReviews(2);
    }

    public function scopeHighestRatedLastSixMonths(Builder $query)
    {
        return $query->highestRated(now()->subMonths(6), now())->minReviews(5);
    }

    protected static function booted()
    {
        static::updated(fn(Book $book) => cache()->forget('book:' . $book->id));
        static::deleted(fn(Book $book) => cache()->forget('book:' . $book->id));
    }
}
