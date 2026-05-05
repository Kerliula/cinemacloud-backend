<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

final class Movie extends Model
{
    use HasFactory;
    use HasSlug;
    use SoftDeletes;

    protected $fillable = [
        'title',
        'slug',
        'description',
        'release_year',
        'thumbnail_url',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function genres(): BelongsToMany
    {
        return $this->belongsToMany(Genre::class);
    }

    public function embedUrls(): HasMany
    {
        return $this->hasMany(MovieEmbedUrl::class);
    }

    public function trailerUrls(): HasMany
    {
        return $this->hasMany(MovieTrailerUrl::class);
    }

    public function scopeSearch(Builder $query, ?string $search): void
    {
        $query->when($search, fn(Builder $q, string $value) => $q->where('title', 'like', '%' . $value . '%'));
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('title')
            ->saveSlugsTo('slug')
            ->slugsShouldBeNoLongerThan(255);
    }

    protected function casts(): array
    {
        return [
            'release_year' => 'integer',
        ];
    }
}
