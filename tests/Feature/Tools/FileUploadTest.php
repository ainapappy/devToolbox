<?php

use App\Http\Requests\Tools\StoreFileUploadRequest;
use App\Models\FileUpload;
use App\Models\User;
use App\Tools\Tool;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use ZipArchive;

beforeEach(function () {
    Storage::fake((string) config('devtoolbox.uploads.disk'));
    $this->withoutVite();
});

function uploadDisk(): string
{
    return (string) config('devtoolbox.uploads.disk');
}

test('the upload page exposes the configured limits as props', function () {
    config()->set('devtoolbox.uploads.max_size_kb', 512);
    config()->set('devtoolbox.uploads.max_files', 3);
    config()->set('devtoolbox.uploads.allowed_mimes', ['txt', 'pdf']);

    $this->get(route('tools.upload.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('tools/upload/index', false)
            ->where('maxSizeKb', 512)
            ->where('maxFiles', 3)
            ->where('allowedMimes', ['txt', 'pdf'])
            ->where('durations', StoreFileUploadRequest::DURATIONS)
            ->where('downloadOptions', StoreFileUploadRequest::DOWNLOAD_OPTIONS));
});

test('a signed-in user can upload several valid files at once', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('tools.upload.store'), [
        'files' => [
            UploadedFile::fake()->createWithContent('notes.md', 'hello world'),
            UploadedFile::fake()->createWithContent('data.csv', 'a,b,c'),
        ],
    ]);

    $response->assertRedirect(route('tools.upload.index'));

    $uploads = FileUpload::query()->get();
    expect($uploads)->toHaveCount(2)
        ->and($uploads->pluck('original_name'))->toContain('notes.md', 'data.csv')
        ->and($uploads->every(fn ($upload) => Str::isUuid($upload->uuid)))->toBeTrue()
        ->and($uploads->every(fn ($upload) => str_starts_with($upload->path, 'uploads/')))->toBeTrue()
        ->and($uploads->every(fn ($upload) => $upload->user_id === $user->id))->toBeTrue();

    Storage::disk(uploadDisk())->assertExists($uploads[0]->path);
    Storage::disk(uploadDisk())->assertExists($uploads[1]->path);

    $response->assertSessionHas('uploaded', fn ($uploaded) => count($uploaded) === 2);
});

test('the store endpoint accepts link options and hashes the password', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->postJson(route('tools.upload.store'), [
        'files' => [UploadedFile::fake()->createWithContent('rapport.pdf', '%PDF-1.4 test')],
        'max_downloads' => 3,
        'expiration' => '7j',
        'password' => 'mot-de-passe',
    ])->assertCreated();

    $upload = FileUpload::query()->sole();
    expect($upload->max_downloads)->toBe(3)
        ->and($upload->downloads)->toBe(0)
        ->and($upload->expires_at)->not->toBeNull()
        ->and($upload->expires_at->greaterThan(now()->addDays(6)))->toBeTrue()
        ->and($upload->password_hash)->not->toBe('mot-de-passe')
        ->and(Hash::check('mot-de-passe', $upload->password_hash))->toBeTrue();
});

test('a missing or invalid expiration or max_downloads is rejected', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post(route('tools.upload.store'), [
        'files' => [UploadedFile::fake()->createWithContent('a.txt', 'a')],
        'expiration' => '1an',
    ])->assertSessionHasErrors('expiration');

    $this->actingAs($user)->post(route('tools.upload.store'), [
        'files' => [UploadedFile::fake()->createWithContent('a.txt', 'a')],
        'max_downloads' => 0,
    ])->assertSessionHasErrors('max_downloads');
});

test('a file larger than max_size_kb is rejected', function () {
    config()->set('devtoolbox.uploads.max_size_kb', 1);

    $this->actingAs(User::factory()->create())
        ->post(route('tools.upload.store'), [
            'files' => [
                UploadedFile::fake()->createWithContent('big.txt', str_repeat('a', 2048)),
            ],
        ])
        ->assertSessionHasErrors('files.0');

    expect(FileUpload::query()->count())->toBe(0)
        ->and(Storage::disk(uploadDisk())->allFiles())->toBe([]);
});

