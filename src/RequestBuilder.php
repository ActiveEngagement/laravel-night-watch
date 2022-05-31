<?php

namespace Actengage\NightWatch;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Contracts\Support\Arrayable;

class RequestBuilder implements Arrayable {

    /**
     * The calls attribute.
     * 
     * @var array
     */
    protected $calls = [];
    
    /**
     * The global Guzzle client.
     * 
     * @var \GuzzleHttp\Client
     */
    protected $client;

    /**
     * The listen attribute.
     * 
     * @var array|null
     */
    protected $listen = null;

    /**
     * The url attribute.
     * 
     * @var array|null
     */
    protected $url = null;

    /**
     * The request watcher.
     * 
     * @var \Actengage\NightWatch\Watcher
     */
    protected $watcher;

    /**
     * Construct the RequestBulder.
     * 
     * @param  \Actengage\NightWatch\Watcher  $watcher
     * @return void
     */
    public function __construct(Watcher $watcher)
    {
        $this->watcher = $watcher;
        $this->calls = $watcher->calls;
        $this->listen = $watcher->listen;
        $this->url = $watcher->url;
    }

    /**
     * Define the API base endpoint URI.
     * 
     * @return string|null
     */
    public function baseUri()
    {
        return config('nightwatch.base_uri');
    }

    /**
     * Get/set the calls attribute.
     * 
     * @param  array|null  $calls
     * @return array|this
     */
    public function calls(array $calls = null)
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
     * @param  array  $client
     * @return \GuzzleHttp\Client
     */
    public function client($config = null): Client
    {
        $mergedConfig = array_merge((
            $this->client ? $this->client->getConfig() : []
        ), (
            is_array($config) ? $config : []
        ));

        if(!$this->client || $config) {
            $this->client = new Client($mergedConfig);
        }

        return $this->client;
    }

    /**
     * Get/set the listen attribute.
     * 
     * @param  array|null  $listen
     * @return array|this
     */
    public function listen(array $listen = null)
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
     * @param  string|null  $url
     * @return string|this
     */
    public function url(string $url = null)
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
     * @param  \Actengage\NightWatch\Watcher|null  $watcher
     * @return \Actengage\NightWatch\Watcher|this
     */
    public function watcher(Watcher $watcher = null)
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
    public function toArray()
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
    public function send()
    {
        try {
            $response = $this->client()->post($this->baseUri() ?? '', [
                'json' => $this->toArray()
            ]);
        }
        catch(ClientException $e) {
            $response = $e->getResponse();
        }

        return $this->watcher->response($response);
    }
}
