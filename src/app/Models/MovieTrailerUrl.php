<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class MovieTrailerUrl extends Model
{
    use HasFactory;

    protected $fillable = [
        'movie_id',
        'url',
        'provider',
    ];

    public function movie(): BelongsTo
    {
        return $this->belongsTo(Movie::class);
    }

    public function scopeForMovie($query, Movie $movie)
    {
        return $query->where('movie_id', $movie->id);
    }
}
