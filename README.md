# NightWatch

![PHP Composer](https://github.com/ActiveEngagement/laravel-night-watch/workflows/PHP%20Composer/badge.svg)

**What is NightWatch?**

NightWatch is a package that watches URL's while you are away or sleeping. NightWatch uses a remote Node server to check the status of remote URL's. For example, NightWatch can heck for 404 URLs, or if a Google Tag Manager has been installed correctly.

**What is included in this package?**

This package has its own factories, migrations, models, and scheduling to make tracking status of remote URL's and services easy and configurable.

## Installation

    composer require actengage/night-watch

    php artisan vendor:publish

## Configure the ENV

This is a private application currently, and thus the API endpoint is not included. Include the endpoint in your ENV file.

    NIGHTWATCH_ENDPOINT_URI='https://the.night.watch.url.goes.here'
    
## Kernel Scheduling

NightWatch makes it easy to schedule the commands. Once you have ran the migrations, add the following 1-liner to `app/Console/Kernel.php`.

``` php
<?php

namespace App\Console;

use Actengage\NightWatch\Watcher;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        Watcher::schedule($schedule);
    }
}
```

## Basic Example

Manually start a watcher to check Google's homepage every hour.

``` php
use \Actengage\NightWatch\Watcher;

Watcher::create([
    'url' => 'https://google.com',
    'schedule' => ['hourly']
]);
```

## Using the Watchable Trait

You can easily relate Watchers to your models.

``` php
use Actengage\NightWatch\Watchable;
use Actengage\NightWatch\Watcher;
use Illuminate\Database\Eloquent\Model;

class Url extends Model {
    use Watchable;
    
    protected $fillable = ['url'];

    public static function boot()
    {
        parent::boot();

        static::created(function($model) {
            $watcher = Watcher::create([
                'url' => $model->url
            ]);

            // This is a many to many relationship provided by the trait.
            $model->watchers()->attach($watcher);
        });
    }
}
```