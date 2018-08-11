@extends('layouts.app') 
@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <h1>Posts</h1>
            {{-- usando la opcion can podemos poner el boton de create si tiene posibilidades 
                pasamos en nombre del metodo y el nonmbre de la clase
                
                o el nombre del metodo del policy y el nombre del modelo si disponemos de el como en update
                    @can('update',$post)
                para pode verlo debo crear un usuario admin. Lo hago con tinker
                    obtengo el usuario: $user=App\User::first()
                    $user->role='author';
                    $user->save();
                Si en lugar de author el role es admin
                    $user->role='admin'; 
                    $user->save();
                ademas verá todos los botones incluso de los que no es el autor
                --}}
            @can('create',App\Post::class)
                <p><a href="#" class="btn btn-primary">Crear post</a></p>
            @endcan
            <table class="table">
                <thead class="thead-dark">
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Título</th>
                        <th scope="col">Autor</th>
                        <th scope="col">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                @foreach ($posts as $post)
                <tr>
                    <th scope="row">{{ $post->id }}</th>
                    <td>{{ $post->title }}</td>
                    <td>{{ $post->author->name }}</td>   {{-- esto da un error. Si lo pongo y paso las pruebas 
                                                        ERROR: ErrorException: Trying to get property 'name' of non-object 
                                                        SOUCION: Voy al modelo de Post.pho y defino la relacion entre posts y users. o hago con la relacion author
                                                                Las pruebas y la vista pasan
                                                        COn tinke voy a hacer que un post pertenezca a mi usuario
                                                        php artisan tinker
                                                        $posts= App\Post::all();  devuelve una coleccion con todos los posts
                                                        $posts->random()->forceFill(['user_id'=>1])->update();   usando el metodo ramdom de la coleccion de laravel voy a forzar que un post pertenezca a mi usuario que es el 1 y lo actualizo. Uso forceFill porque user_id no es fillable, así me lo salto
                                                        lo hago varias veces
                                                        Si voy al navegador veo el resultado
                                                        --}}
                    <td>
                        {{-- una opcion para mostrar u ocultar cosas --}}
                        @if (Gate::allows('update',$post))
                            {{-- Para acceder al formulario de editar creo una ruta nueva en web.php:  Route::get('posts/{post}/edit', 'PostController@edit')->name('posts.edit');
                            y Llamo en el href a la ruta --}}
                            <a href="{{ route('posts.edit',$post) }}" class="btn btn-default">Editar</a>
                        @else 
                        {{-- ejemplo de que podría hacer si no soy el author y no puedo modificar --}}
                            <a href="#">Reportar problema</a>
                        @endif
                        @if (Gate::allows('delete',$post))
                            <a href="#" class="btn btn-default">Eliminar</a>
                        @endif

                        {{-- otra manera mas facil 
                            En lugar de la directiva Gate uso la directiva can y preguntamos si el usuario puedo update --}}
                        @can('update',$post)
                            <a href="{{ route('posts.edit',$post) }}" class="btn btn-default">Editar</a>
                        {{-- ejemplo de que podría hacer si no soy el author y no puedo modificar 
                            pero primero tengo que crear la regla report en el policy porque si no existe el policy da false por defecto--}}
                        @elsecan('report',$post)
                            <a href="#">Reportar problema</a>
                        @endcan
                        @can('delete',$post)
                            <a href="#" class="btn btn-default">Eliminar</a> 
                        @endcan
                    </td>
                </tr>
                @endforeach
                </tbody>
            </table>
            {{-- la siguiente linea me da los links de la paginacion que consigo llamando al metodo paginate en postcontroller --}}
            {{ $posts->links() }}

        </div>
    </div>
</div>
@endsection