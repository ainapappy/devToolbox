<?php

namespace App\Http\Controllers\Tools;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tools\StoreFileUploadRequest;
use App\Models\FileUpload;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class FileUploadController extends Controller
{
    /**
     * Écran principal de l'outil File Drop (dropzone + options du lien).
     * Les limites de config/devtoolbox.php et les choix proposés pour la
     * durée de validité / le quota de téléchargements sont exposés au
     * client pour une validation alignée sur le serveur.
     */
    public function index(): Response
    {
        return Inertia::render('tools/upload/index', [
            'maxSizeKb' => (int) config('devtoolbox.uploads.max_size_kb'),
            'maxFiles' => (int) config('devtoolbox.uploads.max_files'),
            'allowedMimes' => (array) config('devtoolbox.uploads.allowed_mimes'),
            'durations' => StoreFileUploadRequest::DURATIONS,
            'downloadOptions' => StoreFileUploadRequest::DOWNLOAD_OPTIONS,
        ]);
    }

    /**
     * Upload d'un à N fichiers, ouvert aux invités comme les autres outils
     * du toolbox : stockage sous uploads/ sur le disque configuré, une ligne
     * en base par fichier, rattaché au compte seulement si une session existe.
     *
     * @return JsonResponse|RedirectResponse
     */
    public function store(StoreFileUploadRequest $request): SymfonyResponse
    {
        $disk = (string) config('devtoolbox.uploads.disk');

        $uploaded = [];

        foreach ($request->validated('files') as $file) {
            /** @var UploadedFile $file */
            $upload = FileUpload::create([
                'user_id' => $request->user()?->id,
                'uuid' => (string) Str::uuid(),
                'original_name' => $file->getClientOriginalName(),
                'path' => $file->store('uploads', $disk),
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
                'max_downloads' => $request->maxDownloads(),
                'expires_at' => $request->expiresAt(),
                'password_hash' => $request->password() !== null
                    ? Hash::make($request->password())
                    : null,
            ]);

            $uploaded[] = self::metadata($upload);
        }

        if ($request->expectsJson()) {
            return response()->json(['files' => $uploaded], 201);
        }

        return redirect()
            ->route('tools.upload.index')
            ->with('uploaded', $uploaded);
    }

    /**
     * Téléchargement public par UUID, façon Secure Paste : mot de passe
     * optionnel, expiration, quota de téléchargements. Le compteur est
     * incrémenté dans une transaction verrouillée (lockForUpdate) pour
     * qu'un lien à quota de 1 ne puisse pas servir deux téléchargements
     * simultanés.
     */
    public function download(Request $request, string $uuid): SymfonyResponse
    {
        [$status, $upload, $wrongPassword] = $this->resolveSharedUpload($request, $uuid);

        if ($status === 'unavailable') {
            // UUID inconnu, lien expiré ou épuisé : même information que
            // pour les pastes, sans révéler pourquoi.
            return Inertia::render('tools/upload/expired')->toResponse($request);
        }

        if ($status === 'locked') {
            return Inertia::render('tools/upload/locked', [
                'uuid' => $uuid,
                'error' => $wrongPassword ? 'Mot de passe incorrect.' : null,
            ])->toResponse($request);
        }

        return Storage::disk((string) config('devtoolbox.uploads.disk'))
            ->download($upload->path, $upload->original_name);
    }

    /**
     * Résout un lien public : statut « available », « locked » ou
     * « unavailable ». La transaction verrouillée garantit un compteur
     * de téléchargements exact, même sous requêtes concurrentes.
     *
     * @return array{string, FileUpload|null, bool}
     */
    private function resolveSharedUpload(Request $request, string $uuid): array
    {
        return DB::transaction(function () use ($request, $uuid): array {
            $upload = FileUpload::query()
                ->where('uuid', $uuid)
                ->lockForUpdate()
                ->first();

            if ($upload === null || $upload->isExpired() || $upload->isExhausted()) {
                return ['unavailable', null, false];
            }

            if ($upload->requiresPassword()) {
                $password = $request->input('password');

                if (! is_string($password) || $password === '' || ! Hash::check($password, $upload->password_hash)) {
                    return ['locked', $upload, is_string($password) && $password !== ''];
                }
            }

            $upload->increment('downloads');

            return ['available', $upload, false];
        });
    }

    /**
     * Suppression d'un fichier : disque puis base, réservée au propriétaire.
     *
     * @return JsonResponse|RedirectResponse
     */
    public function destroy(Request $request, string $uuid): SymfonyResponse
    {
        $upload = $this->findOwnedUpload($request, $uuid);

        Storage::disk((string) config('devtoolbox.uploads.disk'))->delete($upload->path);
        $upload->delete();

        if ($request->expectsJson()) {
            return response()->json(status: 204);
        }

        return redirect()->route('tools.upload.index');
    }

    /**
     * Fichier du demandeur uniquement (destroy) : 404 (et non 403) pour
     * ne pas révéler l'existence d'un uuid appartenant à un autre utilisateur.
     */
    private function findOwnedUpload(Request $request, string $uuid): FileUpload
    {
        $upload = FileUpload::query()
            ->where('uuid', $uuid)
            ->first();

        abort_if($upload === null || $upload->user_id !== $request->user()->id, 404);

        return $upload;
    }

    /**
     * Métadonnées sérialisables exposées au front (flash/JSON).
     *
     * @return array{uuid: string, original_name: string, mime_type: string, size: int, max_downloads: int|null, expires_at: string|null}
     */
    private static function metadata(FileUpload $upload): array
    {
        return [
            'uuid' => $upload->uuid,
            'original_name' => $upload->original_name,
            'mime_type' => $upload->mime_type,
            'size' => $upload->size,
            'max_downloads' => $upload->max_downloads,
            'expires_at' => $upload->expires_at?->toIso8601String(),
        ];
    }
}
