<?php

namespace Tests\Unit;

use App\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    /*a_user_can_be_an_admin
        Creo un usuario con el model factory de un usuario NO admin, verifico que no es admin con assertFalse, verifico que la propiedad admin es false
        luego al mismo usuario le asigno la propiedad admin a true, grabo y verifico que es admin con assertTrue, verifico que la propiedad admin es verdaders
        Pongo el trait RefreshDatabase
        Pruebo y funciona
        PROBLEMA: El problema es que estoy atando la estructura de la base de datos a la lógica
        SOLUCION: Creo el metodo isAdmin() en User

        PROBLEMA: En algunos casos, por ejemplo si en en la prueba refresco el user: $user->refresh() da el siguiente error
            ERROR: Failed asserting that 0 is false.
            SOLUCION: Esto es porque aunque al crear la vble admin lo hago como boolean mysql lo guarda como tyniInt
                      Por lo tanto en el modelo User debo hacer un cast
                            protected $casts=[
                                'admin'=>'boolean',
                            ];

        Una vez hecho esto podríamos usar este método por ejemplo en:
                             el middleware Admin.php que esta en App\Http\Middleware    Ver allí
                             en las vistas: home.blade.php ver alli

    */

    /*  a_user_can_be_an_admin
        Creo un usuario No Admin y verifico que pasa
        Luego refresco y lo modifico como admin y verifico que pasa
    */
    /** @test */
    function a_user_can_be_an_admin(){
        $user= factory(User::class)->create(); 
        $user->refresh();

        $this->assertFalse($user->isAdmin());

        $user->role='admin'; 
        $user->save();
        $this->assertTrue($user->isAdmin());
    }
}
