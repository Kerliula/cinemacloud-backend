<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Movie\ListMoviesAction;
use App\DTOs\Movie\ListMoviesDTO;
use App\Http\Requests\Movie\ListMoviesRequest;
use App\Http\Resources\Movie\MovieCollection;
use App\Http\Resources\Movie\MovieResource;
use App\Models\Movie;
use Dedoc\Scramble\Attributes\Endpoint;
use Illuminate\Http\JsonResponse;

final class MovieController extends Controller
{
    #[Endpoint(title: 'List movies catalog')]
    public function index(ListMoviesRequest $request, ListMoviesAction $action): MovieCollection
    {
        $dto = ListMoviesDTO::fromRequest($request);

        return $action->execute($dto);
    }

    #[Endpoint(title: 'Show movie details')]
    public function show(Movie $movie): MovieResource
    {
        $movie->load('embedUrls', 'trailerUrls', 'genres');

        return MovieResource::make($movie);
    }

    #[Endpoint(title: 'Delete a movie')]
    public function destroy(Movie $movie): JsonResponse
    {
        $movie->delete();

        return $this->noContent();
    }
}
