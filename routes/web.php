<?php

use App\Http\Controllers\BookController;
use App\Models\Book;
use App\Models\Review;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $book = Book::popular()->minReviews(10)->get();
    dd($book);
});

Route::resource('books', BookController::class);
