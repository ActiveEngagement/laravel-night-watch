<?php

return [
    
    /*
    |--------------------------------------------------------------------------
    | Base URI
    |--------------------------------------------------------------------------
    |
    | This option defines the base_uri for the Guzzle client to send to the
    | NightWatch REST API. Because NightWatch is a private application, the
    | the endpoint url is kept hidden in the ENV scope.
    |
    */

    'base_uri' => env('NIGHTWATCH_ENDPOINT_URI')
];