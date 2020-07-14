<?php

namespace Actengage\NightWatch\Events;

use Actengage\NightWatch\Response;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BadResponse {
    
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The event response.
     * 
     * @return \Actengage\NightWatch\Response
     */
    public $response;

    /**
     * Create a new event instance.
     *
     * @param  \Actengage\NightWatch\Response  $response
     * @return void
     */
    public function __construct(Response $response)
    {
        $this->response = $response;
    }
    
}