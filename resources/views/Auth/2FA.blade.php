@extends('/layout')
@section('title', 'Verificación de Dos Factores')
@section('content')
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="verification-container">
                <h2 class="verification-title mb-3">Verificación de cuenta</h2>

                @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
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
                                autofocus maxlength="6">
                    <span id="codigo_2fa_error" style="color: red;"></span>
                            <input type="hidden" name="id" value="{{$usuario->id}}">
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
                            <input type="hidden" name="id" value="{{$usuario->id}}">

                            <button type="submit" class="btn btn-secondary">Reenviar correo</button>
                        </div>
                    </div>
                </form>
                {{-- Boton logout --}}
                <form method="POST" action="{{ route('cerrarSesion') }}">
                    @csrf
                    <div class="form-group row mb-0">
                        <div class="col-md-8 offset-md-4 mt-4">
                            <button type="submit" class="btn btn-danger">Cerrar sesión</button>
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
        //Validar el campo de texto para que solo acepte números y habilite el boton al agregar el dato como se espera además de agregar el mensaje de "solo números"
        var codigo2fa = document.getElementById('codigo_2fa');
        var errorCodigo2fa = document.getElementById('codigo_2fa_error');
        codigo2fa.addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '');
            if (this.value.length == 6) {
                
                document.querySelector('button[type="submit"]').removeAttribute('disabled');

            } else {
                errorCodigo2fa.textContent = 'El codigo debe ser numérico';
                document.querySelector('button[type="submit"]').setAttribute('disabled', true);
            }
        });
    });
</script>
@endsection