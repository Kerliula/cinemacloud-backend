<?php

declare(strict_types=1);

namespace App\Http\Resources\Movie;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Collection;

/**
 * @property-read Collection<int, MovieResource> $collection
 */
final class MovieCollection extends ResourceCollection
{
    public static $wrap = 'movies';

    public function toArray(Request $request): array
    {
        return parent::toArray($request);
    }
}
