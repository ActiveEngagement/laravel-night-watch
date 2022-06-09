<?php

namespace Actengage\NightWatch\Tests\Unit;

use Actengage\NightWatch\Tests\Url;
use Actengage\NightWatch\Jobs\RunWatcher;
use Actengage\NightWatch\RequestBuilder;
use Actengage\NightWatch\Response;
use Actengage\NightWatch\Tests\TestCase;
use Actengage\NightWatch\Watcher;
use Carbon\Carbon;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response as Psr7Response;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Queue;

class WatcherTest extends TestCase {

    public function testWatcher()
    {
        $watcher = factory(Watcher::class)->create([
            'schedule' => [
                ['everyTwoMinutes']
            ]
        ]);

        // Test the calls attribute.
        $this->assertIsArray($watcher->calls);
        $this->assertCount(0, $watcher->calls);

        $watcher->calls = [['waitForSelector', ['.test', ['timeout' => 3000]]]];

        $this->assertCount(1, $watcher->calls);

        // Test the listen attribute.
        $this->assertIsArray($watcher->listen);
        $this->assertCount(0, $watcher->listen);

        $watcher->listen = ['https://test.com'];

        $this->assertCount(1, $watcher->listen);

        // Test responses()
        $this->assertInstanceOf(HasMany::class, $watcher->responses());
        $this->assertCount(0, $watcher->responses);
        $this->assertNull($watcher->lastResponse());

        $response = new \GuzzleHttp\Psr7\Response(200, [], json_encode([
            'success' => true
        ]));
        
        $watcher->response($response);
        $watcher->refresh();

        $this->assertCount(1, $watcher->responses);
        $this->assertNotNull($watcher->lastResponse());
    }

    public function testWatcherRequest()
    {
        $watcher = factory(Watcher::class)->create($body = [
            'url' => 'https://test.com',
            'calls' => [
                ['waitForSelector', ['.test', ['timeout' => 3000]]]
            ],
            'listen' => [
                'https://test.com'
            ]
        ]);

        $builder = $watcher->request();

        $this->assertInstanceOf(RequestBuilder::class, $builder);
        $this->assertEquals($body, $watcher->request()->toArray());

        $mock = new MockHandler([
            new Psr7Response(200, [], json_encode([
                'success' => true
            ]))
        ]);

        $builder->client([
            'handler' => $mock
        ]);

        $response = $builder->send();

        $this->assertInstanceOf(Response::class, $response);
    }

    public function testWatcherScheduler()
    {
        Queue::fake();
        
        $watcher = factory(Watcher::class)->create($body = [
            'url' => 'https://test.com',
            'schedule' => ['hourly']
        ]);
        
        Watcher::schedule();

        $this->artisan('schedule:run');
        
        Queue::assertNothingPushed();
        
        $watcher = factory(Watcher::class)->create($body = [
            'url' => 'https://test.com'
        ]);
        
        Watcher::schedule();

        $this->artisan('schedule:run');
        
        Queue::assertPushed(RunWatcher::class);
    }

    public function testWatchables()
    {
        $watcher = factory(Watcher::class)->create($body = [
            'url' => 'https://test.com'
        ]);
        
        $url = Url::create([
            'url' => 'https://www.test.com'
        ]);

        $url->watchers()->sync($watcher);
        
        $this->assertCount(1, $url->watchers);
    }

    public function test__schedule__includesCurrentlyRunning() {
        Queue::fake();

        factory(Watcher::class)->create([
            'begins_at' => Carbon::now()->subSecond()
        ]);

        Watcher::schedule();
        $this->artisan('schedule:run');

        Queue::assertPushed(RunWatcher::class);
        $this->assertCount(1, app(Schedule::class)->events());
    }

    /**
     * This test is for historical purposes, since previous versions attempted to prevent running watchers from
     * executing.
     */
    public function test__schedule__includesCurrentlyNotRunning() {
        Queue::fake();

        factory(Watcher::class)->create([
            'begins_at' => Carbon::now()->addSecond()
        ]);

        Watcher::schedule();
        $this->artisan('schedule:run');

        Queue::assertPushed(RunWatcher::class);
        $this->assertCount(1, app(Schedule::class)->events());
    }

    public function test__schedule__excludesInactive() {
        Queue::fake();

        factory(Watcher::class)->create([
            'active' => false
        ]);

        Watcher::schedule();
        $this->artisan('schedule:run');

        Queue::assertNothingPushed();
        $this->assertEmpty(app(Schedule::class)->events());
    }
}