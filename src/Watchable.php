<?php

namespace Actengage\NightWatch;

use Actengage\NightWatch\Watcher;

trait Watchable {
    
    /**
     * Get the watcher class name.
     * 
     * @return string;
     */
    public function getWatcherClassName()
    {
        return Watcher::class;
    }

    /**
     * Define watchers relationship.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function watchers()
    {
        return $this->morphToMany(
            $this->getWatcherClassName(), 'watchable', 'nightwatch_watchables'
        );
    }

}