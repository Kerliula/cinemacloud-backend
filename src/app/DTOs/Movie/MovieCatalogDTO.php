<?php

declare(strict_types=1);

namespace App\DTOs\Movie;

use App\Http\Requests\Movie\MovieCatalogRequest;

final class MovieCatalogDTO
{
    public function __construct(
        public string $search,
        public string $sortBy,
        public string $sortDirection,
        public int $perPage,
    ) {}

    public static function fromRequest(MovieCatalogRequest $request): self
    {
        return new self(
            search: $request->search(),
            sortBy: $request->sortBy(),
            sortDirection: $request->sortDirection(),
            perPage: $request->perPage(),
        );
    }
}
