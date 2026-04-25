<?php

declare(strict_types=1);
return [
    'pagination' => [
        'default_per_page' => 10,
        'max_per_page'     => 100,
    ],

    'movies' => [
        'sort' => [
            'default'   => 'created_at',
            'direction' => 'desc',
            'allowed'   => [
                'id',
                'title',
                'release_year',
            ],
        ],
    ],
];
