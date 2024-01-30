@extends('layout')
@section('content')
<script src="https://www.google.com/recaptcha/api.js?render={{env('GOOGLE_RECAPTCHA_KEY')}}"></script>
<div class="container">
    <h2 class="mt-5">Inicio de sesión</h2>

    @if (session('exito'))
    <div class="alert alert-success">
        {{ session('exito') }}
    </div>
    @endif
    @if($errors->any())
    <div class="alert alert-danger">
            @foreach($errors->all() as $error)
            {{ $error }}
            @endforeach
    </div>
    @endif
    <form method="POST" action="{{ route('iniciarSesion') }}" class="mt-4" id="formLog">
        @csrf

        @if (session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
        @endif

        @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
        @endif

        <input type="hidden" id="recaptchaKey" name="recaptcha" value="{{ env('GOOGLE_RECAPTCHA_KEY') }}">

        <div class="form-group">
            <label for="email">Correo</label>
            <input type="email" id="email" name="correo" placeholder="ejemplo@correo.com" class="form-control" required
                autocomplete="email">
            <span id="emailError" class="text-danger"></span>
            @error('email')
            <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="password">Contraseña</label>
            <input type="password" id="password" placeholder="******" name="contrasena" class="form-control" required
                autocomplete="current-password">
            <span id="passwordError" class="text-danger"></span>
            @error('password')
            <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>

        <button type="submit" id='btn' class="btn btn-primary mt-3 ">Iniciar sesión</button>

        <div class="mt-3 form-group">
            <a href="{{ route('registro') }}" class="btn btn-link">¿No tienes una cuenta? Regístrate</a>
        </div>
    </form>
</div>
<script src="{{ asset('js/recaptchaLogin.js') }}"></script>
<script>

    $(document).ready(function() {
        setTimeout(function() {
            $(".alert").fadeOut('fast');
        }, 3000); 
    });
</script>
@endsection