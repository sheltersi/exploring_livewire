<?php

use App\Http\Controllers\PostController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', [PostController::class, 'index'])->name('dashboard');
    Route::get('/post/create', function () {
        return view('pages::post.create');
    })->name('post.create');
    Route::livewire('/post/{id}/edit', 'pages::post.edit')->name('post.edit');
    Route::delete('/post/{id}', [PostController::class, 'destroy'])->name('post.destroy');
});
