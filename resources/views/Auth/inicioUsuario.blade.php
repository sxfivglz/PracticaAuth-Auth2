@extends('layout')
@section('content')
<div class="container text-center mt-5">
   <h1>Hola, {{ $usuario->nombre }}</h1>
<p>Correo: {{ $usuario->correo }}</p>


<form method="POST" action="{{ route('cerrarSesion') }}">
    @csrf
    <button type="submit" class="btn btn-danger ">
        Cerrar sesión
    </button>
</form>

</div>

@endsection