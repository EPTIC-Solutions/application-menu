<?php

namespace Eptic\ApplicationMenu;

use Illuminate\Support\ServiceProvider;

class ApplicationMenuServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/application-menu.php', 'application-menu');
    }

    public function register()
    {
        $this->publishes([
            __DIR__ . '/../config/application-menu.php' => config_path('application-menu.php'),
        ], 'config');
    }
}
