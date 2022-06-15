<?php

namespace Actengage\NightWatch;

use Actengage\NightWatch\Watcher;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

trait Watchable {
    
    /**
     * Get the watcher class name.
     * 
     * @return string;
     */
    public function getWatcherClassName(): string
    {
        return Watcher::class;
    }

    /**
     * Define watchers relationship.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function watchers(): MorphToMany
    {
        return $this->morphToMany(
            $this->getWatcherClassName(), 'watchable', 'nightwatch_watchables'
        );
    }

}