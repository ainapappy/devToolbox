<?php

namespace App\Models;

use Database\Factories\UrlFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $target_url
 * @property string $code
 */
class Url extends Model
{
    /** @use HasFactory<UrlFactory> */
    use HasFactory;

    protected $fillable = [
        'target_url',
        'code',
    ];

    /**
     * @return HasMany<Click, $this>
     */
    public function clicks(): HasMany
    {
        return $this->hasMany(Click::class);
    }
}
