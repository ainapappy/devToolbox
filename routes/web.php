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
    Route::post('paste', [PasteController::class, 'store'])->name('paste.store');
    Route::get('mock-api', [MockApiController::class, 'create'])->name('mock-api.index');
    Route::post('mock-api', [MockApiController::class, 'store'])->name('mock-api.store');
    Route::get('shortener', [UrlShortenerController::class, 'index'])->name('shortener.index');
    Route::get('markdown', [MarkdownController::class, 'index'])->name('markdown.index');
    Route::get('placeholder', [PlaceholderController::class, 'index'])->name('placeholder.index');
});

// Endpoint public d'un mock JSON (cache write-through à la création).
Route::get('api/mock/{slug}', [MockApiController::class, 'serve'])->name('mocks.serve');

// Lecture publique d'un paste (avertissement puis révélation).
Route::get('p/{hash}', [PasteController::class, 'show'])->name('pastes.show');
Route::post('p/{hash}/reveal', [PasteController::class, 'reveal'])->name('pastes.reveal');

require __DIR__.'/settings.php';
