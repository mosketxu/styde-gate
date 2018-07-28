<?php

namespace Tests\Unit;

use App\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\Eloquent\Model;

class UserModelTest extends TestCase
{
    use RefreshDatabase;

    /*a_user_owns_a_model
        Tendre dos usuarios A y B. No olvidar llamar a la clase user y poner refreshdatabase
        Creare dos modelos, uno propiedad del usuario A y el otro propiedad del usuario B
        Lo hare con el metodo que aun no existe OwnedModel
        Luego compruebo que el usuarioA es dueño del modeloA con el metodo que no existe owns
        Luego compruebo que el usuarioB es dueño del modeloB con el metodo que no existe owns
        Luego compruebo que el usuarioA NO es dueño del modeloA con el metodo que no existe owns
        y que B no es dueño del A
        ERROR: Call to undefined method Tests\Unit\UserModelTest::createUser()
        SOLUCION: En lugar de crear de nuevo este metodo lo muevo de PostPolicyTest a TestCase.php
        
        ERROR: Class 'Tests\Unit\OwnedModel' not found
        SOLUCION: Esta será una clase de soporte que voy a crear directamente en la prueba y que extiende del modelo de eloquent
            Debo incluir el use Illuminate\Database\Eloquent\Model;

        ERROR: Illuminate\Database\Eloquent\MassAssignmentException: Add [user_id] to fillable property to allow mass assignment on [Tests\Unit\OwnedModel].
        SOLUCION: Es una proteccion de MassAssignmentException en la clase del modelo OwnedModel
                Lo desactivo con protected $guarded=[], es decir que no me proteja nada. Como estoy en pruebas me da igual. En otro sitio lo haría con mas cuidado

        ERROR: BadMethodCallException: Method Illuminate\Database\Query\Builder::owns does not exist.
        SOLUCION: Debo crear el metodo owns. Lo hago en el modelo de usuario User.php
            public function owns(){
        
            }

        ERROR: Failed asserting that null is true  Como aun no le he puesto logica me devuelve null cuando espero true
        SOLUCION: Le pongo logica. Para ello le envio un modelo de Eloquent. Debo importarlo al principio de la clase USer    use Illuminate\Database\Eloquent\Model;
                    Donde verifico que la llave primaria del usuario sea la misma que la llave foranea del modelo
            Las pruebas pasan    
        Entonces voy a AuthServiceProvider y uso el metodo owns en la verificacion del Gate
            Las pruebas pasan
    */
    /** @test **/
    public function a_user_owns_a_model()
    {
        $userA=$this->createUser();
        $userB=$this->createUser();

        $ownedByUserA=new OwnedModel(['user_id'=>$userA->id]);
        $ownedByUserB=new OwnedModel(['user_id'=>$userB->id]);

        $this->assertTrue($userA->owns($ownedByUserA));
        $this->assertTrue($userB->owns($ownedByUserB));

        $this->assertFalse($userA->owns($ownedByUserB));
        $this->assertFalse($userB->owns($ownedByUserA));
    }
}

class OwnedModel extends Model {
    protected $guarded=[];
}