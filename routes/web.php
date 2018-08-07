<?php

use App\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

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
Route::put('admin/posts/{post}', function (Post $post, Request $request) {

    $post->update([
        'title' => $request->title,
    ]);

    return 'Post updated!';

})->middleware('can:update,post');

Route::get('login', function () {
    return ('Login');
})->name('login');
