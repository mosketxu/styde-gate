<?php

namespace Tests;

use App\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    /*Los he traido de PolicyPolicyTest porque los llamo desde diversos metodos
        Debo importar el modelo User
    */
    public function createAdmin(){
        return factory(User::class)->states('admin')->create(); //creo el states admin en factorier UserFactory
    }

    public function createUser(){
        return factory(User::class)->create();
    }

}
