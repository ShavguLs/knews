<?php

use App\Http\Controllers\Api\NewsApiController;
use Illuminate\Support\Facades\Route;

Route::get('/news', [NewsApiController::class, 'index'])->name('api.news.index');

