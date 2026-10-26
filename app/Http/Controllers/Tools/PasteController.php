<?php

namespace App\Http\Controllers\Tools;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class PasteController extends Controller
{
    /**
     * Écran principal de l'outil Secure Paste.
     */
    public function create(): Response
    {
        return Inertia::render('tools/paste/index');
    }
}
