<?php

namespace Actengage\NightWatch;

class NightWatchServiceProvider extends \Illuminate\Support\ServiceProvider {

    /**
     * Register the service.
     * 
     * @return void
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/nightwatch.php', 'nightwatch');

        $this->publishes([
            __DIR__ . '/../config/nightwatch.php' => config_path('nightwatch.php')
        ], 'config');

        $this->publishes([
            __DIR__ . '/../migrations' => database_path('migrations')
        ], 'migrations');
    }

    /**
     * Boot the services.
     * 
     * @return void
     */
    public function boot(): void
    {
        //
    }
}