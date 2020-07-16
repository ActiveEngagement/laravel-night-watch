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
     * Append the active scope.
     * 
     * @return void
     */
    public function scopeActive($query)
    {
        $query->hasBegun()->hasNotEnded();
    }

    /**
     * Append the inactive scope.
     * 
     * @return void
     */
    public function scopeInactive($query)
    {
        $query->where(function($q) {
            $q->orWhere(function($q) {
                $q->hasNotBegun();
            });
        
            $q->orWhere(function($q) {
                $q->hasEnded();
            });
        });
    }

    /**
     * Append the has begun scope.
     * 
     * @return void
     */
    public function scopeHasBegun($query)
    {
        $query->where(function($q) {
            $q->whereNull('begins_at');
            $q->orWhereNotNull('begins_at');
            $q->where('begins_at', '<=', now());
        });
    }

    /**
     * Append the has not begun scope.
     * 
     * @return void
     */
    public function scopeHasNotBegun($query)
    {
        $query->where(function($q) {
            $q->whereNotNull('begins_at');
            $q->where('begins_at', '>', now());
        });
    }

    /**
     * Append the has ended scope.
     * 
     * @return void
     */
    public function scopeHasEnded($query)
    {
        $query->where(function($q) {
            $q->whereNotNull('ends_at');
            $q->where('ends_at', '<', now());
        });
    }

    /**
     * Append the has not ended scope.
     * 
     * @return void
     */
    public function scopeHasNotEnded($query)
    {
        $query->where(function($q) {
            $q->whereNull('ends_at');
            $q->orWhereNotNull('ends_at');
            $q->where('ends_at', '>=', now());
        });
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
     * Checks to see if the watcher is active.
     * 
     * @return bool
     */
    public function isActive()
    {
        if ($this->begins_at && $this->begins_at->isFuture() ||
            $this->ends_at && $this->ends_at->isPast()) {
            return false;
        }

        return true;
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
        RunWatcher::dispatch($this);

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
            $event = $schedule->job(new RunWatcher($model));
            
            foreach($model->schedule as $args) {
                if(!is_array($args)) {
                    $args = [$args];
                }

                if(count($method = array_splice($args, 0, 1))) {
                    $event->{$method[0]}(...$args);
                }
            }
        });
    }
}