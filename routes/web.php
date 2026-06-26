<?php

use App\Http\Controllers\AdminLoginController;
use App\Http\Controllers\AirQualityController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookmarkController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\CryptoController;
use App\Http\Controllers\CurrencyController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\SparController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReactionController;
use App\Http\Controllers\WeatherController;
use Illuminate\Support\Facades\Route;

Route::get('/', [NewsController::class, 'index'])->name('news.index');
Route::get('/news', [NewsController::class, 'index']);
Route::get('/weather', [WeatherController::class, 'index'])->name('weather.index');
Route::get('/crypto', [CryptoController::class, 'index'])->name('crypto.index');
Route::get('/currency', [CurrencyController::class, 'index'])->name('currency.index');
Route::get('/air', [AirQualityController::class, 'index'])->name('air.index');
Route::get('/spar', [SparController::class, 'index'])->name('spar.index');
Route::get('/u/{user}', [ProfileController::class, 'show'])->name('profile.show');
Route::get('/news/{news}', [NewsController::class, 'show'])->name('news.show');

Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.store');
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.store');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {
    Route::post('/news/{news}/comments', [CommentController::class, 'store'])->name('comments.store');
    Route::delete('/comments/{comment}', [CommentController::class, 'destroy'])->name('comments.destroy');
    Route::post('/news/{news}/reactions', [ReactionController::class, 'store'])->name('reactions.store');
    Route::get('/bookmarks', [BookmarkController::class, 'index'])->name('bookmarks.index');
    Route::post('/news/{news}/bookmark', [BookmarkController::class, 'toggle'])->name('bookmarks.toggle');
});

Route::get('/admin/login', [AdminLoginController::class, 'showLoginForm'])->name('admin.login');
Route::post('/admin/login', [AdminLoginController::class, 'login'])->name('admin.login.store');
Route::post('/admin/logout', [AdminLoginController::class, 'logout'])->name('admin.logout');

Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
    Route::resource('news', NewsController::class)->except(['show']);
});