@extends('layout')
@section('content')
<div class="container text-center mt-5">
   <h1>Hola, {{ $usuario->nombre }}</h1>
<p>Correo: {{ $usuario->correo }}</p>
<button type="button" class="btn btn-danger ">
    <a style="text-decoration: none" class="text-light" href="{{ route('cerrarSesion') }}">Cerrar sesión</a>
</button>
</div>

@endsection