<?php

namespace App\Providers;

use App\{User, Post};
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

            */
        Gate::define('update-post',function(User $user, Post $post){  
            return $user->isAdmin() || $user->owns($post);
        });
    }
}
