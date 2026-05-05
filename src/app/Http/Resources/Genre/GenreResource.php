<?php

declare(strict_types=1);

namespace App\Http\Resources\Genre;

use App\Models\Genre;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Genre
 */
final class GenreResource extends JsonResource
{
    public static $wrap = 'genre';

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'name' => $this->name,
        ];
    }
}
