<?php

namespace Tests\Feature;

use App\Post;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UpdatePostTest extends TestCase
{
    use RefreshDatabase;

    /* admins_can_update_posts
     En lugar de hacer una prueba untaria para interactuar directamente con el gate como en una leccion anterior
     En este caso trabajamos con una prueba funcional
     
     use RefreshDatabase (arriba)
     Creo un post con factory y con un autor aleatorio  (llamos a la clase Post  use App\Post)
     Creo el admin con el helper createadmin
     Lo conecto con actingAs
        $this->actingAs($admin);
     Envio una peticion de tipo put a la url admin/posts con el $post y con el titulo al que voy a actualizar el post
     
     quiero comprobar que la respuesta sera un estado 200 y mostrare el texto post updated
     reviso que en la tabla de posts tengo el post que acabo de crear y con el titulo al que he actulizado
     Pruebo
     ERROR:   Expected status code 200 but received 404.
               Failed asserting that false is true.
          es logico porque no he hecho nada. Primer error develve 404 porque la URL no existe
     SOLUCION: crear la ruta en web.php. Debo enviar el post y usare implicit model binding para atar este id al modelo de post
          Route::put('admin/posts/{post}', function (Post $post) {
        
          });

    ERROR: recibo un error 500 en lugar del 200
    SOLUCION: Para ver lo que pasa pongo $this->withoutExceptionHandling()
            ReflectionException: Class Post does not exist
            Dice que no existe la clase Post
            Hay que importarla en el archivo de rutas
    ERROR:ModelNotFoundException: No query results for model [App\Post]
    SOLUCION: CUIDADO cuando se manda la variable $post->id deb ir entre comillas dobles, no comillas simples
            MAL             $response=$this->put('admin/posts/{$post->id}',[
            BIEN            $response=$this->put("admin/posts/{$post->id}",[

    ERROR:Failed asserting that '' contains "Post updated!".
    SOLUCION: La ruta no retorna nada. Hago que retorne "Post updated!"
                return "Post updated!"

    ERROR: ErrorException: Use of undefined constant id - assumed 'id' (this will throw an Error in a future version of PHP)
    SOLUCION: He puesto $post.id en lugar de $post->id

    ERROR: QueryException: SQLSTATE[42S22]: Column not found: 1054 Unknown column 'title' in 'where clause'
    SOLUCION: la agrego en la migracion 
    
    ERROR: QueryException: SQLSTATE[HY000]: General error: 1364 Field 'title' doesn't have a default value 
    SOLUCION: Esto pasa cuando en nuestro model factory estamos creando un modelo pero no estamos pasando todos los campos.
                Lo añado en el modelo factory PostFactory.php
    ERROR: Failed asserting that a row in the table [posts] matches the attributes {
                "id": 1,
                "title": "Updated post title"
                }.

                Found: [
    SOLUCION: Esto pasa porque no estamos actualizando el Post
            Hago esto en web.php en la ruta
                Route::put('admin/posts/{post}', function (Post $post, Request $request) {
                    $post->update([
                        'title'=>$request->title,
                ]);
    ERROR: Illuminate\Database\Eloquent\MassAssignmentException: Add [title] to fillable property to allow mass assignment on [App\Post].
    SOLUCION: Voy al modelo Post. Allí puedo usar la propiedad guarded vacia si voy a tener cuidado de no usar request all
                    protected $guarded=[];
            Sino mejor uso fillable
                    protected $fillable=['title'];
    Las pruebas pasan

    PROBLEMA:
    Sin embargo aun no hemos aplicado los permisos correctamente porque la prueba tb pasa sin conectar el usuario. Se me había olvidado conectarlos con 
            $this->actingAs($admin);
    SOLUCION: Pero para arreglarlo quiere priemero escribir la sguiente prueba.           

    Siguiente prueba. Quiero probar que los usuarios anonimos no pueden actualizar los posts
     */

    /** @test */
    public function admins_can_update_posts()
    {
        // $this->withoutExceptionHandling();

        $post=factory(Post::class)->create();
        $admin=$this->createAdmin();
        $this->actingAs($admin);
        $response = $this->put("admin/posts/{$post->id}",[
            'title'=>'Updated post title',
        ]);

        $response->assertStatus(200)
            ->assertSee('Post updated!');
        
        $this->assertDatabaseHas('posts',[
            'id'=>$post->id,
            'title'=>'Updated post title',
        ]);
    }

    /* guests_cannot_update_posts
        Creo un post
        Intento actualizar
        La respuesta puede ser pe de tipo 401 porque el usuario no esta autenticado o una redireccion a la pantalla de inicio de sesion.
            En este caso verificamos que sea de tipo 401
        Verifico que no se ha actulizado la bbdd on assertDatabaseMissing
        Pruebo
        ERROR: Expected status code 401 but received 200.
                Failed asserting that false is true.
            Este error lo da porque la respuesta no da error y es porque no le importa que se guest, es decir, le deja pasar y eso esta mal    
            De hecho si quito la comprobacion y paso de nuevo las pruebas veo que sí se está actualizando
                    Failed asserting that a row in the table [posts] does not match the attributes {
                      "id": 1,
                    "title": "Updated post title"
            Me dice que es falso que no coincide, es decir que si coincide, es decir que ha actualizado y no debería poder
        SOLUCION: en el archivo web.php agrego una clausula guard. Visto en el curso de refactorizacion
                        if(auth()->guest()){
                           abort(401);
                        }
        ERROR: Symfony\Component\HttpKernel\Exception\HttpException:
        SOLUCION: quitar $this->withoutExceptionHandling();

        NOTA: si no tuviera en la primera prueba actingAs($admin) esta prueba ahora daría error
        Las pruebas pasan

        Siguiente prueba:Usuarios conectados y no autorizados
     */
    /** @test */
    public function guests_cannot_update_posts()
    {
        //  $this->withoutExceptionHandling();

        $post = factory(Post::class)->create();

        $response = $this->put("admin/posts/{$post->id}", [
            'title' => 'Updated post title',
        ]);

        // $response->assertStatus(401)     //tras usar el middleware Laravel intenta redirigir a la pantalla de inicio de sesion, y aqui da error asi que cambiamos por
            // ->assertSee('Post updated!');
        $response->assertRedirect('login');

        $this->assertDatabaseMissing('posts', [
            'id' => $post->id,
            'title' => 'Updated post title',
        ]);
    }

    /* unauthorized_users_cannot_update_posts
    Creo un post
    Creo un user
    Conecto con ese user
    verifico que da error 403, usuario no autorizado
    verifico que no actualiza

    Pruebo
    ERROR: Expected status code 403 but received 200.
            Y si comentamos la linea vemos que el post fue actualizado
    SOLUCION: En web.php uso el gate de laravel para preguntar si el usuario puede actualizar posts. Si no puede con ! o con cant en lugar de can aborto
                if(!auth()->user()->can('update',$post)){
                    abort(403),
                }

                if(auth()->user()->cant('update',$post)){
                    abort(403),
                }
    La prueba pasa, pero deja de pasar la de guest SI NO PONGO EL ORDEN CORECTO. PRIMERO GUEST LUEGO ESTA

    siguiente prueba: Verifico que los autores sí pueden mofificar el post
     */
    /** @test */
    public function unauthorized_users_cannot_update_posts()
    {
        // $this->withoutExceptionHandling();

        $post = factory(Post::class)->create();

        $user= $this->createUser(); //esta definida la funcion en TestCase
        $this->actingAs($user);
        
        $response = $this->put("admin/posts/{$post->id}", [
            'title' => 'Updated post title',
        ]);

        $response->assertStatus(403)
            ->assertSee('Post updated!');

        $this->assertDatabaseMissing('posts', [
            'id' => $post->id,
            'title' => 'Updated post title',
        ]);
    }
    /* authors_can_update_posts
    Creo un user $user que será el autor del post
    Lo conecto con actingAs
    $this->actingAs($user);
    Creo un post con factory y le digo que lo ha creado $user

     Envio una peticion de tipo put a la url admin/posts con el $post y con el titulo al que voy a actualizar el post
     
     quiero comprobar que la respuesta sera un estado 200 y mostrare el texto post updated
     reviso que en la tabla de posts tengo el post que acabo de crear y con el titulo al que he actulizado

     las puebas pasan

    Segun Duilio el web.php ha quedado feo y complicado con los if
                if(!auth()->user()->can('update',$post)){
                    abort(403),
                }

                if(auth()->user()->cant('update',$post)){
                    abort(403),
                }
    así que los cambia por gates. Ver allí
     */

    /** @test */
    public function authors_can_update_posts()
    {
        // $this->withoutExceptionHandling();
        $user = $this->createAdmin();
        
        $post = factory(Post::class)->create([
            'user_id'=>$user->id,
            ]);
        
        $this->actingAs($user);
        
        $response = $this->put("admin/posts/{$post->id}", [
            'title' => 'Updated post title',
        ]);

        $response->assertStatus(200)
            ->assertSee('Post updated!');

        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'title' => 'Updated post title',
        ]);
    }

}