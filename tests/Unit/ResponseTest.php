<?php

namespace Actengage\NightWatch\Tests\Unit;

use Actengage\NightWatch\Events\BadResponse;
use Actengage\NightWatch\Response;
use Actengage\NightWatch\Tests\TestCase;
use Actengage\NightWatch\Watcher;
use Illuminate\Support\Facades\Event;

class ResponseTest extends TestCase {

    public function testResponse()
    {
        $this->fakeEvents();

        $response = factory(Response::class)->create();

        $this->assertTrue($response->success);
        $this->assertIsArray($response->response);
        $this->assertInstanceOf(Watcher::class, $response->watcher);

        Event::assertNotDispatched(BadResponse::class);
    }

    public function testBadResponse()
    {
        $this->fakeEvents();

        $response = factory(Response::class)->create([
            'status_code' => 400,
            'success' => false
        ]);

        $this->assertFalse($response->success);

        Event::assertDispatched(BadResponse::class);
    }

}
