<?php

declare(strict_types=1);

namespace App\Http\Resources\Movie;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Collection;

/**
 * @property-read Collection<int, MovieCatalogResource> $collection
 */
final class MovieCatalogCollection extends ResourceCollection
{
    public static $wrap = 'movies';

    public function toArray(Request $request): array
    {
        return parent::toArray($request);
    }

    public function paginationInformation(Request $request, array $paginated, array $default): array
    {
        return [
            'meta' => [
                'current_page' => $paginated['current_page'],
                'per_page' => $paginated['per_page'],
                'total' => $paginated['total'],
                'last_page' => $paginated['last_page'],
                'from' => $paginated['from'],
                'to' => $paginated['to'],
            ],
            'links' => [
                'first' => $paginated['first_page_url'],
                'last' => $paginated['last_page_url'],
                'prev' => $paginated['prev_page_url'],
                'next' => $paginated['next_page_url'],
            ],
        ];
    }
}
