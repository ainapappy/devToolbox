<?php

namespace App\Models;

use Database\Factories\ClickFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $url_id
 * @property string|null $browser
 * @property string|null $country_code
 * @property string|null $referer
 * @property string|null $user_agent
 */
class Click extends Model
{
    /** @use HasFactory<ClickFactory> */
    use HasFactory;

    /**
     * Le clic est purement analytique : pas de updated_at.
     */
    public $timestamps = false;

    protected $fillable = [
        'url_id',
        'browser',
        'country_code',
        'referer',
        'user_agent',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Url, $this>
     */
    public function url(): BelongsTo
    {
        return $this->belongsTo(Url::class);
    }
}
