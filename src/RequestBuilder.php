<?php

namespace Actengage\NightWatch;

use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Contracts\Support\Arrayable;

class RequestBuilder implements Arrayable {

    /**
     * The calls attribute.
     * 
     * @var ?array
     */
    protected ?array $calls = [];
    
    /**
     * The global Guzzle client.
     * 
     * @var \GuzzleHttp\Client
     */
    protected ?Client $client = null;

    /**
     * The current Guzzle confinguration.
     * 
     * This property is necessary because the configuration of a Guzzle client is inaccessible and thus needs to be
     * stored elsewhere.
     * 
     * @var array
     */
    protected array $clientConfig = [];

    /**
     * The listen attribute.
     * 
     * @var ?array
     */
    protected ?array $listen = null;

    /**
     * The url attribute.
     * 
     * @var ?string
     */
    protected ?string $url = null;

    /**
     * Construct the RequestBulder.
     * 
     * @param  \Actengage\NightWatch\Watcher  $watcher  the {@see Watcher} for which to build requests.
     * @return void
     */
    public function __construct(protected Watcher $watcher)
    {
        $this->calls = $watcher->calls;
        $this->listen = $watcher->listen;
        $this->url = $watcher->url;
    }

    /**
     * Define the API base endpoint URI.
     * 
     * @return ?string
     */
    public function baseUri(): ?string
    {
        return config('nightwatch.base_uri');
    }

    /**
     * Get/set the calls attribute.
     * 
     * @param  ?array  $calls
     * @return array|this
     */
    public function calls(?array $calls = null): array|static
    {
        if(is_null($calls)) {
            return $this->calls;
        }

        $this->calls = $calls;

        return $this;
    }

    /**
     * Get the Guzzle client.
     * 
     * @param  ?array  $config
     * @return \GuzzleHttp\Client
     */
    public function client(?array $config = null): Client
    {
        if ($this->client && !$config) {
            return $this->client;
        }

        $this->clientConfig = array_merge($this->clientConfig, $config ?? []);
        $this->client = new Client($this->clientConfig);

        return $this->client;
    }

    /**
     * Get/set the listen attribute.
     * 
     * @param  ?array  $listen
     * @return array|this
     */
    public function listen(?array $listen = null): array|static
    {
        if(is_null($listen)) {
            return $this->listen;
        }

        $this->listen = $listen;

        return $this;
    }

    /**
     * Get/set the url attribute.
     * 
     * @param  ?string  $url
     * @return string|this
     */
    public function url(?string $url = null): string|static
    {
        if(is_null($url)) {
            return $this->url;
        }

        $this->url = $url;

        return $this;
    }

    /**
     * Get/set the watcher attribute.
     * 
     * @param  ?\Actengage\NightWatch\Watcher  $watcher
     * @return \Actengage\NightWatch\Watcher|this
     */
    public function watcher(?Watcher $watcher = null): Watcher|static
    {
        if(is_null($watcher)) {
            return $this->watcher;
        }

        $this->watcher = $watcher;

        return $this;
    }

    /**
     * Cast to an array.
     * 
     * @return array
     */
    public function toArray(): array
    {
        return array_filter([
            'url' => $this->url,
            'calls' => $this->calls,
            'listen' => $this->listen
        ]);
    }

    /**
     * Send the HTTP request.
     * 
     * @return \Actengage\NightWatch\Response
     */
    public function send(): Response
    {
        $this->watcher->update(['begins_at' => Carbon::now(), 'ends_at' => null]);

        try
        {
            $response = $this->client()->post($this->baseUri() ?? '', [
                'json' => $this->toArray()
            ]);
        } catch(ClientException $e)
        {
            $response = $e->getResponse();
        } finally
        {
            $this->watcher->update(['ends_at' => Carbon::now()]);
        }

        return $this->watcher->response($response);
    }
}
