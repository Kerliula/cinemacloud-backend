<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

final class Movie extends Model
{
    use HasFactory;
    use HasUuids;
    use SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'release_year',
        'thumbnail_url',
        'embed_url',
        'trailer_url',
    ];

    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    public function genres()
    {
        return $this->belongsToMany(Genre::class);
    }

    public function scopeSearch(Builder $query, ?string $search): void
    {
        $query->when(
            $search,
            fn (Builder $q) =>
            $q->whereFullText(['title'], $search)
        );
    }

    protected function casts(): array
    {
        return [
            'release_year' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }
}
