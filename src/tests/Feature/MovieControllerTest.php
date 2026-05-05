<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Genre;
use App\Models\Movie;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

final class MovieControllerTest extends TestCase
{
    use RefreshDatabase;

    private const string MOVIES_URI = '/api/movies';
    private const string MOVIE_SHOW_URI = '/api/movies/{slug}';
    private const string MOVIE_DESTROY_URI = '/api/movies/{slug}';

    // ─────────────────────────────────────────────────────────────────
    // GET /api/movies - List Movies
    // ─────────────────────────────────────────────────────────────────

    public function test_index_returns_200_with_correct_structure(): void
    {
        Movie::factory(5)->create();

        $this->getJson(self::MOVIES_URI)
            ->assertOk()
            ->assertJsonStructure([
                'movies' => [
                    '*' => [
                        'id',
                        'title',
                        'slug',
                        'description',
                        'thumbnail_url',
                        'release_year',
                        'genres',
                    ],
                ],
            ]);
    }

    public function test_index_returns_movies_with_genres_loaded(): void
    {
        $movie = Movie::factory()->create();
        $genres = Genre::factory(3)->create();
        $movie->genres()->attach($genres);

        $response = $this->getJson(self::MOVIES_URI)
            ->assertOk()
            ->json('movies.0');

        $this->assertIsArray($response['genres']);
        $this->assertCount(3, $response['genres']);
    }

    public function test_index_paginates_movies_with_default_per_page(): void
    {
        Movie::factory(15)->create();
        $defaultPerPage = config('api.pagination.default_per_page');

        $this->getJson(self::MOVIES_URI)
            ->assertOk()
            ->assertJsonCount($defaultPerPage, 'movies');
    }

    public function test_index_respects_per_page_parameter(): void
    {
        Movie::factory(20)->create();

        $this->getJson(self::MOVIES_URI . '?per_page=5')
            ->assertOk()
            ->assertJsonCount(5, 'movies');
    }

    public function test_index_respects_page_parameter(): void
    {
        Movie::factory(25)->create();
        $perPage = config('api.pagination.default_per_page');

        $firstPageResponse = $this->getJson(self::MOVIES_URI . '?page=1')->json('movies');
        $secondPageResponse = $this->getJson(self::MOVIES_URI . '?page=2')->json('movies');

        // Movies on different pages should be different
        $this->assertNotEquals($firstPageResponse[0]['id'], $secondPageResponse[0]['id']);
    }

    public function test_index_searches_movies_by_title(): void
    {
        Movie::factory()->create(['title' => 'The Shawshank Redemption']);
        Movie::factory()->create(['title' => 'The Dark Knight']);
        Movie::factory(3)->create();

        $response = $this->getJson(self::MOVIES_URI . '?search=Shawshank')
            ->assertOk()
            ->json('movies');

        $this->assertCount(1, $response);
        $this->assertStringContainsString('Shawshank', $response[0]['title']);
    }

    public function test_index_search_is_case_insensitive(): void
    {
        Movie::factory()->create(['title' => 'The Shawshank Redemption']);
        Movie::factory(2)->create();

        $response = $this->getJson(self::MOVIES_URI . '?search=shawshank')
            ->assertOk()
            ->json('movies');

        $this->assertCount(1, $response);
    }

    public function test_index_search_returns_no_results_when_no_match(): void
    {
        Movie::factory(5)->create();

        $this->getJson(self::MOVIES_URI . '?search=NonexistentMovie')
            ->assertOk()
            ->assertJsonCount(0, 'movies');
    }

