<?php

declare(strict_types=1);

return [
    'auth' => [
        'max_attempts' => (int)env('AUTH_RATE_LIMIT_MAX_ATTEMPTS', 5),
        'decay_minutes' => (int)env('AUTH_RATE_LIMIT_DECAY_MINUTES', 1),
    ],
];
