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
    /* authenticated_users_can_view_posts 
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
    public function authenticated_users_can_view_posts()
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
}
