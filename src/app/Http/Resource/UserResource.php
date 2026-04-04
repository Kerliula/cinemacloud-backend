<?php

declare(strict_types=1);

namespace App\Http\Resource;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin User
 */
final class UserResource extends JsonResource
{
    public static $wrap = 'user';

    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->resource->uuid,
            'username' => $this->resource->username,
            'email' => $this->resource->email,
        ];
    }
}
