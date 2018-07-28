<?php

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

$factory->define(App\User::class, function (Faker $faker) {
    return [
        'name' => $faker->name,
        'email' => $faker->unique()->safeEmail,
        'password' => '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77UdFm', // secret
        'remember_token' => str_random(10),
    ];
});

/* Defino el state que me hacer falta en la prueba PolicyPolicyTest
        $factory->state(\App\User::class,'admin',function(Faker $faker){
            return ['role'=>'admin'];
        });
    Aunque una forma más sencilla en la que me permita además pasar los datos es en lugar de 
    pasar una funcion anonima, pasar una array asociativo con los datos.
*/
$factory->state(\App\User::class,'admin',['role'=>'admin']);