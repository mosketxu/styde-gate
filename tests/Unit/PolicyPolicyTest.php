<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PolicyPolicyTest extends TestCase
{
    
    /* admins_can_update_posts
        Creo un administrador usando una técnica llamada wishful thinking. Esto es que voy a escribir el codigo que quiero utilizar aunque aun no lo tenga desarrollado
        Por ejemplo el admin a partir del metodo createAdmin() aunque este aun no exista, ya lo crearé luego
        luego uso el metodo be que como el usuario actingAs permite con conecte con el usuario
         
    */
    /** @test **/
    public function admins_can_update_posts()
    {
        $admin=$this->createAdmin();
        
    }
}
