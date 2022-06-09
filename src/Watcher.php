<?php

namespace Actengage\NightWatch;

use Actengage\NightWatch\Jobs\RunWatcher;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class Watcher extends Model {

    /**
     * The table associated with the model.
     * 
     * @var string
     */
    protected $table = 'nightwatch_watchers';

    /**
     * The fillable attributes.
     * 
     * @var array
     */
    protected $fillable = [
        'url', 'request', 'begins_at', 'ends_at'
    ];

    /**
     * The default attributes.
     * 
     * @var string
     */
    protected $attributes = [
        'request' => '{}',
        'schedule' => '[]'
    ];

    /**
     * The attributes that are cast.
     * 
     * @var string
     */
    protected $casts = [
        'request' => 'array',
        'schedule' => 'array'
    ];

    /**
     * The attributes that are dates.
     * 
     * @var string
     */
    protected $dates = [
        'begins_at', 'ends_at'
    ];

    /**
     * Get the request calls attribute.
     * 
     * @return array|null
     */
    public function getCallsAttribute()
    {
        return Arr::get($this->request, 'calls', []);
    }

    /**
     * Set the calls attribute.
     * 
     * @return void
     */
    public function setCallsAttribute(array $value)
    {
        $this->request = array_merge([], $this->request, ['calls' => $value]);
    }

    /**
     * Get the request listen attribute.
     * 
     * @return array|null
     */
    public function getListenAttribute()
    {
        return Arr::get($this->request, 'listen', []);
    }

    /**
     * Set the listen attribute.
     * 
     * @return void
     */
    public function setListenAttribute(array $value)
    {
        $this->request = array_merge([], $this->request, ['listen' => $value]);
    }

    /**
     * Set the url attribute.
     * 
     * @return void
     */
    public function setUrlAttribute(string $value = null)
    {
        $this->request = array_merge([], $this->request, [
            'url' => $this->attributes['url'] = $value
        ]);
    }

    /**
     * The "active" scope.
     * 
     * Gets a scope with all "active" watchers. Active watchers are simply those whose "active" column is truthy. This
     * is different than in previous versions where an "active" watcher was one that was currently running.
     * 
     * Please note that a watcher's activation status is solely in relation to scheduling. An "inactive" watcher may of
     * course still be run manually.
     * 
     * This scope is syntactic sugar for `->where('active', true)`.
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * The "inactive" scope.
     * 
     * Gets a scope with all non-"active" watchers. Active watchers are simply those whose "active" column is truthy.
     * This is different than in previous versions where an "active" watcher was one that was currently running.
     * 
     * Please note that a watcher's activation status is solely in relation to scheduling. An "inactive" watcher may of
     * course still be run manually.
     * 
     * This scope is syntactic sugar for `->where('active', false)`.
     */
    public function scopeInactive($query)
    {
        return $query->where('active', false);
    }

    /**
     * The responses assiociated with this watcher.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany;
     */
    public function responses()
    {
        return $this->hasMany(Response::class)->orderBy('id', 'desc');
    }

    /**
     * The last responses assiociated with this watcher.
     * 
     * @return \Actengage\NightWatch\Response|null
     */
    public function lastResponse()
    {
        return $this->responses()->first();
    }

    /**
     * Instantiate a new request.
     * 
     * @return \Actengage\NightWatch\RequestBuilder
     */
    public function request()
    {
        return new RequestBuilder($this);
    }

    /**
     * Record a database response from a Guzzle response.
     * 
     * @param  \GuzzleHttp\Psr7\Response  $response
     * @return this
     */
    public function response(\GuzzleHttp\Psr7\Response $response)
    {
        $body = json_decode($response->getBody(), true);

        return $this->responses()->create([
            'response' => $body,
            'status_code' => $response->getStatusCode(),
            'success' => Arr::get($body, 'success'),
            'message' => Arr::get($body, 'message'),
        ]);
    }

    /**
     * Run the watcher manually.
     * 
     * @return this
     */
    public function run()
    {
        if($this->shouldRun()) {
            RunWatcher::dispatch($this);
        }

        return true;
    }

    /**
     * Should the wathcher run.
     * 
     * @return bool
     */
    public function shouldRun()
    {
        return true;
    }

    /**
     * Schedule the watchers.
     * 
     * @param  \Illuminate\Console\Scheduling\Schedule
     * @return \Illuminate\Support\Collection
     */
    public static function schedule(Schedule $schedule = null)
    {
        $schedule = $schedule ?: app(Schedule::class);

        return static::active()->each(function($model) use ($schedule) {
            if($model->shouldRun()) {
                $event = $schedule->job(new RunWatcher($model));
                
                if(is_array($model->schedule)) {
                    foreach($model->schedule as $args) {
                        if(!is_array($args)) {
                            $args = [$args];
                        }

                        if(count($method = array_splice($args, 0, 1))) {
                            $event->{$method[0]}(...$args);
                        }
                    }
                }
            }
        });
    }
}