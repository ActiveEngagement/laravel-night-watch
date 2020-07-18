<?php

namespace Actengage\NightWatch\Tests\Unit;

use Actengage\NightWatch\RequestBuilder;
use Actengage\NightWatch\Tests\TestCase;
use Actengage\NightWatch\Watcher;
use GuzzleHttp\Client;
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

}