<?php

namespace App\Models;

use Database\Factories\KnownDeviceFactory;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Appareil connu d'un utilisateur (empreinte IP + user-agent — Phase 16).
 * Sert à n'alerter que sur les connexions depuis un nouvel appareil.
 */
#[UseFactory(KnownDeviceFactory::class)]
class KnownDevice extends Model
{
    /** @use HasFactory<KnownDeviceFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'fingerprint',
        'device',
        'first_seen_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'first_seen_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
