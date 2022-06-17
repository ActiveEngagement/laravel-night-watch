<?php

namespace Actengage\NightWatch;

use Actengage\NightWatch\Events\BadResponse;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
    public function watcher(): BelongsTo
    {
        return $this->belongsTo(Watcher::class, 'watcher_id');
    }

    /**
     * Boot the model.
     * 
     * @return void
     */
    public static function boot(): void
    {
        parent::boot();

        static::created(function($model) {
            if(!$model->success) {
                BadResponse::dispatch($model);
            }
        });
    }
}