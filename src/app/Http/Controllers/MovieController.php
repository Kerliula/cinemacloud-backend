<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\DTOs\Movie\MovieCatalogDTO;
use App\Actions\Movie\GetMovieCatalogAction;
use App\Http\Requests\Movie\MovieCatalogRequest;
use App\Http\Resources\Movie\MovieCatalogCollection;

final class MovieController extends Controller
{
    public function index(MovieCatalogRequest $request, GetMovieCatalogAction $action): MovieCatalogCollection
    {
        $dto = MovieCatalogDTO::fromRequest($request);

        return MovieCatalogCollection::make($action->execute($dto));
    }
}
