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

                Tras probar con la prueba guests_cannot_update_posts que laravel deniega por defecto el acceso a usuarios no conectados juego un poco con esta prueba.
                
                Pruebo a comentar cuando conecto con el usuario $this->be($admin);
                ERROR: Failed asserting that false is true
                SOLUCION: indico expresamente que el gate se debe probar con el usuario que le envío con el metodo foruser
                                $result=Gate::forUser($admin)->allows('update-post',$post);   
                            Es decir, puedo elegir con que usuario probar el allows.  Este casi lo entiendo mejor que conectar antes con be o actingAs y probar
                                    public function admins_can_update_posts()
                                    {
                                        // Arrange
                                        $admin=$this->createAdmin();
                                        $post=new Post;    // me hará falta crear el modelo

                                        // Act
                                        $result=Gate::forUser($admin)->allows('update-post',$post);

                                        //Assert
                                        $this->assertTrue($result);
                                    }

                Otro juego
                Gracias a eloquent puedo interactuar directamente con el modelo:
                Pregunto si el usuario $admin puede actualizar el post $post con el metodo can de eloquent. Para esto debo tener be activo
                    $result= $admin->can('update-post',$post);
                Tambien existe el metodo contrario: cannot que en este caso falla porque siempre retorno true
                    $result= $admin->cannot('update-post',$post);

                        public function admins_can_update_posts()
                        {
                            // Arrange
                            $admin=$this->createAdmin();
                            $this->be($admin); 
                            $post=new Post;   

                            // Act
                            $result=$admin->can('update-post',$post);
                            //$result=$admin->cannot('update-post',$post);

                            //Assert
                            $this->assertTrue($result);
                        }

                Tambien existe el metodo contrario: cannot y en este caso deberia ser un assertFalse
                    $result= $admin->cannot('update-post',$post);

                        public function admins_can_update_posts()
                        {
                            // Arrange
                            $admin=$this->createAdmin();
                            $this->be($admin); 
                            $post=new Post;   

                            // Act
                            $result=$admin->can('update-post',$post);
                            //$result=$admin->cannot('update-post',$post);

                            //Assert
                            $this->assertFalse($result);
                        }
                Otro juego: Si estoy trabajando con el usuario conectado puedo obtnerlo con el auth 
                        $result=auth()->user()->can('update-post',$post);

                        public function admins_can_update_posts()
                            {
                                // Arrange
                                $admin=$this->createAdmin();
                                $this->be($admin); 
                                $post=new Post;   

                                // Act
                                $result=auth()->user()->can('update-post',$post);
                                //Assert
                                $this->assertTrue($result);
                            }
                
            Pero hasta ahora siempre retornaba true. 
            Vamos a mejorar la logica del Gate en AuthServiceProvider.php
            Para ello vamos a crear una prueba para verificar que los usuarios conectados pero no autorizados no pueden actualizar el post
            Copio la prueba de Guest y la llamo   unathorized_users_cannot_update_posts
            Luego hago prueba authors_can_update_posts y todo pasa
            
            Ahora cambio la manera de generar el post. En lugar de new Post uso los model factory de Laravel.
            ERROR: General error: 1364 Field 'user_id' doesn't have a default value 
                Falla porque estoy intentando crear post sin tener el valor asignado a user_id
            SOLUCION: Voy al model factory de Post y asigno un usuario aleatorio a cada post
                            return [
                                'user_id'=>factory(\App\User::class),
                            ]
                La prueba pasa
            Otra mejora es ir a UserFactory y en lugar de pasar una funcion anonima pasamos el array asociativo con los datos
                $factory->state(\App\User::class),'admin',['role'=>'admin']);

            Otra mejora: En AuthServiceProvider en lugar de preguntar el role del usuario y compararlo con la cadena 'admin'
            puedo preguntarle al usuario si es un admin con el metodo isAdmin que debo crear en el modelo User
                    public function isAdmin(){
                        return $this->role==='admin';
                    }


        */
    /** @test **/
    public function admins_can_update_posts()
    {
        // Arrange
        $admin=$this->createAdmin();
        $this->be($admin); 
        //$post=new Post;   // lo puedo crear así o usando los model factories de laravel
        $post=factory(Post::class)->create();

         // Act
         $result=Gate::allows('update-post',$post);
         //Assert
         $this->assertTrue($result);
    }

    /* guests_cannot_update_posts 
        Aqui ni creo el usuario ni lo conecto. Solo mando un post, pregunto y verifico que no pueda actualizarlo
        La prueba pasa. Nota: veo que pasan 4 pruebas cuando solo tengo 2. Es porque estan las de ejemplo de feature y de unit. Las borro
        EXPLICACION: A pesar de que en AuthServiceProvider siempre estoy retornando True, Laravel deniega el acceso a los usuarios anonimos, 
                    y esto es algo que ocurre por defecto
                    Podría decirse que esta prueba es redundante
                    LARAVEL SIEMPRE DEVUELVE FALSO SI EL USUARIO NO ESTA CONECTADO

    */
    /** @test **/
    public function guests_cannot_update_posts()
    {
        // Arrange
        $post=new Post;    // me hará falta crear el modelo

         // Act
         $result=Gate::allows('update-post',$post);

         //Assert
         $this->assertFalse($result);
    }

    /* unathorized_users_cannot_update_posts
        Tengo que conectar un usuario que no sea admin. Lo puedo hacer con el 
                be($user)
            o con 
                $result=Gate::forUser($user)->allows('update-post',$post);
        Creo el metodo createUser con un model factory general. No hago el proceso de crear el states etc
        como no debe porder actualizar el post la comprobacion debe ser assertFalse

        ERROR: Failed asserting that true is false.  Lo cual es logico porque en el Gate de AuthServiceProvider siempre devuelve true
        SOLUCION: Modifico la logica en AuthServiceProvider y solo retorno true si es admin:   return $user->role==='admin'; //solo retorno true si el usuario es admin

        Ahora vamos a comprobar que los autores del post pueden actualizarlo con la prueba authors_can_update_posts
    */
    /** @test **/
    public function unathorized_users_cannot_update_posts()
    {
        // Arrange
        $user=$this->createUser();
        // $this->be($user); 

        $post=new Post;    // me hará falta crear el modelo

         // Act
         //$result=Gate::allows('update-post',$post);
         $result=Gate::forUser($user)->allows('update-post',$post);


         //Assert
         $this->assertFalse($result);
    }

    /* authors_can_update_posts
        Creo un usuario general
        Conecto el usuario general
        Creo el post con model factories y enlazo el post al usuario con la llave foranea user_id que no existe en este momento
        ERROR: Column not found: 1054 Unknown column 'user_id' in 'field list'  Logico porque no la he creado
        SOLUCION: Voy a la migracion create_post y la añado  
                    $table->unsignedInteger('user_id');
                y tambien añado una llave foranes
                    $table->foreign('user_id')->references('id')->on('users');

        ERROR: Failed asserting that false is true  Me devuelve falso y espero true porque no he modificado la logica de AuthServiceProvider
        SOLUCION: en AuthServiceProvider modifico la logica return $user->role==='admin' || $user->id=== $post->user_id;
        la prueba pasa
    */
    /** @test **/
    public function authors_can_update_posts()
    {
        // Arrange
        $user=$this->createUser();
        $this->be($user); 
        $post=factory(Post::class)->create([
            'user_id'=>$user->id,
        ]);   

         // Act
         $result=Gate::allows('update-post',$post);
         
         //Assert
         $this->assertTrue($result);
    }

/*  Como llamo a estos dos metodos desde otras pruebas los muevo a TestCase.php
    public function createAdmin(){
        return factory(User::class)->states('admin')->create(); //creo el states admin en factorier UserFactory
    }

    public function createUser(){
        return factory(User::class)->create();
    }
*/
}
