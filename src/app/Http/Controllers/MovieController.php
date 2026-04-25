<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Movie\MovieIndexRequest;
use App\Http\Resources\Movie\MovieCatalogCollection;
use App\Models\Movie;

final class MovieController extends Controller
{
    public function index(MovieIndexRequest $request): MovieCatalogCollection
    {
        $movies = Movie::query()
            ->search($request->search())
            ->orderBy($request->sortBy(), $request->sortDirection())
            ->paginate($request->perPage());

        return MovieCatalogCollection::make($movies);
    }
}
