@extends('layout')
@section('content')
<script src="https://www.google.com/recaptcha/api.js?render={{env('GOOGLE_RECAPTCHA_KEY')}}"></script>
<div class="container">
    <h2 class="mt-5">Registro</h2>

    <form method="POST" action="{{ route('registrar') }}" class="mt-4" id="formReg">
        @csrf
        @if (session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
        @endif
        @if (session('throttle_error'))
        <div class="alert alert-danger">
            {{session('throttle_error') }}
        </div>
        @endif
   
        <input type="hidden" id="recaptchaKey" name="recaptcha" value="{{ env('GOOGLE_RECAPTCHA_KEY') }}"> 
       

        <div class="form-group">
            <label for="nombre">Nombre</label>
            <input type="text" id="nombre" name="nombre" placeholder="Ej. Juan Pérez" maxlength="60" class="form-control" required autocomplete="name" >
        
            <span id="nombreError" class="text-danger"></span>
            @error('nombre')
            <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="email">Correo</label>
            <input type="email" id="email" name="correo" placeholder="ejemplo@correo.com" class="form-control" required autocomplete="email">
            <span id="emailError" class="text-danger"></span>
            @error('email')
            <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="password">Contraseña</label>
            <input type="password" maxlength="60" id="password" placeholder="******" name="contrasena" class="form-control" required
                autocomplete="current-password">
            <span id="passwordError" class="text-danger"></span>
            @error('password')
            <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="confirm_password">Confirmar Contraseña</label>
            <input type="password" id="confirm_password" placeholder="******" name="confirm_contrasena" class="form-control" required
                autocomplete="current-password">
            <span id="confirmPasswordError" class="text-danger"></span>
            @error('confirm_contrasena')
            <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>

        <button type="submit" id="btn" class="btn btn-primary mt-3">Registrarme</button>

        <div class="mt-3">
            <a href="{{ route('inicioSesion') }}" class="btn btn-link">¿Ya tienes una cuenta? Inicia sesión</a>
        </div>

    </form>
</div>
<script src="{{ asset('js/recaptchaRegistro.js') }}"></script>
@endsection