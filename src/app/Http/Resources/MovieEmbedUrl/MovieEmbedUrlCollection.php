<?php

declare(strict_types=1);

namespace App\Http\Resources\MovieEmbedUrl;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Collection;

/**
 * @property-read Collection<int, MovieEmbedUrlResource> $collection
 */
final class MovieEmbedUrlCollection extends ResourceCollection
{
    public static $wrap = 'movie_urls';

    public function toArray(Request $request): array
    {
        return parent::toArray($request);
    }
}
