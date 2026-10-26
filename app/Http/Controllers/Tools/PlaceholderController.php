<?php

namespace App\Http\Controllers\Tools;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class PlaceholderController extends Controller
{
    /**
     * Écran principal de l'outil SVG Placeholder (constructeur interactif).
     */
    public function index(): Response
    {
        return Inertia::render('tools/placeholder/index');
    }
}
