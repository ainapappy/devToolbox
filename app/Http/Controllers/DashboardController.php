<?php

namespace App\Http\Controllers;

use App\Tools\Tool;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Point d'entrée du front-office : liste des outils de la boîte à outils.
     */
    public function __invoke(): Response
    {
        return Inertia::render('dashboard', [
            'tools' => array_map(
                fn (Tool $tool) => $tool->toArray(),
                Tool::all(),
            ),
        ]);
    }
}
