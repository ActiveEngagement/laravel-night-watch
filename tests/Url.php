<?php

namespace Actengage\NightWatch\Tests;

use Actengage\NightWatch\Support\Watchable;
use Illuminate\Database\Eloquent\Model;

class Url extends Model {
    use Watchable;
    
    protected $fillable = ['url'];
}