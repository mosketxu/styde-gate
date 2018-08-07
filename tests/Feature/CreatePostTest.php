<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CreatePostTest extends TestCase
{
    use RefreshDatabase;

    
    /*
     En este test veremos que podemos crear Posts y tambien que 
     hay acciones que aunque esten enalzadas a un modelo no tienen un modelo como tal
     */

    /*admins_can_create_posts
        use RefreshDatabase;
        creo un usario admin y lo conecto
        envio una peticion para creor un post
        verifico status 201 que es el codigo qeu devuelve cuando creamos un recurso
        y que puedo ver el texto 'Post created!!'
            aunque aquí tipiacmente retornariamos una redirecion
        verifico que se ha creado el post con el id del admin
        Pruebo

        ERROR:  Tests\Feature\CreatePostTest::admins_can_create_posts
                Expected status code 201 but received 404.
                Failed asserting that false is true.
        SOLUCION: Esto es porque no he declarado la ruta. La declaro dentro del grupo de rutas en web.php
                  enlazandola al controlador PostController y al metodo store
                  Route::post('admin/posts','PostController@store');
        
        ERROR:  1) Tests\Feature\CreatePostTest::admins_can_create_posts
                Expected status code 201 but received 500.
                Failed asserting that false is true.
        SOLUCION: pongo $this->withoutExceptionHandling(); y pruebo

        ERROR:  1) Tests\Feature\CreatePostTest::admins_can_create_posts
                BadMethodCallException: Method App\Http\Controllers\Admin\PostController::store does not exist.
        SOLUCION: No existe el metodo en el controlador, asi que lo creo.
                En la funcion acepto el objeto Request, creo el post con el titulo del objeto request
                retorno una respuesta con el texto deseado y el estado 201
                 public function store(Request $request)
                        {
                            Post::create([
                                'title' => $request->title,
                            ]);
                    return new Responser('Post created!!',201);
                        }
        ERROR: PDOException: SQLSTATE[HY000]: General error: 1364 Field 'user_id' doesn't have a default value
        SOLUCION: userid no tiene un valor por defecto, así que vuelvo al controlador y asigno el id del usario como el id del usuario conectdo

        ERROR: el mismo
        SOLUCION: es porque en el modelo Post no estoy pasando user_id como fillable
                 No olvidar poner en PostController use Illuminate\Http\Response;
        las pruebas pasan,

        Pero a Duilio no le gusta poner el user_id en fillable, así que lo que hace es en el modelo
        User.php  colocar la relacion diciendo que un usuario va a tener muchos posts
                public function post()
                {
                    return $this->hasMany(Post::class);
                }
        y de vuelta a PostController voy a obtner el usuario de la peticion, luego obtengo la relacion con post y 
        a traves del metodo create paso el titulo con el que quiero crear el post
                $request->user()->post()->create([
                    'title'=>'New post',
                ]);
        Las pruebas pasan

        Mejoro si creo un metodo createPost pero lo deja como deberes

        Siguiente prueba. Que los autores pueden crear nuevos posts
     */
    /** @test */
    public function admins_can_create_posts()
    {
        $this->withoutExceptionHandling();

        $this->actingAs($admin=$this->createAdmin());

        $response=$this->post('admin/posts',[
            'title'=>'New Post',
        ]);

        $response->assertStatus(201)->assertSee('Post created!!');
        $this->assertDatabaseHas('posts',[
            'title'=>'New Post',
        ]);

    }

    /* authors_can_create_posts
        creo y conecto un usuario con el role de autor
        Voy al helper createUser que esta en TestCase.php para permitir que este helper pueda pasar atributos al helper factory
                public function createUser(array $attributes=[]){
                    return factory(User::class)->create($attributes);
                }
        Pruebo
        ERROR:  1) Tests\Feature\CreatePostTest::authors_can_create_posts
                ErrorException: Use of undefined constant role - assumed 'role' (this will throw an Error in a future version of PHP)
        SOLUCION: tenia role sin comillas cuando pasaba author

        Pasan

     */
    /** @test */
    public function authors_can_create_posts()
    {
        $this->withoutExceptionHandling();

        $this->actingAs($user = $this->createUser(['role'=>'author']));

        $response = $this->post('admin/posts', [
            'title' => 'New Post',
        ]);

        $response->assertStatus(201)->assertSee('Post created!!');
        $this->assertDatabaseHas('posts', [
            'title' => 'New Post',
        ]);
    }

    /* Pruebo que usuarios no autorizados no puedan crear posts 
        quito $this->withoutExceptionHandling() porque necesito el manejador de excepciones
        creo un usuario con el role subscriptor
        la respuesta debería ser 403, es decir que no tiene permisos
        y verifco que no esta el post con databaseMissing

        ERROR: 1) Tests\Feature\CreatePostTest::unathorized_users_cannot_create_posts
                Expected status code 403 but received 201.
                Failed asserting that false is true.
        SOLUCION: Voy a la carpeta Policies y a PostPolicy y colocamos una condicion dentro del metodo create
                return $user->role==='author'; // nuestros usuarios solo pueden crear posts si son autores

        ERROR:  La prueba debe estar fallando con el mismo error porque debo poner el metodo auhtorize en PostController
        SOLUCION: poner el metodo authorize en el metodo store
                    $this->authorize('create',new Post)
                El problema es que ademas de la habiidad a la que autorizar 'create'
                debo pasar el modelo, pero en este punt no dispongo del modelo de post
                si hago un new Post funcionaría aunuqe se suele pasar el nombre del la clase, el modelo o el recurso en cuestion
                            $this->authorize('create', Post::class); 

     */
    /** @test */
    public function unathorized_users_cannot_create_posts()
    {
        // $this->withoutExceptionHandling();

        $this->actingAs($user = $this->createUser(['role' => 'subscriber']));

        $response = $this->post('admin/posts', [
            'title' => 'New Post',
        ]);

        $response->assertStatus(403);
        $this->assertDatabaseMissing('posts', [
            'title' => 'New Post',
        ]);
    }

}
