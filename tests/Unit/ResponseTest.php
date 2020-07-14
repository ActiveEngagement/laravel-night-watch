<?php

namespace Actengage\NightWatch\Tests\Unit;

use Actengage\NightWatch\Events\BadResponse;
use Actengage\NightWatch\Response;
use Actengage\NightWatch\Tests\TestCase;
use Actengage\NightWatch\Watcher;

class ResponseTest extends TestCase {

    public function testResponse()
    {
        $this->doesntExpectEvents(BadResponse::class);

        $response = factory(Response::class)->create();

        $this->assertTrue($response->success);
        $this->assertIsArray($response->response);
        $this->assertInstanceOf(Watcher::class, $response->watcher);
    }

    public function testBadResponse()
    {
        $this->expectsEvents(BadResponse::class);

        $response = factory(Response::class)->create([
            'status_code' => 400,
            'success' => false
        ]);

        $this->assertFalse($response->success);
    }

}