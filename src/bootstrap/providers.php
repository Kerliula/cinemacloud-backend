<?php

declare(strict_types=1);

$providers = [
    App\Providers\AppServiceProvider::class,
];

$telescopeInstalled = class_exists(\Laravel\Telescope\TelescopeServiceProvider::class);
$telescopeEnabled = env('TELESCOPE_ENABLED', false);

if ($telescopeInstalled && $telescopeEnabled) {
    $providers[] = \Laravel\Telescope\TelescopeServiceProvider::class;
    $providers[] = App\Providers\TelescopeServiceProvider::class;
}

return $providers;
