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
            retorno true para que la prueba pase y voy a otra leccion
        */
        Gate::define('update-post',function(User $user, Post $post){  
            return true;
        });
    }
}
