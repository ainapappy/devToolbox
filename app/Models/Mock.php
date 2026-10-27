<?php

namespace App\Models;

use Database\Factories\MockFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $payload
 * @property string $slug
 * @property int $status_code
 */
class Mock extends Model
{
    /** @use HasFactory<MockFactory> */
    use HasFactory;

    protected $fillable = [
        'payload',
        'slug',
        'status_code',
    ];

    protected function casts(): array
    {
        return [
            'status_code' => 'integer',
        ];
    }
}
