<?php
namespace App\Http\Controllers\Admin;

use App\Post;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdatePostRequest;
use Illuminate\Http\Response;

class PostController extends Controller
{

    public function index()
    {
//        return view('admin.posts.index'); 
    /* con la prueba index me hace falta modificarlo ya que debo capturar en la vble $posts
    todos los posts de la BD y pasar la variable $posts a la vista */

        // $posts=Post::all(); // si quiero paginacion uso el metodo paginate
        $posts = Post::paginate();

        return view('admin.posts.index', compact('posts'));
    }

    /* Pego la funcion anonima del Route::put('admin/posts/{post}'
        y la convierto en un metodo: pongo public y un nombre, en este caso update que es como le llamare desde web.php
        Me aseguro de importar las clases que necesito: request y el modelo post
        Las pruebas pasan
        Vamos a ver otra manera de usar nuestro policy en lugar de usar un middleware
     
    public function update(Post $post, Request $request)
    {
        $post->update([
            'title' => $request->title,
        ]);

        return 'Post updated!';

    }*/
    /* Vamos a ver otra manera de usar nuestro policy en lugar de usar un middleware usar authorize 
        Este metodo viene incluido en el controlador. Viene de extends Controller
        Este controlador incluye algunos traits con metodos utiles como el validate o en este caso el metodo authorize
        Entonces uso este metodo para preguntar si el usuario puede actualizar el post
                    $this->authorize('update',$post);
        ERROR:  Tests\Feature\UpdatePostTest::guests_cannot_update_posts
                Response status code [403] is not a redirect status code.
                Failed asserting that false is true.
        SOLUCION:  O modifico el Handler.php (paso) o en la ruta uso un middleware para asegurar que esta conectado
            ->middleware('auth')
                o usar grupos de rutas
     */

//    public function update(Post $post, Request $request)
    public function update(Post $post, UpdatePostRequest $request)  //si uso formrequest. Debo importarlo
    {
        // $this->authorize('update',$post);  // si uso formrequest no necesito llamar a este metodo
        $post->update([
            'title' => $request->title,
        ]);

        return 'Post updated!';
    }

/*
    public function create()
    {
        $this->authorize('create', Post::class);
    }

    public function store(Request $request)
    {
        $this->authorize('create', Post::class);
        $request->user()->posts()->create([
            'title' => $request->title,
        ]);
        return new Response('Post created', 201);
    }
    public function edit(Post $post)
    {
        $this->authorize('update', $post);
    }

    public function update(Post $post, UpdatePostRequest $request)
    {
        $post->update([
            'title' => $request->title,
        ]);
        return 'Post updated!';
    }*/
/*  */
    public function store(Request $request)
    {
        /* una opcion poniendo user_id en fillable 
        Post::create([
            'user_id'=>$request->user()->id,
            'title' => $request->title,
        ]); */

        /* otra opcion si establezco la relacion un user hasmany posts en el PostController 
                $request->user()->post()->create([
                    'title' => 'New post',
                ]);
         */

        /* con la linea de auhorize hago que la prueba unathorized_users_cannot_create_posts pase  */
        // $this->authorize('create', new Post); // en lugar del new Post se suele pasar el nombre de la clase, del modelo o del recurso en cuestion
        $this->authorize('create', Post::class); 

        $request->user()->post()->create([
            'title' => 'New post',
        ]);
        return new Response('Post created!!', 201);
    }

    /* a partir del metodo store, si quiero crear el formulario para probar esto y creo el  metodo create tb puedo llamar al metodo authorize 
                $this->authorize('create', Post::class); 
        reutilizando el nombre del metodo
        Si el usario tiene permiso para creor posts deberia tener permiso para ver el formulario para crear posts y sino lo tiene no lo tiene para ambos
    */    
    public function create()
    {
        $this->authorize('create', Post::class); 

    }

    /* Lo mismo si voy a crear la accion edit puedo usar authorize con update y pasar el post en cuestion como vble*/
    public function edit(Post $post)
    {
        $this->authorize('update', $post);

    }

}
