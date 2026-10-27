<?php

// Extensions autorisées pour l'outil File Drop. Liste par défaut imposée
// par le cahier des charges (Phase 17). Variable intermédiaire pour
// satisfaire l'analyse statique : env() peut retourner autre chose qu'une
// chaîne (booléens .env).
$allowedMimes = (string) env(
    'DEVTOOLBOX_UPLOADS_ALLOWED_MIMES',
    'zip,rar,gz,pdf,png,jpg,md,csv,json,txt',
);

return [

    /*
    |--------------------------------------------------------------------------
    | File Drop — réglages d'upload (Phase 17)
    |--------------------------------------------------------------------------
    |
    | Limites de l'outil File Drop : taille par fichier, nombre de fichiers
    | par requête, extensions autorisées et disque de stockage. Toutes les
    | valeurs sont surchargeables par .env, aucune limite n'est codée en dur
    | dans le code applicatif.
    |
    */

    'uploads' => [
        // Taille maximale par fichier (ko).
        'max_size_kb' => (int) env('DEVTOOLBOX_UPLOADS_MAX_SIZE_KB', 2048),

        // Nombre maximal de fichiers par upload.
        'max_files' => (int) env('DEVTOOLBOX_UPLOADS_MAX_FILES', 5),

        // Extensions autorisées (liste blanche de validation, alignée sur
        // la validation client). Mimes attendus : application/zip,
        // application/vnd.rar, application/gzip, application/pdf, image/png,
        // image/jpeg, text/markdown, text/csv, application/json, text/plain.
        'allowed_mimes' => explode(',', $allowedMimes),

        // Disque de stockage (les fichiers sont rangés sous uploads/).
        'disk' => (string) env('DEVTOOLBOX_UPLOADS_DISK', 'local'),
    ],

];
