<?php

namespace App\Http\Controllers\Tools;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class MarkdownController extends Controller
{
    /**
     * Écran principal de l'outil Markdown Previewer.
     */
    public function index(): Response
    {
        return Inertia::render('tools/markdown/index');
    }
}
