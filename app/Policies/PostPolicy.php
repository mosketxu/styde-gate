<?php

namespace App\Policies;

use App\{User,Post};
use Illuminate\Auth\Access\HandlesAuthorization;

class PostPolicy
{
    use HandlesAuthorization;

    /* Creo esta clase para hacer los gates del AuthServiceProvider fuera del fichero y mejorar si crece mucho */

    /* Creo un nuevo metodo update para el gate update-post
        copio la logica del los gates y lo paso al metodo
        importo los modelo user y post
        Las pruebas pasan. Normal porque no he hecho nada.
        Tengo que enlazar el metodo del policy al gate.
        Para ello en AuthServiceProvider elimino el código de la funcion anonima del gate update-post y lo sustituyo por el policy update
            \App\Policies\PostPolicy@update
        Las pruebas pasan. Como prueba de que esto funciona hago que la funcion retorne siempre falso y veo que alguna prueba falla
        Repito la jugada para el metodo delete-post.
        Las pruebas pasan

        Generalmente cuando estamos trabajando con un recurso como por ejemplo un Post, nos hace falta
        crear reglas para Visualizar, crear, actualizar y eliminar dicho recurso, es decir CRUD
        Laravel nos permite definir reglas usando el metodo resource de Gate.
        Para usarlo en AuthServiceProvider pasamos como primer argumento el nombre de nuestro recurso en singular o plurar i.e post
        y como segundo argumento la clase qye queremos utilizar. En nuestro caso PostPolicy
            Gate::resource('post',PostPolicy::class);
    */

    public function updatePost(User $user, Post $post)
    {  
        //return false; //si lo pongo veo que alguna prueba falla
        return $user->owns($post);
    }

    public function deletePost(User $user, Post $post){  
        return $user->owns($post) && !$post->isPublished();  
    }
}
