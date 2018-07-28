<?php

namespace App\Providers;

use App\{User, Post};
use App\Policies\PostPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        'App\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        /* Defino el Gate con el nombre del gate y después la funcion anónima con la lógica.
            Debo enviar el $post para saber si el usuario esta autorizado a pasar un post, y Laravel nos devuelve de manera automatica el usuario, por eso tb lo debo poner
            Debo llamar a los dos modelos al principio, User y Post.\Tests\Unit\PolicyPolicyTest
            retorno true para que la prueba pase y voy a otra leccion.
                Gate::define('update-post',function(User $user, Post $post){  
                    return true;
                });

            Para seguir con la leccion creo una segunda prueba para guest_can_not_update_posts(). Voy pa ya
                Gate::define('update-post',function(User $user, Post $post){  
                    return $user->role==='admin' ; //solo retorno true si el usuario es admin
                });

            Llegado el momento ademas de ser admin debe ver si es el creador del post
                Gate::define('update-post',function(User $user, Post $post){  
                    return $user->role==='admin' || $user->id=== $post->user_id; //solo retorno true si el usuario es admin o si el usuario es el que ha escrito el post
                });
            
            Aunque mejor que preguntar si el role es admin, es preguntar directamente si es admin con el metodo isAdmin que debo crear en el modelo User
            Creo la prueba unitaria para ver que pasa el metodo isAdmin
            Pasa 
            En AuthServiceProvider
                    public function isAdmin(){
                        return $this->role==='admin';
                    }

                Gate::define('update-post',function(User $user, Post $post){  
                    return $user->isAdmin() || $user->id=== $post->user_id;
                });

            Ahora hago algo similar en la segunda parte de la comprobacion. Creo el metodo owns() podria llamarse isAuthor que me gusta mas pero a Duilio no
            ya que dice que es un poco mas generico.
            Lo hacemos a partir de una prueba unitaria: php artisan make:test UserModelTest --unit

                Gate::define('update-post',function(User $user, Post $post){  
                    return $user->isAdmin() || $user->owns($post);
                });

            Simplifico usando el metodo before del gate. Lo pongo al final con las demas reglar

        */

        /* Solo pueden borrar posts los autores
                Gate::define('delete-post',function(User $user, Post $post){  
                    return $user->owns($post);
                });
            y no que esten publicados
            Gate::define('delete-post',function(User $user, Post $post){  
                return $user->owns($post) && !$post->isPublished();  //debo crear este metodo. Lo hago en el modelo Post. OJO estoy negando $post
            });
            o: que sean admin o (autores y no publicado)
            
            Gate::define('delete-post',function(User $user, Post $post){  
                return $user->isAdmin() || ($user->owns($post) && !$post->isPublished());  
            });

            Segun Duilio la logica del gate es un poco compleja y que estoy repitiendo la comprobacion isAdmin para cada una de las reglas.\App\User
            LAravel me permite simplificar esto con usando el método before
            
            lo pongo al final con las demas reglas.
            */

            /* Defino una funcion callback que siempre va a ser llamada antes de revisar cualquiera de las reglas SIEMPRE
                Si lanzo las pruebas sin poner nada, la funcion no ha alterado el comportamiento de nuestras reglas
                    Gate::before(function(User $user){

                    })
                Si retorna true siemrpe voy a otorgar permiso, así que aquellas pruebas que esperan un false fallan
                    Gate::before(function(User $user){
                        return true;
                    })
                Si retorna false siempre deniego el permiso con lo que las pruebas que esperan true fallan
                    Gate::before(function(User $user){
                        return false;
                    })
                Si retorna null Laravel ejecutara las pruebas sin tenerlo en cuenta
                    Gate::before(function(User $user){
                        return null;
                    })

            Así que mi logica será que si el user es Admin retorne verdadero y que si no lo es retorne null. Puedo ponerlo o no. Lo pongo para verlo mas claro

            Las pruebas pasan, así que puedo eliminar la comprobacion de si es admin en el resto de los gates
        
            Gate::before(function(User $user){
                if($user->isAdmin()){
                    return true;
                }
                return null;

            });

            Gate::define('update-post',function(User $user, Post $post){  
                return $user->owns($post);
            });

            Gate::define('delete-post',function(User $user, Post $post){  
                return $user->owns($post) && !$post->isPublished();  
            });
        */
        
        /**Uso de clases y metodos en lugar de funciones anonimas en los gates */
        /* Puede llegar a suceder que si tengo muchos gates el fichero AuthServiceProvider puede crecer mucho,
        así que usaremos de clases y metodos en lugar de funciones anonimas en los gates 
        
        Genero una nueva clase con el metodo POLICY php artisan make:policy PostPolicy

        Gate::before(function(User $user){
            if($user->isAdmin()){
                return true;
            }
            return null;

        });

        Gate::define('update-post','\App\Policies\PostPolicy@update');

        Gate::define('delete-post','\App\Policies\PostPolicy@delete');


         Generalmente cuando estamos trabajando con un recurso como por ejemplo un Post, nos hace falta
        crear reglas para Visualizar, crear, actualizar y eliminar dicho recurso, es decir CRUD
        Laravel nos permite definir reglas usando el metodo resource de Gate.
        Para usarlo en AuthServiceProvider pasamos como primer argumento el nombre de nuestro recurso en singular o plurar i.e post
        y como segundo argumento la clase qye queremos utilizar. En nuestro caso PostPolicy 
            Gate::resource('post',PostPolicy::class);
        Esto es equivalente a crear reglas para view, create, update, delete del recurso Post
        Ejecuto pruebas y fallan
        ERROR: Fallan varias pruebas porque el nombre de las reglas va a ser diferente
        SOLUCION: Sustituir en las rutas donde pone update-post por post.update es el nombre del recurso post + . + metodo  Si hubiera llamado posts al recurso seria posts + . + metodo
                                                    delete-post por post.delete
        Cambio los nombres y error
        ERROR: No existe la clase PostPolicy
        SOLUCION: importala al comienzo del fichero

        Cabe destacar que PostPolicy es una clase usada por el framework pero no deja de ser una clase de PHP
        Por eso tambien puedo hacer pruebas unitarias sobre la clase.
        Para probarlo como ejemplo vamos a modificar la prueba authors_can_update_posts. Ver allí lo que hago. Esto no es necesario, es un ejemplo que puede ser interesante para logicas mas complejas
        
        Que pasa si quiero otros metodos distintos a los que da el resource CRUD
        Se puede hacer pasando un tercer argumento que será un array asociativo donde las llaves van a ser los nombres de las reglas y los valores
        los nombre de los metodos que se quieren llamar

        */

        Gate::before(function(User $user){
            if($user->isAdmin()){
                return true;
            } 
            return null;

        });

        //Gate::resource('post',PostPolicy::class); //Esto es equivalente a crear reglas para view, create, update, delete del recurso Post Asi que me sobran los otros gate
        /* si quiero ademas del CRUD otros metodos lo hago con array asociativo array asociativo donde las llaves van a ser los nombres de las reglas y los valores
        los nombre de los metodos que se quieren llamar
        Aqui los llamo updatePost y deletePost entonces tengo que cambiar el nombre de los metodos en PostPolcy
        */    
        Gate::resource('post',PostPolicy::class,[
            'update'=>'updatePost',
            'delete'=>'deletePost', 
        ]);
    }

}
