<?php

declare(strict_types=1);

namespace App\Http\Resources\MovieEmbedUrl;

use App\Models\MovieEmbedUrl;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin MovieEmbedUrl
 */
final class MovieEmbedUrlResource extends JsonResource
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
