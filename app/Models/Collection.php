<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Collection extends Model
{
    use HasUuids;

    protected $fillable = [
        'name',
        'schema',
        'user_id',
        'owner_type',
        'owner_id',
        'relationships'
    ];

    protected function casts(): array
    {
        return [
            'schema' => 'array',
            'relationships' => 'array'
        ];
    }

    public function owner(): MorphTo
    {
        return $this->morphTo();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->select(['id', 'name', 'email', 'email_verified_at', 'created_at', 'updated_at']);
    }
}
