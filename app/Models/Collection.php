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
        'display_field',
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
            'relationships' => 'array',
            'display_field' => 'array'
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

    /**
     * Get all the fields combined (both the mandatory display field and the schema)
     */
    public function fields(): array
    {
        return array_merge($this->display_field, $this->schema);
    }
}
