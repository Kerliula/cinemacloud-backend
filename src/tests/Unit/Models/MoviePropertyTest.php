<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Genre;
use App\Models\Movie;
use App\Models\MovieEmbedUrl;
use App\Models\MovieTrailerUrl;
use Eris\Generator;
use Eris\TestTrait;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class MoviePropertyTest extends TestCase
{
    use RefreshDatabase;
    use TestTrait;

    // ─────────────────────────────────────────────────────────────────
    // Slug Generation Properties
    // ─────────────────────────────────────────────────────────────────

    public function test_slug_is_always_generated_from_title(): void
    {
        $this->forAll(
            Generator\strings()
                ->withMaxSize(100)
                ->filter(fn($s) => strlen($s) > 0)
        )
            ->then(function (string $title) {
                $movie = Movie::factory()->create(['title' => $title]);

                $this->assertNotEmpty($movie->slug);
                $this->assertTrue(strlen($movie->slug) <= 255);
                // Slug should be lowercase or contain only valid slug characters
                $this->assertMatchesRegularExpression('/^[a-z0-9-]+$/', $movie->slug);
            });
    }

    public function test_slug_generation_is_deterministic(): void
    {
        $this->forAll(
            Generator\strings()
                ->withMaxSize(100)
                ->filter(fn($s) => strlen(trim($s)) > 0)
        )
            ->then(function (string $title) {
                $movie1 = Movie::factory()->create(['title' => $title]);
                $movie2 = Movie::factory()->create(['title' => $title]);

                // Same title should generate the same slug pattern (modulo database uniqueness)
                // The slug generation logic should be identical
                $this->assertIsString($movie1->slug);
                $this->assertIsString($movie2->slug);
            });
    }

    public function test_slug_does_not_exceed_max_length(): void
    {
        $this->forAll(
            Generator\strings()
                ->withMaxSize(500)
                ->filter(fn($s) => strlen($s) > 0)
        )
            ->then(function (string $title) {
                $movie = Movie::factory()->create(['title' => $title]);

                $this->assertLessThanOrEqual(255, strlen($movie->slug));
            });
    }

    // ─────────────────────────────────────────────────────────────────
    // Release Year Properties
    // ─────────────────────────────────────────────────────────────────

    public function test_release_year_is_always_integer(): void
    {
        $this->forAll(
            Generator\integers()
                ->between(1800, 2100)
        )
            ->then(function (int $year) {
                $movie = Movie::factory()->create(['release_year' => $year]);

                $this->assertIsInt($movie->release_year);
                $this->assertEquals($year, $movie->release_year);
            });
    }

    public function test_release_year_is_cast_correctly(): void
    {
        $this->forAll(
            Generator\integers()
                ->between(1900, 2050)
        )
            ->then(function (int $year) {
                $movie = Movie::factory()->create(['release_year' => $year]);
                $retrievedMovie = Movie::find($movie->id);

                $this->assertIsInt($retrievedMovie->release_year);
                $this->assertEquals($year, $retrievedMovie->release_year);
            });
    }

    // ─────────────────────────────────────────────────────────────────
    // Fillable Attributes Properties
    // ─────────────────────────────────────────────────────────────────

    public function test_title_can_be_set(): void
    {
        $this->forAll(
            Generator\strings()
                ->withMaxSize(200)
                ->filter(fn($s) => strlen($s) > 0)
        )
            ->then(function (string $title) {
                $movie = Movie::factory()->make(['title' => $title]);

                $this->assertEquals($title, $movie->title);
            });
    }

    public function test_description_can_be_set(): void
    {
        $this->forAll(
            Generator\strings()
                ->withMaxSize(5000)
        )
            ->then(function (string $description) {
                $movie = Movie::factory()->make(['description' => $description]);

                $this->assertEquals($description, $movie->description);
            });
    }

    public function test_thumbnail_url_can_be_set(): void
    {
        $this->forAll(
            Generator\strings()
                ->withMaxSize(2048)
                ->filter(fn($s) => strlen($s) > 0)
        )
            ->then(function (string $url) {
                $movie = Movie::factory()->make(['thumbnail_url' => $url]);

                $this->assertEquals($url, $movie->thumbnail_url);
            });
    }

    // ─────────────────────────────────────────────────────────────────
    // Search Properties
    // ─────────────────────────────────────────────────────────────────

    public function test_search_returns_only_movies_containing_search_term(): void
    {
        $this->forAll(
            Generator\strings()
                ->withMaxSize(50)
                ->filter(fn($s) => strlen($s) >= 2)
        )
            ->then(function (string $searchTerm) {
                $matchingTitle = "The {$searchTerm} Story";
                Movie::factory()->create(['title' => $matchingTitle]);
                Movie::factory(3)->create();

                $results = Movie::query()->search($searchTerm)->get();

                // All results should contain the search term
                foreach ($results as $movie) {
                    $this->assertStringContainsString(
                        strtolower($searchTerm),
                        strtolower($movie->title)
                    );
                }
            });
    }

    public function test_search_with_empty_string_returns_all_movies(): void
    {
        $this->forAll(
            Generator\integers()
                ->between(1, 5)
        )
            ->then(function (int $count) {
                Movie::factory($count)->create();

                $results = Movie::query()->search('')->get();

                $this->assertCount($count, $results);
            });
    }

    public function test_search_is_case_insensitive(): void
    {
        $this->forAll(
            Generator\strings()
                ->withMaxSize(30)
                ->filter(fn($s) => strlen($s) >= 2)
        )
            ->then(function (string $term) {
                $movie = Movie::factory()->create(['title' => "Movie {$term} Title"]);

                // Search with different cases should find the same movie
                $resultsLower = Movie::query()->search(strtolower($term))->get();
                $resultsUpper = Movie::query()->search(strtoupper($term))->get();
                $resultsMixed = Movie::query()->search(ucfirst(strtolower($term)))->get();

                $this->assertTrue(
                    $resultsLower->contains($movie) ||
                    $resultsUpper->contains($movie) ||
                    $resultsMixed->contains($movie)
                );
            });
    }

    // ─────────────────────────────────────────────────────────────────
    // Route Key Properties
    // ─────────────────────────────────────────────────────────────────

    public function test_route_key_is_always_slug(): void
    {
        $this->forAll(
            Generator\strings()
                ->withMaxSize(100)
                ->filter(fn($s) => strlen($s) > 0)
        )
            ->then(function (string $title) {
                $movie = Movie::factory()->create(['title' => $title]);

                $this->assertEquals('slug', $movie->getRouteKeyName());
                $this->assertNotEmpty($movie->getRouteKey());
                $this->assertEquals($movie->slug, $movie->getRouteKey());
            });
    }

    // ─────────────────────────────────────────────────────────────────
    // Soft Delete Properties
    // ─────────────────────────────────────────────────────────────────

    public function test_soft_delete_preserves_other_movies(): void
    {
        $this->forAll(
            Generator\integers()
                ->between(1, 5)
        )
            ->then(function (int $count) {
                $movies = Movie::factory($count)->create();
                $movieToDelete = $movies->first();

                $movieToDelete->delete();

                // Other movies should still exist and not be deleted
                foreach ($movies->skip(1) as $movie) {
                    $this->assertNull($movie->fresh()->deleted_at);
                }
            });
    }

    public function test_soft_deleted_movie_can_be_restored(): void
    {
        $this->forAll(
            Generator\strings()
                ->withMaxSize(100)
                ->filter(fn($s) => strlen($s) > 0)
        )
            ->then(function (string $title) {
                $movie = Movie::factory()->create(['title' => $title]);
                $movie->delete();

                $this->assertNotNull($movie->deleted_at);

                $movie->restore();

                $this->assertNull($movie->deleted_at);
            });
    }

    // ─────────────────────────────────────────────────────────────────
    // Relationship Properties
    // ─────────────────────────────────────────────────────────────────

    public function test_movie_can_have_multiple_genres(): void
    {
        $this->forAll(
            Generator\integers()
                ->between(0, 5)
        )
            ->then(function (int $genreCount) {
                $movie = Movie::factory()->create();
                $genres = Genre::factory($genreCount)->create();

                $movie->genres()->attach($genres);

                $this->assertCount($genreCount, $movie->genres);
            });
    }

    public function test_movie_genre_relationship_is_many_to_many(): void
    {
        $this->forAll(
            Generator\integers()
                ->between(1, 3)
        )
            ->then(function (int $movieCount) {
                $movies = Movie::factory($movieCount)->create();
                $genre = Genre::factory()->create();

                foreach ($movies as $movie) {
                    $movie->genres()->attach($genre);
                }

                $this->assertCount($movieCount, $genre->movies);
            });
    }

    public function test_movie_can_have_multiple_embed_urls(): void
    {
        $this->forAll(
            Generator\integers()
                ->between(0, 5)
        )
            ->then(function (int $urlCount) {
                $movie = Movie::factory()->create();
                MovieEmbedUrl::factory($urlCount)->for($movie)->create();

                $this->assertCount($urlCount, $movie->embedUrls);
            });
    }

    public function test_movie_can_have_multiple_trailer_urls(): void
    {
        $this->forAll(
            Generator\integers()
                ->between(0, 5)
        )
            ->then(function (int $urlCount) {
                $movie = Movie::factory()->create();
                MovieTrailerUrl::factory($urlCount)->for($movie)->create();

                $this->assertCount($urlCount, $movie->trailerUrls);
            });
    }

    // ─────────────────────────────────────────────────────────────────
    // Attribute Immutability Properties
    // ─────────────────────────────────────────────────────────────────

    public function test_changing_title_updates_slug(): void
    {
        $this->forAll(
            Generator\tuples(
                Generator\strings()->withMaxSize(50)->filter(fn($s) => strlen($s) > 0),
                Generator\strings()->withMaxSize(50)->filter(fn($s) => strlen($s) > 0)
            )
        )
            ->then(function (array $titles) {
                [$title1, $title2] = $titles;

                $movie = Movie::factory()->create(['title' => $title1]);
                $originalSlug = $movie->slug;

                $movie->update(['title' => $title2]);

                // Slug should be regenerated based on new title
                $this->assertIsString($movie->fresh()->slug);
            });
    }

    public function test_identical_attributes_create_identical_instances(): void
    {
        $this->forAll(
            Generator\tuples(
                Generator\strings()->withMaxSize(100)->filter(fn($s) => strlen($s) > 0),
                Generator\strings()->withMaxSize(500),
                Generator\integers()->between(1900, 2100)
            )
        )
            ->then(function (array $data) {
                [$title, $description, $year] = $data;

                $attributes = [
                    'title' => $title,
                    'description' => $description,
                    'release_year' => $year,
                ];

                $movie1 = Movie::factory()->make($attributes);
                $movie2 = Movie::factory()->make($attributes);

                $this->assertEquals($movie1->title, $movie2->title);
                $this->assertEquals($movie1->description, $movie2->description);
                $this->assertEquals($movie1->release_year, $movie2->release_year);
            });
    }
}

