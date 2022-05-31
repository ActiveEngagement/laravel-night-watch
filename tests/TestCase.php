<?php

namespace Actengage\NightWatch\Tests;

use Actengage\NightWatch\NightWatchServiceProvider;
use Orchestra\Testbench\Concerns\WithLaravelMigrations;
use Illuminate\Support\Facades\Event;
use Actengage\NightWatch\Events\BadResponse;

class TestCase extends \Orchestra\Testbench\TestCase
{
    use WithLaravelMigrations;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        $this->withFactories(__DIR__.'/../factories');

        $this->loadLaravelMigrations();
        $this->loadMigrationsFrom(__DIR__.'/../migrations');
        $this->loadMigrationsFrom(__DIR__.'/migrations');

        $this->artisan('migrate', [
            '--database' => 'testbench'
        ]);
    }

    protected function getPackageProviders($app)
    {
        return [
            NightWatchServiceProvider::class
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);

        $app['config']->set('services.messagegears', [
            'api_key' => 'API_KEY',
            'account_id' => 'ACCOUNT_ID',
            'campaign_id' => 'CAMPAIGN_ID'
        ]);
    }

    protected function fakeEvents()
    {
        Event::fake([
          BadResponse::class
        ]);
    }
}
