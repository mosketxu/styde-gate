<?php

use Faker\Generator as Faker;

$factory->define(App\Post::class, function (Faker $faker) {
    return [
        'user_id'=>factory(\App\User::class), // lo hago para dar un valor por defecto al usuario en las pruebas
    ];
});
