<?php

namespace App\Models;

use Database\Factories\PasteFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $content
 * @property Carbon|null $expires_at
 * @property bool $burn_after_read
 * @property string $hash
 */
class Paste extends Model
{
    /** @use HasFactory<PasteFactory> */
    use HasFactory;

    protected $fillable = [
        'content',
        'expires_at',
        'burn_after_read',
        'hash',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'burn_after_read' => 'boolean',
        ];
    }

    /**
     * Le secret est-il encore consultable ?
     */
    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }
}
