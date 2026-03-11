<?php

declare(strict_types=1);

namespace App\Http\Resource;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class UserResource extends JsonResource
{
    public static $wrap = 'user';

    public function __construct(
        User $user,
    ) {
        parent::__construct($user);
    }

    public function toArray(Request $request): array
    {
        /** @var User $user */
        $user = $this->resource;

        return [
            'uuid' => $user->uuid,
            'username' => $user->username,
            'email' => $user->email,
            'created_at' => $user->created_at,
        ];
    }
}
