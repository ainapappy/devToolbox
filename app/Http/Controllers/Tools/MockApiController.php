<?php

namespace App\Http\Controllers\Tools;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class MockApiController extends Controller
{
    /**
     * Écran principal de l'outil JSON Mock API.
     */
    public function create(): Response
    {
        return Inertia::render('tools/mock-api/index');
    }
}