test('more files than max_files are rejected', function () {
    config()->set('devtoolbox.uploads.max_files', 2);

    $this->actingAs(User::factory()->create())
        ->post(route('tools.upload.store'), [
            'files' => [
                UploadedFile::fake()->createWithContent('a.txt', 'a'),
                UploadedFile::fake()->createWithContent('b.txt', 'b'),
                UploadedFile::fake()->createWithContent('c.txt', 'c'),
            ],
        ])
        ->assertSessionHasErrors('files');

    expect(FileUpload::query()->count())->toBe(0);
});

test('a mime type outside allowed_mimes is rejected', function () {
    // Liste imposée par le cahier des charges (défaut de la config).
    config()->set('devtoolbox.uploads.allowed_mimes', [
        'zip', 'rar', 'gz', 'pdf', 'png', 'jpg', 'md', 'csv', 'json', 'txt',
    ]);

    $user = User::factory()->create();

    $this->actingAs($user)->post(route('tools.upload.store'), [
        'files' => [UploadedFile::fake()->createWithContent('song.mp3', 'ID3 garbage')],
    ])->assertSessionHasErrors('files.0');

    // Un type de la liste passe bien (double emploi avec le test du PDF).
    $this->actingAs($user)->post(route('tools.upload.store'), [
        'files' => [UploadedFile::fake()->createWithContent('archive.zip', "PK\x03\x04content")],
    ])->assertRedirect();

    expect(FileUpload::query()->where('original_name', 'archive.zip')->exists())->toBeTrue();
});

test('a public download works and increments the counter atomically', function () {
    $upload = FileUpload::factory()->create(['original_name' => 'mon fichier.txt']);
    Storage::disk(uploadDisk())->put($upload->path, 'contenu secret');

    // Aucune authentification : le lien est public (façon Secure Paste).
    // Réponse streamée : le contenu passe par le disque, on vérifie le
    // nom d'origine et l'incrément du compteur.
    $this->get(route('tools.upload.download', $upload->uuid))
        ->assertOk()
        ->assertDownload('mon fichier.txt');

    expect($upload->refresh()->downloads)->toBe(1)
        ->and(Storage::disk(uploadDisk())->get($upload->path))->toBe('contenu secret');
});

test('an exhausted link is refused once the quota is reached', function () {
    $upload = FileUpload::factory()->exhausted()->create();
    Storage::disk(uploadDisk())->put($upload->path, 'contenu');

    $this->get(route('tools.upload.download', $upload->uuid))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('tools/upload/expired', false));

    expect($upload->refresh()->downloads)->toBe(1);
});

test('an expired link is refused like an expired paste', function () {
    $upload = FileUpload::factory()->expired()->create();
    Storage::disk(uploadDisk())->put($upload->path, 'contenu');

    $this->get(route('tools.upload.download', $upload->uuid))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('tools/upload/expired', false));

    expect(Storage::disk(uploadDisk())->exists($upload->path))->toBeTrue();
});

test('an unknown uuid shows the expired page', function () {
    $this->get(route('tools.upload.download', (string) Str::uuid()))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('tools/upload/expired', false));
});

