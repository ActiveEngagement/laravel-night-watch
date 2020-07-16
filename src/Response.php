<?php

namespace Actengage\NightWatch;

use Actengage\NightWatch\Events\BadResponse;
use Illuminate\Database\Eloquent\Model;

class Response extends Model {

    /**
     * The table associated with the model.
     * 
     * @var string
     */
    protected $table = 'nightwatch_responses';

    /**
     * The fillable attributes.
     * 
     * @var array
     */
    protected $fillable = [
        'watcher_id', 'response', 'success', 'message', 'status_code'
    ];

    /**
     * The attributes that are cast.
     * 
     * @var string
     */
    protected $casts = [
        'success' => 'bool',
        'response' => 'array'
    ];

    /**
     * The parent watcher.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo;
     */
    public function watcher()
    {
        return $this->belongsTo(Watcher::class, 'watcher_id');
    }

    /**
     * Boot the model.
     * 
     * @return void
     */
    public static function boot()
    {
        parent::boot();

        // Order by name ASC
        static::addGlobalScope('order', function ($builder) {
            $builder->orderBy('created_at', 'desc');
        });

        static::created(function($model) {
            if(!$model->success) {
                BadResponse::dispatch($model);
            }
        });
    }
}