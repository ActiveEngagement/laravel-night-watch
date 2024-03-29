<?php

namespace Actengage\NightWatch;

use Actengage\NightWatch\Jobs\RunWatcher;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

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
        'url', 'request', 'begins_at', 'ends_at', 'active'
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
        'request' => 'collection',
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
     * Gets and sets the `calls` attribute.
     * 
     * Implements a Laravel custom mutator and accessor for the `calls` attribute, by retrieving and setting the
     * `'calls'` key on the request attribute.
     * 
     * @return Attribute
     */
    protected function calls(): Attribute
    {
        return $this->collectionAttribute('request', 'calls');
    }

    /**
     * Gets and sets the `calls` attribute.
     * 
     * Implements a Laravel custom mutator and accessor for the `calls` attribute, by retrieving and setting the
     * `'calls'` key on the request attribute.
     * 
     * @return Attribute
     */
    protected function listen(): Attribute
    {
        return $this->collectionAttribute('request', 'listen');
    }

    /**
     * Sets the `url` attribute.
     * 
     * Implements a Laravel custom mutator for the `url` attribute that saved the value to a `'url'` key on the request
     * attribute *as well as* the `url` column itself.
     * 
     * @return Attribute
     */
    protected function url(): Attribute
    {
        return Attribute::make(
            set: fn ($value) => [
                'url' => $value,
                'request' => $this->request->put('url', $value)
            ]
        );
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
     * 
     * @return void
     */
    public function scopeActive($query): void
    {
        $query->where('active', true);
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
     * 
     * @return void
     */
    public function scopeInactive($query): void
    {
        $query->where('active', false);
    }

    /**
     * The responses assiociated with this watcher.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany;
     */
    public function responses(): HasMany
    {
        return $this->hasMany(Response::class)->orderBy('id', 'desc');
    }

    /**
     * The last responses assiociated with this watcher.
     * 
     * @return ?\Actengage\NightWatch\Response
     */
    public function lastResponse(): ?Response
    {
        return $this->responses()->first();
    }

    /**
     * Instantiate a new request.
     * 
     * @return \Actengage\NightWatch\RequestBuilder
     */
    public function request(): RequestBuilder
    {
        return new RequestBuilder($this);
    }

    /**
     * Record a database response from a Guzzle response.
     * 
     * @param  \GuzzleHttp\Psr7\Response  $response
     * @return \Actengage\NightWatch\Response
     */
    public function response(\GuzzleHttp\Psr7\Response $response): Response
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
     * @return bool
     */
    public function run(): bool
    {
        RunWatcher::dispatch($this);

        return true;
    }

    /**
     * Called before the watcher is executed.
     * 
     * This method is called by `RunWatcher` before this watcher is executed. By default, it does nothing. It may be
     * overriden with app-specific functionality that should be run before watcher execution.
     * 
     * If this method returns false, the watcher will not be run.
     * 
     * @return bool whether to continue and execute the watcher.
     */
    public function beforeRun(): bool
    {
        return true;
    }

    /**
     * Creates a collection attribute.
     * 
     * Creates a new Laravel {@see Attribute} that gets and sets the given key on a given collection.
     * 
     * @param string $collection the name of the collection on the model to get/set. An attribute with this name will be
     * queried to access the collection value, and an array with this name will be returned fromn the setter.
     * @param string $key the key on the collection to get/set.
     * @return Attribute the instantiated Laravel attribute, which may be returned from a mutator/accessor.
     */
    private function collectionAttribute(string $collection, string $key): Attribute
    {
        return Attribute::make(
            get: fn () => $this->$collection->get($key, collect()),
            set: fn ($value) => [ $collection => $this->$collection->put($key, $value) ]
        );
    }

    /**
     * Schedule the watchers.
     * 
     * @param  \Illuminate\Console\Scheduling\Schedule
     * @return void
     */
    public static function schedule(Schedule $schedule = null): void
    {
        $schedule = $schedule ?: app(Schedule::class);

        static::active()->each(function($model) use ($schedule) {
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
        });
    }
}