<?php

declare(strict_types=1);

namespace App\Http\Resources\Movie;

use App\Http\Resources\Genre\GenreCollection;
use App\Http\Resources\MovieEmbedUrl\MovieEmbedUrlCollection;
use App\Http\Resources\MovieTrailerUrl\MovieTrailerUrlCollection;
use App\Models\Movie;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Movie
 */
final class MovieResource extends JsonResource
{
    public static $wrap = 'movie';

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->description,
            'thumbnail_url' => $this->thumbnail_url,
            'release_year' => $this->release_year,

            'genres' => $this->whenLoaded('genres', fn() => GenreCollection::make($this->genres)),
            'trailer_urls' => $this->whenLoaded(
                'trailerUrls',
                fn() => MovieTrailerUrlCollection::make($this->trailerUrls),
            ),
            'embed_urls' => $this->whenLoaded(
                'embedUrls',
                fn() => MovieEmbedUrlCollection::make($this->embedUrls),
            ),
        ];
    }
}
