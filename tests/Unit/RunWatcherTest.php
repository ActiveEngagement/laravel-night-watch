<?php

namespace Actengage\NightWatch\Tests\Unit;

use Actengage\NightWatch\Tests\Url;
use Actengage\NightWatch\Jobs\RunWatcher;
use Actengage\NightWatch\RequestBuilder;
use Actengage\NightWatch\Response;
use Actengage\NightWatch\Tests\TestCase;
use Actengage\NightWatch\Watcher;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response as Psr7Response;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Queue;

class RunWatcherTest extends TestCase {

    public function testRunWatcher()
    {
        $handler = new MockHandler([
            new Psr7Response(200, [], json_encode([
                'success' => true
            ]))
        ]);

        $mock = $this->createMock(Watcher::class);

        $builder = new RequestBuilder($mock);

        $mock->method('request')->willReturn($builder);
        $mock->method('beforeRun')->willReturn(true);
        
        $builder->client([
            'handler' => $handler
        ]);

        $job = new RunWatcher($mock);
        $job->handle();

        $this->assertNotNull($handler->getLastRequest());
    }
}