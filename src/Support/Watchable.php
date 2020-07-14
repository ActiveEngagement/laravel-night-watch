<?php

namespace Actengage\NightWatch\Support;

use Actengage\NightWatch\Watcher;

trait Watchable {

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function watchers()
    {
        return $this->morphToMany(Watcher::class, 'nightwatch_watchable');
    }

}