
@extends('/layout')
@section('content')
@section('title', 'Código de autenticación')
<p>Hola, {{$usuario['nombre']}}</p>

<p>Aquí está tu código de autenticación de dos factores:</p>

<h2>{{ $codigo2fa }}</h2>

<p>Por favor, utiliza este código para completar el proceso de inicio de sesión de dos factores.</p>

<p>Este código expirará en 2 minutos.</p>

<p>¡Gracias!</p>


@endsection
