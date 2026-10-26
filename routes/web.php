<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Tools\MarkdownController;
use App\Http\Controllers\Tools\MockApiController;
use App\Http\Controllers\Tools\PasteController;
use App\Http\Controllers\Tools\PlaceholderController;
use App\Http\Controllers\Tools\UrlShortenerController;
use Illuminate\Support\Facades\Route;

// Front-office public : dashboard + navigation des outils.
Route::get('/', DashboardController::class)->name('home');
Route::get('/dashboard', DashboardController::class)->name('dashboard');

Route::prefix('tools')->name('tools.')->group(function () {
    Route::get('paste', [PasteController::class, 'create'])->name('paste.index');
    Route::get('mock-api', [MockApiController::class, 'create'])->name('mock-api.index');
    Route::get('shortener', [UrlShortenerController::class, 'index'])->name('shortener.index');
    Route::get('markdown', [MarkdownController::class, 'index'])->name('markdown.index');
    Route::get('placeholder', [PlaceholderController::class, 'index'])->name('placeholder.index');
});

require __DIR__.'/settings.php';
