<?php

/*  Cuando uso los controladores ya no necesito usar estas clases pero si en PostControler
use App\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
 */
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
 */

Route::get('/', function () {
    return view('welcome');
});

// Comento la línea de este Route y la pongo mas abajo, cuando incluyo el middelware para que se vea mas claro
//Route::put('admin/posts/{post}', function (Post $post, Request $request) {

    // OJO AL ORDEN. SI PONGO EL DE GUEST DEPUES DEl auth()->user()->cant FALLA UNA PRUEBA
    //Cuando todo funcion Duilio propone quitar estos if y cambiarlos por gates
/*    if (auth()->guest()) {
        abort(401);
    }
    
    if (auth()->user()->cant('update', $post)) {
        abort(403);
    }
*/
    /* Explico el cambio de los if de arriba por gates
        Hay que importar el facade Gate
        Llamo al metodo denies que es el contrario de allow:
            Si el usuario no tiene permiso para actualizar el post devuelvo 403
                if (Gate::denies('update',$post)){
                    abort(403);
                } 
            NOTA: LARAVEL DENIEGA EL ACCESO A LOS USUARIOS ANONIMOS DE MANERA AUTOMATICA
        Pruebo
        ERROR: Expected status code 401 but received 403.
        SOLUCION: Es porque en la prueba mandaba el 401. Lo cambio allí y ya está. Pero esto no es lo que quería. Así que lo devuelvo a 401 y
                Laravel nos permite interactuar con los errores  a traves de un middleware
        Comento todo y lo pongo despues con el middlware y lo explico ahí
Route::put('admin/posts/{post}', function (Post $post, Request $request) {

     if (Gate::denies('update',$post)){
        abort(403);
    }    
    
    $post->update([
        'title'=>$request->title,
    ]);

    return 'Post updated!';
});
*/
/* Explico el middleware 
    Laravel nos permite interactuar con el middleware "can" para bloquear por completo el acceso a la ruta.
    El middleware esta definido en App/http/Kernel.php EL middleware se llama authorize y tiene el alias de can
                'can' => \Illuminate\Auth\Middleware\Authorize::class,
    Entonces  interactuamos con el middleware y le preguntamos si el usuario puedo actualizar el post.
    Para ello pongo el nombre del metodo del Policy y enlazo con el modelo post
            })->middleware('can:update,post');            
    Para enalzarlo necesito usar route model binding
            Route::put('admin/posts/{post}', etc
    Es decir en el middleare hago referencia al nombre del parametro que tengo en la ruta. post que tambien uso en la funcion anonimo $post            
    De esta manera no necesito ningun condicional.
    Será laravel el que verá si el usuario tiene permisos y le mandará a 403 o a la pantalla de inicio de sesion 

    Pruebo y falla
    ERROR: Expected status code 401 but received 500.
    SOLUION: pongo $this->withoutExceptionHandling(); y veo que el error es una excepcion de autenticacion, que en el fondo espero porque el usuario no está autenticado
            Illuminate\Auth\AuthenticationException: Unauthenticated.
            Este error se produce por una razon profunda en FoundationExceptionHandler.php, y es que ahí cuando se da este error Laravel reenvia a la ruta login
            Y esta ruta no existe en nuestro proyecto. Así que la creamos dandole el nmbre login
                Route::get('login', function () {
                    return ('Login');
                })>name('login');

    ERROR: Illuminate\Auth\AuthenticationException: Unauthenticated.
    SOLUCION: quitar  $this->withoutExceptionHandling();

    ERROR:  Tests\Feature\UpdatePostTest::guests_cannot_update_posts
            Expected status code 401 but received 302.
    SOLUCION: Esto es porque Laravel intenta redirigir al usuario a la pantalla de inicio de sesion
            Las pruebas pasan


 */

/* Comienzo la siguiete leccion y vamos a pasar la logica a los controladores
    Comento la ruta y la preparo para llevarlo a controlador 
 Route::put('admin/posts/{post}', function (Post $post, Request $request) {

    $post->update([
        'title' => $request->title,
    ]);

    return 'Post updated!';

})->middleware('can:update,post');
*/
/* corto la funcion anonima y pongo el nombre del cotrolador al que voy a llamar 
    creo el nuevo controlador: php artisan make:controller Admin/PostController  
    Ya no necesito importar las clases Post, Request, Gate
    Las pruebas pasan
    Vamos a ver otra manera de usar nuestro policy
            Route::put('admin/posts/{post}', 'Admin\PostController@update')->middleware('can:update,post');
 */
/* Vamos a ver otra manera de usar nuestro policy en lugar de usar un middleware
    quito el middleware
        Route::put('admin/posts/{post}', 'Admin\PostController@update');
    ERROR: Las pruebas fallan
    SOLUCION: En el controlador PostController antes de actualizar el post llamo al metodo authorize

    ERROR/SOLUCION: Tendre que asegurarme que el usuario este conectado con el middleware('auth')

    Tambien podemos usar grupos de rutas
    Meto la ruta que ya tenia
        Route::put('admin/posts/{post}', 'Admin\PostController@update')->middleware('auth');
    en un grupo en el que podría poner un prefix que en este caso no pongo
        quito en la ruta el middleare pq ya esta en el grupo y el nombre de espacio Admin donde llamo al controller
    Las pruebas pasan

    Otra opcion para autorizar suponiendo que estamos usando FormRequest
 */

/*
 Route::middleware('auth')->namespace('Admin\\')->group(function(){
    
    Route::get('admin/posts','PostConroller@index');

    Route::post('admin/posts','PostController@store');
    
    Route::put('admin/posts/{post}', 'PostController@update');
});*/

/* Modifico el grupo añadiendo el prefix admin */
Route::middleware('auth')->namespace('Admin\\')->prefix('admin/')->group(function () {

    Route::get('posts','PostController@index');

    Route::post('posts', 'PostController@store');

    Route::put('posts/{post}', 'PostController@update');
});

 /* Otra opcion para autorizar suponiendo que estamos usando FormRequest
    Creo un FormRequest         php artisan make:request UpdatePostRequest
 */

/* Cuando he hecho el php artisan make:auth ya no me hace falta la ruta
        Route::get('login', function () {
            return ('Login');
        })->name('login');
    Lo pruebo con 
        http://styde-gates.test/admin/posts
    y veo que me redirige al login. Esto lo hace auth
    Tengo un form de login y otro de registro
 */
Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');
