@extends('layouts.app') 
@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <h1>Posts</h1>
            <table class="table">
                <thead class="thead-dark">
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Título</th>
                        <th scope="col">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                @foreach ($posts as $post)
                <tr>
                    <th scope="row">{{ $post->id }}</th>
                    <td>{{ $post->title }}</td>
                    <td>
                        <a href="#" class="btn btn-default">Editar</a>
                        <a href="#" class="btn btn-default">Eliminar</a>
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