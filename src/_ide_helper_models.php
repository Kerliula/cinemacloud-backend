<?php

// @formatter:off
// phpcs:ignoreFile
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models{
/**
 * @property int $user_id
 * @property-read \App\Models\User $user
 * @method static \Database\Factories\AdminFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Admin newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Admin newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Admin query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Admin whereUserId($value)
 */
	final class Admin extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string|null $slug
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Movie> $movies
 * @property-read int|null $movies_count
 * @method static \Database\Factories\GenreFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Genre newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Genre newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Genre query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Genre whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Genre whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Genre whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Genre whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Genre whereUpdatedAt($value)
 */
	final class Genre extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $title
 * @property string|null $slug
 * @property string|null $description
 * @property int $release_year
 * @property string|null $thumbnail_url
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\MovieEmbedUrl> $embedUrls
 * @property-read int|null $embed_urls_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Genre> $genres
 * @property-read int|null $genres_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\MovieTrailerUrl> $trailerUrls
 * @property-read int|null $trailer_urls_count
 * @method static \Database\Factories\MovieFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Movie newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Movie newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Movie onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Movie query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Movie search(?string $search)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Movie whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Movie whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Movie whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Movie whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Movie whereReleaseYear($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Movie whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Movie whereThumbnailUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Movie whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Movie whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Movie withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Movie withoutTrashed()
 */
	final class Movie extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $movie_id
 * @property string $url
 * @property string $provider
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Movie $movie
 * @method static \Database\Factories\MovieEmbedUrlFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MovieEmbedUrl forMovie(\App\Models\Movie $movie)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MovieEmbedUrl newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MovieEmbedUrl newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MovieEmbedUrl query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MovieEmbedUrl whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MovieEmbedUrl whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MovieEmbedUrl whereMovieId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MovieEmbedUrl whereProvider($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MovieEmbedUrl whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MovieEmbedUrl whereUrl($value)
 */
	final class MovieEmbedUrl extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $movie_id
 * @property string $url
 * @property string $provider
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Movie $movie
 * @method static \Database\Factories\MovieTrailerUrlFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MovieTrailerUrl forMovie(\App\Models\Movie $movie)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MovieTrailerUrl newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MovieTrailerUrl newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MovieTrailerUrl query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MovieTrailerUrl whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MovieTrailerUrl whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MovieTrailerUrl whereMovieId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MovieTrailerUrl whereProvider($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MovieTrailerUrl whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MovieTrailerUrl whereUrl($value)
 */
	final class MovieTrailerUrl extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $username
 * @property string $email
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Admin|null $admin
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUsername($value)
 */
	final class User extends \Eloquent implements \PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject {}
}

