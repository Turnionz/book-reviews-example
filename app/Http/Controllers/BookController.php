<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\Request;

class BookController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $title = $request->input('title');
        $filter = $request->input('filter', '');
        $page = $request->input('page', 1);

        $query = Book::when(
            $title,
            fn($query, $title) =>
            $query->title($title)
        );

        $query = match ($filter) {
            'popular_last_month' => $query->popularLastMonth(),
            'popular_last_6months' => $query->popularLastSixMonths(),
            'highest_rated_last_month' => $query->highestRatedLastMonth(),
            'highest_rated_last_6months' => $query->highestRatedLastSixMonths(),
            default => $query->orderBy('created_at', 'desc')->withAvgRating()->withReviewCount()
        };

        $cacheKey = 'books:' . $filter . ':' . $title . ':page:' . $page;
        $books = cache()->remember($cacheKey, 3600, fn() => $query->paginate());

        return view('books.index', ['books' => $books]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id)
    {
        $cacheKey = 'book:' . $id;

        $book = cache()->remember(
            $cacheKey,
            3600,
            fn() => Book::with([
                'reviews' => fn($query) => $query->latest()
            ])->withAvgRating()->withReviewCount()->findOrFail($id)
        );

        return view('books.show', ['book' => $book]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
