<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Post;

class ListPostsTest extends TestCase
{
    /*En esta prueba voy a preparar las funcionalidades para que los usurios puedan ver los posts*/ 

    use RefreshDatabase;
    /* authenticated_users_can_view_posts  . Comento la funcion y la hago con nuevo nombre para que solo los autores puedan ver los posts
        use RefreshDatabase;
        creo dos posts con modelo factory
        probamos que cuando hacemos una peticions a la url posts recibimos estado 200
        que estamos en la vista admin.post.index 
        y la vista contiene la variale posts y
        esta variable es una coleccion que contiene los posts 1 y 2

        ERROR:  1) Tests\Feature\ListPostsTest::users_can_view_posts
                Expected status code 200 but received 404.
                Failed asserting that false is true.
        SOLUCION: La ruta no existe, asú que voy a web.php y la creo dentro de grupo
            Route::get('admin/posts','PostConroller@index')
            ademas modifico el grupo de rutas con el prefix admin
        ERROR:  Sigue dando el error que devuelve 404 en lugar de 200
        SOLUCION: en el get es 'admin/posts' y habia puesto solo 'posts'
        
        ERROR:  devulve 302 en lugar de 200
        SOLUCION:  Es porque estoy usando el middlewae auth que esta redirigiendo a la pantalla de inicio de sesion 
                solucion 1: crear y conectar el usuario en la prueba .
                solucion 2: quitar el metodo auth, aunque esto sería casi para la misma prueba y para usuarios no autenticados
        
        ERROR: devuelve 500 en lugar de 200
        SOLUCION:         $this->withoutExceptionHandling();
                Fallará pq no existe el controlador pero lo miro para ver que tipo de error da:
        
        ERROR:     el metodo index no existe en PostController
        SOLUCION:   Lo creo y que retorne una vista, sino tb da error, y que exista la vista
                    creo la vista admin.posts.index 
        
        ERROR:    1) Tests\Feature\ListPostsTest::authenticated_users_can_view_posts
                    ErrorException: Undefined variable: post
        SOLUCION:  En un punto de la prueba he puesto $post en lugar de $posts

        ERROR:  1) Tests\Feature\ListPostsTest::authenticated_users_can_view_posts
                Error: Call to a member function contains() on null
        SOLUCION: Esto es porque no estoy pasando la variable posts a la vista y en la vista la estoy esperando,
                la espera en la funcion anonima function($posts, etc)
                Si quito la funcion anomima el error que me da es:
                    Tests\Feature\ListPostsTest::authenticated_users_can_view_posts
                    Failed asserting that an array has the key 'posts'.
                Voy a PostCotroller y traigo todos los posts de la BD con eloquent
                    $posts=Post::all();       
                y paso la vable post a la vista
                    return view('admin.posts.index',compact('posts'))
        Las pruebas pasan

        Quiere decir que index existe, carga la pagina sin problemas y estoy pasando los posts a la vista, aunque un no estoy mostrando nada en la vista
        si intento ir a styde-gates.test/admin/posts me lleva a login.

        Vamos a crear todas las vistas que necesito: php artisan make:auth

        En el archivo de rutas ya no me hace falta la ruta del login ya que auth me redirige a login
        Creo un usuario

        ERROR: La tabla users no existe
        SOLUCION: ejecuto las migraciones php artisan migrate

        llego a styde-gates.test/admin/posts y accedo pero no veo nada

        copio el home.blade.php en index.blade.php y la modifico

        Creo unos seeder para generar posts
        php artisan make:seeder PostSeeder y lo modifico
        recordar que hay que registrarlo en DatabaseSeeders
        y ejecuto el seeder php artisan db:seed

        Agrego paginacion.
        Se hace cambiando el metodo all() por el metodo paginate en PostController
        y en la vista llamo al pie de la tabla al metodo links 
            {{ $post->links() }}
            funciona
        
     */
    /** @test */
/*     public function authenticated_users_can_view_posts()
    {
        $this->withoutExceptionHandling();
        $post1 = factory(Post::class)->create();
        $post2 = factory(Post::class)->create();

        $this->actingAs($this->createUser()); 

        $response=$this->get('admin/posts');
        
        $response->assertStatus(200)
            ->assertViewIs('admin.posts.index')
            ->assertViewHas('posts',function($posts) use ($post1, $post2){ // php me obliga a incluir las vbles con use
                return $posts->contains($post1) && $posts->contains($post2);
            });
    }
 */
    /* authors_can_only_see_their_posts  . Comento la funcion y la hago con nuevo nombre para que solo los autores puedan ver los posts 
        Creare el autor al principio de la prueba y lo asigno como autor del primer post, no será autor del segundo post pero si de un tercer post y no de un cuarto post
        Ahora la condicion es que la coleccin de posts contenga el primer post pero no el segundo post y si el tercero pero no el cuarto
        ERROR: 1) Tests\Feature\ListPostsTest::authors_can_only_see_their_posts
                Illuminate\Auth\AuthenticationException: Unauthenticated.
        SOLUCION: No habia conectado el usuario $this->actingAs($user);

        ERROR: 1) Tests\Feature\ListPostsTest::authors_can_only_see_their_posts
                Failed asserting that false is true.
        SOLUCION: Debe fallar porque estoy pasando todos los posts a la vista, no solo los que tocan
                En PostController en el listado de posts me aseguro de que solo mando la lista de posts donde el usuario es el autor
                y me aseguro de que solo mando a la vista los post donde el usuario es el autor del post 
                Una forma es obtener el id del usuario conectado a traves del metodo auth() y luego filtro pidiendo todos los posts 
                cuyos user_id sean el del user conectado
                Recordamos que solo podemos acceder a esta vista si el usuario esta conectado. Si veo la ruta en web.php veo que la ruta esta bajo el middleware 'auth'
     
                    $posts = Post::where ('user_id', auth ()->id ())->paginate ();
        Las pruebas pasan y en la web se cumple

        Vamos a ver qué pasa con los admin. Hago otra prueba
     */

