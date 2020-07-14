<?php

namespace Actengage\NightWatch;

use Illuminate\Console\Scheduling\Schedule;

class ServiceProvider extends \Illuminate\Support\ServiceProvider {

    /**
     * Register the service.
     * 
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Boot the services.
     * 
     * @return void
     */
    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__.'/../migrations');

        $this->app->booted(function () {
            $schedule = $this->app->make(Schedule::class);
            $schedule->command('some:command')->everyMinute();
        });
    }

}