<?php

declare(strict_types=1);

namespace App\Http\Resources\Movie;

use App\Models\Movie;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Movie
 */
final class MovieCatalogResource extends JsonResource
{
    public function toArray(mixed $request): array
    {
        return [
            'uuid' => $this->uuid,
            'title' => $this->title,
            'thumbnail_url' => $this->thumbnail_url,
            'release_year' => $this->release_year,
            'genres' => $this->genres->pluck('name'),
        ];
    }
}
