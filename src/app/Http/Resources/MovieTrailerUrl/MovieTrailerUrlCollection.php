<?php

declare(strict_types=1);

namespace App\Http\Resources\MovieTrailerUrl;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Collection;

/**
 * @property-read Collection<int, MovieTrailerUrlResource> $collection
 */
final class MovieTrailerUrlCollection extends ResourceCollection
{
    public static $wrap = 'trailer_urls';

    public function toArray(Request $request): array
    {
        return parent::toArray($request);
    }
}
