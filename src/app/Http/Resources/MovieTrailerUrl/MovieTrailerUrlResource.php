<?php

declare(strict_types=1);

namespace App\Http\Resources\MovieTrailerUrl;

use App\Models\MovieTrailerUrl;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin MovieTrailerUrl
 */
final class MovieTrailerUrlResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'url' => $this->url,
            'provider' => $this->provider,
        ];
    }
}
