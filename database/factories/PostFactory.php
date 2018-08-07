<?php

use Faker\Generator as Faker;

$factory->define(App\Post::class, function (Faker $faker) {
    return [
        'user_id'=>factory(\App\User::class), // lo hago para dar un valor por defecto al usuario en las pruebas
        'title'=> $faker->sentence,
    ];
});

$factory->state(\App\Post::class,'draft',['status'=>'draft']); // creo el state 'draft' y para que se cumpla debe tener el status draft. Tendre que crear la columna

$factory->state(\App\Post::class,'published',['status'=>'published']);
