<?php

use EragLaravelDisposableEmail\LaravelDisposableEmailServiceProvider;

return [
    App\Providers\AppServiceProvider::class,
    App\Providers\Filament\AdminPanelProvider::class,
    LaravelDisposableEmailServiceProvider::class,
];
