<?php

namespace Tests\Unit;

use App\{User,Post};
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use \Illuminate\Support\Facades\Gate;


class PolicyPolicyTest extends TestCase
{
    use RefreshDatabase;
    
    /* admins_can_update_posts
        La prueva consta de tres partes:
        Arrange- Preparacion 
            Creo un administrador usando una técnica llamada wishful thinking. Esto es que voy a escribir el codigo que quiero utilizar aunque aun no lo tenga desarrollado
            Por ejemplo el admin a partir del metodo createAdmin() aunque este aun no exista, ya lo crearé luego
            luego uso el metodo be que como el usuario actingAs permite con conecte con el usuario
            luego si quiero ver que el usuario actualice un Post entonces debo tener un Post.
        Act- Es la segunda fase y es donde obtengo el resultado de interactuar con el Gate
            Debo incluir el facade Gate  use \Illuminate\Support\Facades\Gate;
            Y llamo al metodo allows del facade gate. Como primer argutmento el nombre de la regla que quiero comprobar y como segundo el post en cuestion 
            No necesito pasar el usuario porque laravel ya interactua con el al crear $admin y al conectarlo con be, pero no necesito pasarlo al gate
        Assert- verifico que todo esta bien
            verifico que al llamar al metodo allows del facade Gate con la regla y el post en cuestion devuelve verdadero

                public function admins_can_update_posts()
                {
                    // Arrange
                    $admin=$this->createAdmin();
                    $this->be($admin); //Este metodo permite que conecte un usuario. Es equivalente al actingAs
                    $post=new Post;    // me hará falta crear el modelo

                    // Act
                    $result=Gate::allows('update-post',$post);

                    //Assert
                    $this->assertTrue($result);
                }
        Ejecuto la prueba:
        ERROR: Call to undefined method Tests\Unit\PolicyPolicyTest::createAdmin()
        SOLUCION: Creo el metodo justo debajo     
                public function createAdmin(){
                    
                }
        ERROR: be() must be an instance of Illuminate\Contracts\Auth\Authenticatable, null given
        SOLUCION: Esta esperando un usuario y devuelvo null
                    Creo el usuario con el model factory y el estado states admin . Los states son propios de Laravel
                    Importo User
        ERROR:  InvalidArgumentException: Unable to locate [admin] state for [App\User].
        SOLUCION: Voy a database factory UserFactory.php y defino el state
                $factory->state(\App\User::class,'admin',function(Faker $faker){
                     return ['role'=>'admin'];
                });     
        ERROR: Base table or view not found: 1146 Table 'styde_gates_tests.users
        SOLUCION: Si no he creado la base de datos la creo y pongo el trait RefreshDatabase. Para la de produccion deberé ejecutar las migraciones
        
        ERROR: Column not found: 1054 Unknown column 'role' in 'field list'
        SOLUCION: La añado a la migracion de create_users $table->string('role')->default('user');

        ERROR:  Class 'Tests\Unit\Post' not found
        SOLUCION: Creo el model Post y aprovecho para crear las migraciones y modelo factory para este modelo php artisan make:model Post -mf
                Importo el modelo en la clase use App\Post

        ERROR: Failed asserting that false is true. En la linea 82 (cuando lo ejecute antes de comentar mas) $this->assertTrue($result)
        SOLUCION: Voy a Providers\AuthServiceProvider.php
                  Registro un nuevo Gate con el primer argumento el nombre del gate y como segundo una funcion anonima con la logica del gate,
                       Gate::define('update-post',function(Post $post){});   Debo aceptar el $post porque si quiero preguntar si un usuario tiene permiso para actualizar un post debería pasar el post
                                                                            Aqui no envio el user, pero Laravel me o devuelve, Ver detalles allí. 
                                                                            retorna true forzadamente para ver que hace la prueba.
                La prueba pasa si retorno true y no pasa si retorno false
                Es decir que si la funcion anonima devuelve true dara acceso y si devuelve false no dara acceso
                

        */
    /** @test **/
    public function admins_can_update_posts()
    {
        // Arrange
        $admin=$this->createAdmin();
        $this->be($admin); //Este metodo permite que conecte un usuario. Es equivalente al actingAs
        $post=new Post;    // me hará falta crear el modelo

         // Act
         $result=Gate::allows('update-post',$post);

         //Assert
         $this->assertTrue($result);
    }

    public function createAdmin(){
        return factory(User::class)->states('admin')->create();
    }
}
