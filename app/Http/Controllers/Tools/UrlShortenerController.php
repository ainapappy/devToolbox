<?php

namespace App\Http\Controllers\Tools;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class UrlShortenerController extends Controller
{
    /**
     * Écran principal de l'outil URL Shortener.
     */
    public function index(): Response
    {
        return Inertia::render('tools/shortener/index');
    }
}
