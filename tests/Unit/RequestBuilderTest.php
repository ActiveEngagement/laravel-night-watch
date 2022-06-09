<?php

namespace Actengage\NightWatch\Tests\Unit;

use Actengage\NightWatch\RequestBuilder;
use Actengage\NightWatch\Tests\TestCase;
use Actengage\NightWatch\Watcher;
use Carbon\Carbon;
use Carbon\CarbonInterval;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;

class RequestBuilderTest extends TestCase {
    public function testBuildingRequest()
    {
        $watcher = factory(Watcher::class)->create();

        $builder = new RequestBuilder($watcher);

        // Test baseUri()
        $this->assertNull($builder->baseUri());

        config()->set('nightwatch.base_uri', $baseUri = 'https://test.com/test');

        $this->assertEquals($baseUri, $builder->baseUri());

        // Test the watcher property
        $this->assertEquals($watcher, $builder->watcher());
        $this->assertInstanceOf(RequestBuilder::class, $builder->watcher($watcher));
        $this->assertEquals($watcher, $builder->watcher());

        // Test the url property
        $this->assertEquals($watcher->url, $builder->url());
        $this->assertInstanceOf(RequestBuilder::class, $builder->url($url = 'https://test.org'));
        $this->assertEquals($url, $builder->url());

        // Test the calls property
        $this->assertEquals([], $builder->calls());
        $this->assertInstanceOf(RequestBuilder::class, $builder->calls($calls = [['waitUntiSelector', ['.test', ['timeout' => 3000]]]]));
        $this->assertEquals($calls, $builder->calls());

        // Test the listen property
        $this->assertEquals([], $builder->listen());
        $this->assertInstanceOf(RequestBuilder::class, $builder->listen($listen = ['https://test.com']));
        $this->assertEquals($listen, $builder->listen());

        // Test the toArray() method.
        $this->assertIsArray($builder->toArray());

        // Test the client() method.
        $this->assertInstanceOf(Client::class, $builder->client());

        $mock = new MockHandler([
            new Response(200, [], json_encode([
                'success' => true
            ]))
        ]);

        $builder->client([
            'handler' => $mock
        ]);

        $response = $builder->send();

        $this->assertInstanceOf(\Actengage\NightWatch\Response::class, $response);
        $this->assertEquals(200, $response->status_code);
    }

    /**
     * @dataProvider data__send__setsBeginsAtAndEndsAt
     */
    public function test__send__setsBeginsAtAndEndsAt(int $status, bool $success) {
        $watcher = factory(Watcher::class)->create();
        $builder = new RequestBuilder($watcher);
        // createWithMiddleware() is necessary in order to get Guzzle to raise client/server exceptions.
        $handler = MockHandler::createWithMiddleware([
            new Response($status, [], json_encode([
                'success' => $success
            ]))
        ]);
        $builder->client([
            'handler' => $handler,
            'delay' => 2500
        ]);
        $began = Carbon::now();
        try
        {
            $builder->send();
        } catch (ServerException)
        {
        }
        $ended = Carbon::now();

        $this->assertNotNull($watcher->begins_at);
        $this->assertNotNull($watcher->ends_at);

        $this->assertCarbonsEqualWithDelta($began, $watcher->begins_at, CarbonInterval::second(1));
        $this->assertCarbonsEqualWithDelta($ended, $watcher->ends_at, CarbonInterval::second(1));
    }

    public function data__send__setsBeginsAtAndEndsAt() {
        return [
            [200, true],
            [400, false],
            [500, false]
        ];
    }
}