    /** @test */
    public function authors_can_only_see_their_posts()
    {
        $this->withoutExceptionHandling();

        $user = $this->createUser();

        $this->actingAs($user);

        $post1 = factory(Post::class)->create(['user_id' => $user->id]);
        $post2 = factory(Post::class)->create();
        $post3 = factory(Post::class)->create(['user_id' => $user->id]);
        $post4 = factory(Post::class)->create();


        $response = $this->get('admin/posts');

        $response->assertStatus(200)
            ->assertViewIs('admin.posts.index')
            ->assertViewHas('posts', function ($posts) use ($post1, $post2, $post3, $post4) { // php me obliga a incluir las vbles con use
                return $posts->contains($post1) && !$posts->contains($post2)
                    && $posts->contains($post3) && !$posts->contains($post4);
            });
    }

    /* adins_can_see_all_the_posts
        Creo un admin
        No creo post donde el usuario es autor. No lo necesito
        Compruebo que se vean los posts que tocan

        ERROR: 1) Tests\Feature\ListPostsTest::adins_can_see_all_the_posts
                Failed asserting that false is true.
                 Falla porque estoy limitando que los post que se muestren sean solo los del author, y nuestr usuario no ha creado ningun posts
        SOLUCION: Voy a PostCOntroller. si ademas quiero que si es admin muestre todos los posts lo puedo haer de una forma "un poco inocente" segun duilio chequeando si el usuaro es admin
            para ello lo obtengo del metodo isAdmin del modelo User que he obtenido del metodo auth()
                    if (auth()->user()->isAdmin()){
                       $posts = Post::paginate();
                    }else{
                    $posts = Post::where('user_id',auth()->id())->paginate();
                    }
            una forma mas limpia:
                Con el metodo query que es el que utiliza cuando necesita usar consultas de varias lineas
                cargo en la vble $q el where siempre excepto si es admin

                $posts = Post::query ()
                    ->unless (auth ()->user ()->isAdmin (), function($q)
                {
                    $q->where('user_id', auth()->id());
                }
                )
                    ->paginate ();

        Pruebo con tinker a cambiar el role de mi usuario a admin y verlo en la web
        php artisan tinker;
        $user=User::first();
        $user->role='admin';
        $user->save();

        las pruebas pasan y la web tb
     */
    /** @test */
    public function admins_can_see_all_the_posts()
    {
        $this->withoutExceptionHandling();
        
        // $admin = $this->createAdmin();
        // $this->actingAs($admin);
        // lo mismo en una linea
        $this->actingAs($this->createAdmin());

        $post2 = factory(Post::class)->create();
        $post4 = factory(Post::class)->create();


        $response = $this->get('admin/posts');

        $response->assertStatus(200)
            ->assertViewIs('admin.posts.index')
            ->assertViewHas('posts', function ($posts) use ( $post2, $post4) { // php me obliga a incluir las vbles con use
                return $posts->contains($post2) && $posts->contains($post4);
            });
    }

}