    public function test_index_search_requires_minimum_length_of_two_characters(): void
    {
        $this->getJson(self::MOVIES_URI . '?search=a')
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['search']);
    }

    public function test_index_sorts_by_created_at_descending_by_default(): void
    {
        $firstMovie = Movie::factory()->create(['created_at' => now()->subMinutes(3)]);
        $secondMovie = Movie::factory()->create(['created_at' => now()->subMinutes(2)]);
        $thirdMovie = Movie::factory()->create(['created_at' => now()->subMinutes(1)]);

        $response = $this->getJson(self::MOVIES_URI)->json('movies');

        // Most recent first (descending order)
        $this->assertEquals($thirdMovie->id, $response[0]['id']);
        $this->assertEquals($secondMovie->id, $response[1]['id']);
        $this->assertEquals($firstMovie->id, $response[2]['id']);
    }

    public function test_index_sorts_by_title_ascending(): void
    {
        Movie::factory()->create(['title' => 'Zebra']);
        Movie::factory()->create(['title' => 'Alpha']);
        Movie::factory()->create(['title' => 'Movie']);

        $response = $this->getJson(self::MOVIES_URI . '?sort_by=title&sort_dir=asc')->json('movies');

        $this->assertEquals('Alpha', $response[0]['title']);
        $this->assertEquals('Movie', $response[1]['title']);
        $this->assertEquals('Zebra', $response[2]['title']);
    }

    public function test_index_sorts_by_release_year(): void
    {
        Movie::factory()->create(['release_year' => 2020]);
        Movie::factory()->create(['release_year' => 2010]);
        Movie::factory()->create(['release_year' => 2015]);

        $response = $this->getJson(self::MOVIES_URI . '?sort_by=release_year&sort_dir=asc')->json('movies');

        $this->assertEquals(2010, $response[0]['release_year']);
        $this->assertEquals(2015, $response[1]['release_year']);
        $this->assertEquals(2020, $response[2]['release_year']);
    }

    public function test_index_sorts_by_id(): void
    {
        $movie1 = Movie::factory()->create();
        $movie2 = Movie::factory()->create();
        $movie3 = Movie::factory()->create();

        $response = $this->getJson(self::MOVIES_URI . '?sort_by=id&sort_dir=asc')->json('movies');

        $this->assertEquals($movie1->id, $response[0]['id']);
        $this->assertEquals($movie2->id, $response[1]['id']);
        $this->assertEquals($movie3->id, $response[2]['id']);
    }

    public function test_index_rejects_invalid_sort_by_parameter(): void
    {
        $this->getJson(self::MOVIES_URI . '?sort_by=invalid_field')
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['sort_by']);
    }

    public function test_index_rejects_invalid_sort_direction(): void
    {
        $this->getJson(self::MOVIES_URI . '?sort_dir=sideways')
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['sort_dir']);
    }

    public function test_index_rejects_per_page_less_than_1(): void
    {
        $this->getJson(self::MOVIES_URI . '?per_page=0')
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['per_page']);
    }

    public function test_index_rejects_per_page_exceeding_max(): void
    {
        $maxPerPage = config('api.pagination.max_per_page');

        $this->getJson(self::MOVIES_URI . '?per_page=' . ($maxPerPage + 1))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['per_page']);
    }

    public function test_index_rejects_invalid_page_parameter(): void
    {
        $this->getJson(self::MOVIES_URI . '?page=0')
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['page']);
    }

    public function test_index_accepts_string_numeric_values_for_page_and_per_page(): void
    {
        Movie::factory(15)->create();

        $this->getJson(self::MOVIES_URI . '?page=1&per_page=5')
            ->assertOk()
            ->assertJsonCount(5, 'movies');
    }

    public function test_index_returns_empty_list_when_no_movies_exist(): void
    {
        $this->getJson(self::MOVIES_URI)
            ->assertOk()
            ->assertJsonCount(0, 'movies');
    }

    public function test_index_combines_search_and_sorting(): void
    {
        Movie::factory()->create(['title' => 'Alpha Movie', 'release_year' => 2020]);
        Movie::factory()->create(['title' => 'Zebra Movie', 'release_year' => 2010]);
        Movie::factory()->create(['title' => 'Beta Movie', 'release_year' => 2015]);

        $response = $this->getJson(self::MOVIES_URI . '?search=Movie&sort_by=release_year&sort_dir=asc')
            ->assertOk()
            ->json('movies');

        $this->assertEquals(2010, $response[0]['release_year']);
        $this->assertEquals(2015, $response[1]['release_year']);
        $this->assertEquals(2020, $response[2]['release_year']);
    }

    // ─────────────────────────────────────────────────────────────────
    // GET /api/movies/{slug} - Show Movie Details
    // ─────────────────────────────────────────────────────────────────

    public function test_show_returns_200_with_correct_structure(): void
    {
        $movie = Movie::factory()->create();

        $this->getJson(str_replace('{slug}', $movie->slug, self::MOVIE_SHOW_URI))
            ->assertOk()
            ->assertJsonStructure([
                'movie' => [
                    'id',
                    'title',
                    'slug',
                    'description',
                    'thumbnail_url',
                    'release_year',
                    'genres',
                    'trailer_urls',
                    'embed_urls',
                ],
            ]);
    }

    public function test_show_loads_genres_relation(): void
    {
        $movie = Movie::factory()->create();
        $genres = Genre::factory(3)->create();
        $movie->genres()->attach($genres);

        $response = $this->getJson(str_replace('{slug}', $movie->slug, self::MOVIE_SHOW_URI))
            ->assertOk()
            ->json('movie.genres');

        $this->assertCount(3, $response);
        foreach ($response as $genre) {
            $this->assertArrayHasKey('id', $genre);
            $this->assertArrayHasKey('name', $genre);
            $this->assertArrayHasKey('slug', $genre);
        }
    }

    public function test_show_loads_trailer_urls_relation(): void
    {
        $movie = Movie::factory()->create();

        $response = $this->getJson(str_replace('{slug}', $movie->slug, self::MOVIE_SHOW_URI))
            ->assertOk();

        $this->assertIsArray($response->json('movie.trailer_urls'));
    }

    public function test_show_loads_embed_urls_relation(): void
    {
        $movie = Movie::factory()->create();

        $response = $this->getJson(str_replace('{slug}', $movie->slug, self::MOVIE_SHOW_URI))
            ->assertOk();

        $this->assertIsArray($response->json('movie.embed_urls'));
    }

    public function test_show_returns_correct_movie_data(): void
    {
        $movie = Movie::factory()->create([
            'title' => 'Test Movie',
            'description' => 'Test Description',
            'release_year' => 2023,
        ]);

        $this->getJson(str_replace('{slug}', $movie->slug, self::MOVIE_SHOW_URI))
            ->assertOk()
            ->assertJson([
                'movie' => [
                    'id' => $movie->id,
                    'title' => 'Test Movie',
                    'slug' => $movie->slug,
                    'description' => 'Test Description',
                    'release_year' => 2023,
                ],
            ]);
    }

    public function test_show_returns_404_for_nonexistent_movie(): void
    {
        $this->getJson(str_replace('{slug}', 'nonexistent-slug', self::MOVIE_SHOW_URI))
            ->assertNotFound();
    }

    public function test_show_uses_slug_as_route_key(): void
    {
        $movie = Movie::factory()->create(['title' => 'My Movie']);
        // The slug should be automatically generated from title

        $this->getJson(str_replace('{slug}', $movie->slug, self::MOVIE_SHOW_URI))
            ->assertOk()
            ->assertJsonPath('movie.id', $movie->id);
    }

    public function test_show_does_not_return_sensitive_fields(): void
    {
        $movie = Movie::factory()->create();

        $response = $this->getJson(str_replace('{slug}', $movie->slug, self::MOVIE_SHOW_URI))
            ->assertOk()
            ->json('movie');

        $this->assertArrayNotHasKey('updated_at', $response);
        $this->assertArrayNotHasKey('created_at', $response);
        $this->assertArrayNotHasKey('deleted_at', $response);
    }

    // ─────────────────────────────────────────────────────────────────
    // DELETE /api/movies/{slug} - Delete a Movie
    // ─────────────────────────────────────────────────────────────────

    public function test_destroy_returns_204_no_content_on_success(): void
    {
        $admin = $this->createAdmin();
        $movie = Movie::factory()->create();
        $token = Auth::login($admin->user);

        $this->withToken($token)
            ->deleteJson(str_replace('{slug}', $movie->slug, self::MOVIE_DESTROY_URI))
            ->assertNoContent();
    }

    public function test_destroy_soft_deletes_the_movie(): void
    {
        $admin = $this->createAdmin();
        $movie = Movie::factory()->create();
        $token = Auth::login($admin->user);

        $this->withToken($token)
            ->deleteJson(str_replace('{slug}', $movie->slug, self::MOVIE_DESTROY_URI))
            ->assertNoContent();

        // Movie should still exist in database with deleted_at set
        $this->assertSoftDeleted('movies', ['id' => $movie->id]);
    }

    public function test_destroy_returns_401_without_authentication(): void
    {
        $movie = Movie::factory()->create();

        $this->deleteJson(str_replace('{slug}', $movie->slug, self::MOVIE_DESTROY_URI))
            ->assertUnauthorized();
    }

    public function test_destroy_returns_403_for_non_admin_user(): void
    {
        $user = User::factory()->create();
        $movie = Movie::factory()->create();
        $token = Auth::login($user);

        $this->withToken($token)
            ->deleteJson(str_replace('{slug}', $movie->slug, self::MOVIE_DESTROY_URI))
            ->assertForbidden();
    }

    public function test_destroy_admin_user_can_delete_movie(): void
    {
        $admin = $this->createAdmin();
        $movie = Movie::factory()->create();
        $token = Auth::login($admin->user);

        $this->withToken($token)
            ->deleteJson(str_replace('{slug}', $movie->slug, self::MOVIE_DESTROY_URI))
            ->assertNoContent();

        $this->assertSoftDeleted('movies', ['id' => $movie->id]);
    }

    public function test_destroy_returns_404_for_nonexistent_movie(): void
    {
        $admin = $this->createAdmin();
        $token = Auth::login($admin->user);

        $this->withToken($token)
            ->deleteJson(str_replace('{slug}', 'nonexistent-slug', self::MOVIE_DESTROY_URI))
            ->assertNotFound();
    }

    public function test_destroy_does_not_delete_other_movies(): void
    {
        $admin = $this->createAdmin();
        $movieToDelete = Movie::factory()->create();
        $movieToKeep = Movie::factory()->create();
        $token = Auth::login($admin->user);

        $this->withToken($token)
            ->deleteJson(str_replace('{slug}', $movieToDelete->slug, self::MOVIE_DESTROY_URI))
            ->assertNoContent();

        $this->assertSoftDeleted('movies', ['id' => $movieToDelete->id]);
        $this->assertDatabaseHas('movies', ['id' => $movieToKeep->id, 'deleted_at' => null]);
    }

    public function test_show_does_not_return_soft_deleted_movies(): void
    {
        $movie = Movie::factory()->create();
        $movie->delete();

        $this->getJson(str_replace('{slug}', $movie->slug, self::MOVIE_SHOW_URI))
            ->assertNotFound();
    }

    public function test_index_does_not_return_soft_deleted_movies(): void
    {
        $activeMovie = Movie::factory()->create();
        $deletedMovie = Movie::factory()->create();
        $deletedMovie->delete();

        $response = $this->getJson(self::MOVIES_URI)->json('movies');

        $activeIds = array_map(fn($movie) => $movie['id'], $response);

        $this->assertContains($activeMovie->id, $activeIds);
        $this->assertNotContains($deletedMovie->id, $activeIds);
    }

    // ─────────────────────────────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────────────────────────────

    private function createAdmin(): Admin
    {
        $user = User::factory()->create();

        return Admin::factory()->forUser($user)->create();
    }
}

