@extends('/layout')
@section('title', 'Verificación de Dos Factores')
@section('content')
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="verification-container">
                <h2 class="verification-title mb-3">Verificación de cuenta</h2>

                @if(session('mensaje'))
                <div class="alert alert-danger">{{ session('mensaje') }}</div>
                @endif
                @if(session('exito-codigo'))

                <div class="alert alert-success">{{ session('exito-codigo') }}</div>
                @endif

                <form method="POST" action="{{ route('verificar.2fa') }}">
                    @csrf
                    <div class="form-group row">
                        <label for="codigo_2fa" class="col-md-4 col-form-label text-md-right">Ingresa el código que
                            llegó a tu dirección de correo</label>
                        <div class="col-md-6">
                            <input id="codigo_2fa" type="text" class="form-control" name="codigo_2fa" required
                                autofocus>
                        </div>
                    </div>

                    <div class="form-group row mb-0">
                        <div class="col-md-8 offset-md-4 mt-4">
                            <button type="submit" class="btn btn-primary">Verificar</button>
                        </div>
                    </div>
                </form>

                <form method="POST" action="{{ route('reenviar.2fa') }}">
                    @csrf
                    <div class="form-group row mb-0">
                        <div class="col-md-8 offset-md-4 mt-4">
                            <button type="submit" class="btn btn-secondary">Reenviar correo</button>
                        </div>
                    </div>
                </form>
                {{-- Boton logout --}}
                <form method="POST" action="{{ route('cerrarSesion') }}">
                    @csrf
                    <div class="form-group row mb-0">
                        <div class="col-md-8 offset-md-4 mt-4">
                            <button type="submit" class="btn btn-danger">Salir</button>
                        </div>
            </div>
        </div>
    </div>
</div>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        setTimeout(function() {
            var mensajeElement = document.getElementById("mensaje");
            if (mensajeElement) {
                mensajeElement.style.display = "none";
            }
        }, 2000);
        setTimeout(function() {
            var exitoCodigoElement = document.getElementById("exito-codigo");
            if (exitoCodigoElement) {
                exitoCodigoElement.style.display = "none";
            }
        }, 2000);
    });
</script>
@endsection