<?php

namespace App\Models;

use Database\Factories\FileUploadFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Fichier déposé via l'outil File Drop : façon « Secure Paste pour
 * fichiers », le lien public (uuid) est soumis à expiration, quota de
 * téléchargements et mot de passe optionnel.
 *
 * @property int $id
 * @property int $user_id
 * @property string $uuid
 * @property string $original_name
 * @property string $path
 * @property string $mime_type
 * @property int $size
 * @property int $downloads
 * @property int|null $max_downloads
 * @property string|null $password_hash
 * @property Carbon|null $expires_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class FileUpload extends Model
{
    /** @use HasFactory<FileUploadFactory> */
    use HasFactory;

    protected $fillable = [
        'uuid',
        'original_name',
        'path',
        'mime_type',
        'size',
        'max_downloads',
        'password_hash',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'size' => 'integer',
            'downloads' => 'integer',
            'max_downloads' => 'integer',
            'expires_at' => 'datetime',
        ];
    }

    /**
     * Propriétaire du fichier (destroy lui est réservé).
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Le lien est-il encore dans sa période de validité ?
     */
    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    /**
     * Quota de téléchargements atteint ?
     */
    public function isExhausted(): bool
    {
        return $this->max_downloads !== null && $this->downloads >= $this->max_downloads;
    }

    /**
     * Un mot de passe protège-t-il le fichier ?
     */
    public function requiresPassword(): bool
    {
        return $this->password_hash !== null;
    }
}
