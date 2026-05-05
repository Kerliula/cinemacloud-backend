<?php

declare(strict_types=1);

namespace App\Actions\Movie;

use App\DTOs\Movie\ListMoviesDTO;
use App\Http\Resources\Movie\MovieCollection;
use App\Models\Movie;

final class ListMoviesAction
{
    public function execute(ListMoviesDTO $data): MovieCollection
    {
        $movies = Movie::query()
            ->with('genres')
            ->search($data->search)
            ->orderBy($data->sortBy, $data->sortDirection)
            ->paginate($data->perPage, ['*'], 'page', $data->page);

        return MovieCollection::make($movies);
    }
}
