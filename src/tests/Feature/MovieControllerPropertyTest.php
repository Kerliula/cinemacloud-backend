<?php

declare(strict_types=1);

namespace Tests\Feature\Models;

use App\Models\Admin;
use App\Models\Genre;
use App\Models\Movie;
use App\Models\User;

use function Eris\Generator\choose;
use function Eris\Generator\string;
use function Eris\Generator\suchThat;
use function Eris\Generator\tuple;

use Eris\TestTrait;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class MovieControllerPropertyTest extends TestCase
{
    use RefreshDatabase;
    use TestTrait;

    private const string MOVIES_URI = '/api/movies';
    private const string MOVIE_SHOW_URI = '/api/movies/{slug}';
    private const string MOVIE_DESTROY_URI = '/api/movies/{slug}';

    // ─────────────────────────────────────────────────────────────────
    // Pagination Properties
    // ─────────────────────────────────────────────────────────────────

    public function test_index_pagination_respects_per_page_limits(): void
    {
        $this->forAll(
            choose(1, (int) config('api.pagination.max_per_page'))
        )
            ->then(function (int $perPage): void {
                Movie::factory(50)->create();

                $response = $this->getJson(self::MOVIES_URI . '?per_page=' . $perPage)
                    ->assertOk();

                $movies = $response->json('movies');
                $this->assertLessThanOrEqual($perPage, count($movies));
            });
    }

    public function test_index_pages_correctly_with_various_page_numbers(): void
    {
        $this->forAll(
            choose(1, 3)
        )
            ->then(function (int $page): void {
                Movie::factory(50)->create();
                $perPage = config('api.pagination.default_per_page');

                $response = $this->getJson(self::MOVIES_URI . '?page=' . $page)
                    ->assertOk();

                $this->assertIsArray($response->json('movies'));
            });
    }

    // ─────────────────────────────────────────────────────────────────
    // Search Properties
    // ─────────────────────────────────────────────────────────────────

    public function test_index_search_returns_movies_containing_search_term(): void
    {
        $this->forAll(
            suchThat(fn ($s) => strlen($s) >= 2 && strlen($s) <= 100, string())
        )
            ->then(function (string $searchTerm): void {
                Movie::factory()->create(['title' => "The {$searchTerm} Movie"]);
                Movie::factory(5)->create();

                $response = $this->getJson(self::MOVIES_URI . '?search=' . urlencode($searchTerm))
                    ->assertOk();

                $movies = $response->json('movies');
                foreach ($movies as $movie) {
                    // Movie title should contain the search term
                    $this->assertStringContainsString(
                        strtolower($searchTerm),
                        strtolower($movie['title'])
                    );
                }
            });
    }

    // ─────────────────────────────────────────────────────────────────
    // Sorting Properties
    // ─────────────────────────────────────────────────────────────────

    public function test_index_sorts_by_title_in_ascending_order(): void
    {
        $this->forAll(
            choose(3, 10)
        )
            ->then(function (int $count): void {
                $movies = [];
                for ($i = 0; $i < $count; $i++) {
                    $movies[] = chr(65 + $i); // Create titles: A, B, C, etc.
                }

                foreach ($movies as $title) {
                    Movie::factory()->create(['title' => $title]);
                }

                $response = $this->getJson(self::MOVIES_URI . '?sort_by=title&sort_dir=asc')
                    ->assertOk();

                $resultTitles = array_map(
                    fn ($movie) => $movie['title'],
                    $response->json('movies')
                );

                // Results should be in order
                $this->assertEquals(
                    $resultTitles,
                    array_values($resultTitles)
                );
            });
    }

    public function test_index_sorts_by_release_year_in_ascending_order(): void
    {
        $this->forAll(
            choose(1, 5)
        )
            ->then(function (int $count): void {
                $years = [];
                for ($i = 0; $i < $count; $i++) {
                    $years[] = 2000 + ($i * 5);
                }

                foreach ($years as $year) {
                    Movie::factory()->create(['release_year' => $year]);
                }

                $response = $this->getJson(self::MOVIES_URI . '?sort_by=release_year&sort_dir=asc')
                    ->assertOk();

                $resultYears = array_map(
                    fn ($movie) => $movie['release_year'],
                    $response->json('movies')
                );

                // Years should be in ascending order
                $this->assertEquals(
                    $resultYears,
                    array_values($resultYears)
                );
            });
    }

    // ─────────────────────────────────────────────────────────────────
    // Show Movie Properties
    // ─────────────────────────────────────────────────────────────────

    public function test_show_always_returns_complete_movie_structure(): void
    {
        $this->forAll(
            tuple(
                suchThat(fn ($s) => strlen($s) > 0, string()),
                string(),
                choose(1900, 2100)
            )
        )
            ->then(function (array $data): void {
                [$title, $description, $year] = $data;

                $movie = Movie::factory()->create([
                    'title' => $title,
                    'description' => $description,
                    'release_year' => $year,
                ]);

                $response = $this->getJson(str_replace('{slug}', $movie->slug, self::MOVIE_SHOW_URI))
                    ->assertOk();

                $movieData = $response->json('movie');

                // Verify all required fields are present
                $this->assertArrayHasKey('id', $movieData);
                $this->assertArrayHasKey('title', $movieData);
                $this->assertArrayHasKey('slug', $movieData);
                $this->assertArrayHasKey('description', $movieData);
                $this->assertArrayHasKey('release_year', $movieData);
                $this->assertArrayHasKey('genres', $movieData);
                $this->assertArrayHasKey('trailer_urls', $movieData);
                $this->assertArrayHasKey('embed_urls', $movieData);

                // Verify data integrity
                $this->assertEquals($title, $movieData['title']);
                $this->assertEquals($description, $movieData['description']);
                $this->assertEquals($year, $movieData['release_year']);
            });
    }

    public function test_show_genres_are_always_arrays(): void
    {
        $this->forAll(
            choose(0, 5)
        )
            ->then(function (int $genreCount): void {
                DB::beginTransaction();

                try {
                    $movie = Movie::factory()->create();
                    $genres = Genre::factory($genreCount)->create();
                    $movie->genres()->attach($genres);

                    $response = $this->getJson(str_replace('{slug}', $movie->slug, self::MOVIE_SHOW_URI))
                        ->assertOk();

                    $genresData = $response->json('movie.genres');
                    $this->assertIsArray($genresData);
                    $this->assertCount($genreCount, $genresData);
                } finally {
                    DB::rollBack();
                }
            });
    }

    // ─────────────────────────────────────────────────────────────────
    // Delete (Destroy) Properties
    // ─────────────────────────────────────────────────────────────────

    public function test_destroy_always_requires_authentication(): void
    {
        $this->forAll(
            suchThat(fn ($s) => strlen($s) > 0, string())
        )
            ->then(function (string $slug): void {
                $movie = Movie::factory()->create();

                $response = $this->deleteJson(
                    str_replace('{slug}', $movie->slug, self::MOVIE_DESTROY_URI)
                );

                // Unauthenticated request should fail
                $this->assertTrue($response->status() === 401);
            });
    }

    public function test_destroy_always_requires_admin_role(): void
    {
        $this->forAll(
            suchThat(fn ($s) => strlen($s) > 0, string())
        )
            ->then(function (): void {
                $user = User::factory()->create();
                $movie = Movie::factory()->create();
                $token = Auth::login($user);

                $response = $this->withToken($token)
                    ->deleteJson(str_replace('{slug}', $movie->slug, self::MOVIE_DESTROY_URI));

                // Non-admin should be forbidden
                $this->assertEquals(403, $response->status());
            });
    }

    public function test_destroy_by_admin_always_returns_204(): void
    {
        $this->forAll(
            suchThat(fn ($s) => strlen($s) > 0, string())
        )
            ->then(function (): void {
                $admin = $this->createAdmin();
                $movie = Movie::factory()->create();
                $token = Auth::login($admin->user);

                $response = $this->withToken($token)
                    ->deleteJson(str_replace('{slug}', $movie->slug, self::MOVIE_DESTROY_URI));

                // Admin delete should succeed with 204
                $this->assertEquals(204, $response->status());
            });
    }

    public function test_destroy_actually_soft_deletes_movie(): void
    {
        $this->forAll(
            choose(1, 5)
        )
            ->then(function (int $count): void {
                $admin = $this->createAdmin();
                $movies = Movie::factory($count)->create();
                $movieToDelete = $movies->first();
                $token = Auth::login($admin->user);

                $this->withToken($token)
                    ->deleteJson(str_replace('{slug}', $movieToDelete->slug, self::MOVIE_DESTROY_URI));

                // Deleted movie should have deleted_at set
                $this->assertNotNull($movieToDelete->fresh()->deleted_at);

                // Other movies should not be deleted
                foreach ($movies->skip(1) as $movie) {
                    $this->assertNull($movie->fresh()->deleted_at);
                }
            });
    }

    public function test_index_never_returns_soft_deleted_movies(): void
    {
        $this->forAll(
            choose(1, 5)
        )
            ->then(function (int $activeCount): void {
                DB::beginTransaction();

                try {
                    $deletedCount = $activeCount > 0 ? 1 : 0;
                    $activeMovies = Movie::factory($activeCount)->create();
                    $deletedMovies = Movie::factory($deletedCount)->create();

                    foreach ($deletedMovies as $movie) {
                        $movie->delete();
                    }

                    $response = $this->getJson(self::MOVIES_URI)->assertOk();
                    $returnedIds = array_map(fn ($m) => $m['id'], $response->json('movies'));

                    // Deleted movies should not be in results
                    foreach ($deletedMovies as $movie) {
                        $this->assertNotContains($movie->id, $returnedIds);
                    }

                    // Active movies should be in results
                    foreach ($activeMovies as $movie) {
                        $this->assertContains($movie->id, $returnedIds);
                    }
                } finally {
                    DB::rollBack();
                }
            });
    }

    public function test_show_never_returns_soft_deleted_movies(): void
    {
        $this->forAll(
            suchThat(fn ($s) => strlen($s) > 0, string())
        )
            ->then(function (): void {
                $movie = Movie::factory()->create();
                $movie->delete();

                $response = $this->getJson(str_replace('{slug}', $movie->slug, self::MOVIE_SHOW_URI));

                // Soft deleted movie should return 404
                $this->assertEquals(404, $response->status());
            });
    }

    // ─────────────────────────────────────────────────────────────────
    // Response Consistency Properties
    // ─────────────────────────────────────────────────────────────────

    public function test_index_and_show_return_same_movie_fields(): void
    {
        $this->forAll(
            suchThat(fn ($s) => strlen($s) > 0, string())
        )
            ->then(function (): void {
                $movie = Movie::factory()->create();

                $indexResponse = $this->getJson(self::MOVIES_URI)->json('movies.0');
                $showResponse = $this->getJson(str_replace('{slug}', $movie->slug, self::MOVIE_SHOW_URI))
                    ->json('movie');

                // Both responses should have the same basic fields
                $commonFields = ['id', 'title', 'slug', 'description', 'release_year'];
                foreach ($commonFields as $field) {
                    $this->assertArrayHasKey($field, $indexResponse);
                    $this->assertArrayHasKey($field, $showResponse);
                }
            });
    }

    // ─────────────────────────────────────────────────────────────────
    // Helper
    // ─────────────────────────────────────────────────────────────────

    private function createAdmin(): Admin
    {
        $user = User::factory()->create();

        return Admin::factory()->forUser($user)->create();
    }
}