test('a locked file asks for the password without revealing availability', function () {
    $upload = FileUpload::factory()->locked()->create();
    Storage::disk(uploadDisk())->put($upload->path, 'contenu');

    // Sans mot de passe : page de déverrouillage, compteur intact.
    $this->get(route('tools.upload.download', $upload->uuid))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('tools/upload/locked', false)
            ->where('uuid', $upload->uuid)
            ->where('error', null));

    expect($upload->refresh()->downloads)->toBe(0);

    // Mauvais mot de passe : refus avec erreur, compteur intact.
    $this->get(route('tools.upload.download', ['uuid' => $upload->uuid, 'password' => 'faux']))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('tools/upload/locked', false)
            ->where('error', 'Mot de passe incorrect.'));

    expect($upload->refresh()->downloads)->toBe(0);

    // Bon mot de passe : téléchargement et incrémentation.
    $this->get(route('tools.upload.download', ['uuid' => $upload->uuid, 'password' => 'secret']))
        ->assertOk()
        ->assertDownload($upload->original_name);

    expect($upload->refresh()->downloads)->toBe(1)
        ->and(Storage::disk(uploadDisk())->get($upload->path))->toBe('contenu');

    expect($upload->refresh()->downloads)->toBe(1);
});

test('the owner can destroy the file on disk and in database', function () {
    $upload = FileUpload::factory()->create();
    Storage::disk(uploadDisk())->put($upload->path, 'contenu');

    $this->actingAs($upload->user)
        ->deleteJson(route('tools.upload.destroy', $upload->uuid))
        ->assertNoContent();

    expect(FileUpload::query()->whereKey($upload->getKey())->exists())->toBeFalse()
        ->and(Storage::disk(uploadDisk())->exists($upload->path))->toBeFalse();
});

test('another user cannot destroy a file', function () {
    $upload = FileUpload::factory()->create();
    Storage::disk(uploadDisk())->put($upload->path, 'contenu');

    $this->actingAs(User::factory()->create())
        ->delete(route('tools.upload.destroy', $upload->uuid))
        ->assertNotFound();

    expect(FileUpload::query()->whereKey($upload->getKey())->exists())->toBeTrue()
        ->and(Storage::disk(uploadDisk())->exists($upload->path))->toBeTrue();
});

test('guests can upload anonymously, destroy stays reserved to the owner', function () {
    $this->post(route('tools.upload.store'), [
        'files' => [UploadedFile::fake()->createWithContent('anonyme.md', 'depot invite')],
    ])->assertRedirect(route('tools.upload.index'));

    $upload = FileUpload::query()->sole();
    expect($upload->user_id)->toBeNull();

    $this->delete(route('tools.upload.destroy', $upload->uuid))
        ->assertRedirect(route('login'));

    $this->get(route('tools.upload.download', $upload->uuid))
        ->assertOk()
        ->assertDownload($upload->original_name);

    expect(Storage::disk(uploadDisk())->get($upload->path))->toBe('depot invite');
});

test('a batch link downloads every file of the upload as one zip', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson(route('tools.upload.store'), [
        'files' => [
            UploadedFile::fake()->createWithContent('a.md', 'contenu a'),
            UploadedFile::fake()->createWithContent('b.csv', 'a,b'),
            UploadedFile::fake()->createWithContent('c.json', '{"k":1}'),
        ],
    ])->assertCreated();

    $batch = $response->json('files.0.batch_uuid');

    expect($batch)->toBeString()
        ->and(FileUpload::where('batch_uuid', $batch)->count())->toBe(3);

    $download = $this->get(route('tools.upload.download', $batch))
        ->assertOk()
        ->assertDownload('file-drop-'.substr($batch, 0, 8).'.zip');

    // BinaryFileResponse : le contenu se lit via getFile(), pas getContent().
    $zipPath = $download->getFile()->getPathname();

    $zip = new ZipArchive;
    expect($zip->open($zipPath))->toBeTrue()
        ->and($zip->numFiles)->toBe(3)
        ->and((string) $zip->getNameIndex(0))->toBe('a.md')
        ->and((string) $zip->getFromName('b.csv'))->toBe('a,b');
    $zip->close();

    expect(FileUpload::where('batch_uuid', $batch)->where('downloads', 1)->get())->toHaveCount(3);
});

test('the File Drop tool is registered after SVG Placeholder', function () {
    $tools = Tool::all();
    $last = $tools[count($tools) - 1];

    expect($last->slug)->toBe('upload')
        ->and($last->color)->toBe('#f59e0b')
        ->and($last->icon)->toBe('upload');
});
