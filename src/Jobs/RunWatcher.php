<?php

namespace Actengage\NightWatch\Jobs;

use Actengage\NightWatch\Watcher;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RunWatcher implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new RunWatcher.
     * 
     * Creates a new {@see RunWatcher} for the given {@see Watcher}.
     *
     * @param  \Actengage\NightWatch\Watcher  $watcher
     * @return void
     */
    public function __construct(protected Watcher $watcher)
    {
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        if ($this->watcher->beforeRun()) {
            $this->watcher->request()->send();
        }
    }
}