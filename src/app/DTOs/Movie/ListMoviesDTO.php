<?php

declare(strict_types=1);

namespace App\DTOs\Movie;

use App\Http\Requests\Movie\ListMoviesRequest;

final readonly class ListMoviesDTO
{
    public function __construct(
        public int $perPage,
        public int $page,
        public ?string $search,
        public string $sortBy,
        public string $sortDirection,
    ) {
    }

    public static function fromRequest(ListMoviesRequest $request): self
    {
        return new self(
            perPage: $request->perPage(),
            page: $request->page(),
            search: $request->search(),
            sortBy: $request->sortBy(),
            sortDirection: $request->sortDirection(),
        );
    }
}
