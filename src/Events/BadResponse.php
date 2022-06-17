<?php

namespace Actengage\NightWatch\Events;

use Actengage\NightWatch\Response;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BadResponse {
    
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new BadResponse.
     * 
     * Creates a new {@see BadResponse} event with the given {@see \Actengage\NightWatch\Response} instance.
     *
     * @param  \Actengage\NightWatch\Response  $response
     * @return void
     */
    public function __construct(public Response $response)
    {
    }
    
}