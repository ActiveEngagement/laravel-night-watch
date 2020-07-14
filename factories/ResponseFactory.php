<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use Actengage\NightWatch\Response;
use Actengage\NightWatch\Watcher;
use Faker\Generator as Faker;

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| This directory should contain each of the model factory definitions for
| your application. Factories provide a convenient way to generate new
| model instances for testing / seeding your application's database.
|
*/

$factory->define(Response::class, function (Faker $faker) {
    $watcher = factory(Watcher::class)->create();

    return [
        'watcher_id' => $watcher->id,
        'response' => ['success' => 200],
        'status_code' => 200,
        'success' => true
    ];
});
