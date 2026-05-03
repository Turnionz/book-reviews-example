<?php

use App\Models\Book;
use App\Models\Review;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $book = Book::popular()->limit(5)->get();
    dd($book);
});
