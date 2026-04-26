<?php

declare(strict_types=1);

namespace App\Actions\Movie;

use App\DTOs\Movie\MovieCatalogDTO;
use App\Models\Movie;
use Illuminate\Pagination\LengthAwarePaginator;

final class GetMovieCatalogAction
{
    public function execute(MovieCatalogDTO $dto): LengthAwarePaginator
    {
        return Movie::query()
            ->search($dto->search)
            ->orderBy($dto->sortBy, $dto->sortDirection)
            ->paginate($dto->perPage);
    }
}
