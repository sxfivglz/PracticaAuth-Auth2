@extends('layout')

@section('content')
<div class="container text-center mt-5 ">
    @if(session('exito'))
    <div class="alert alert-success" id="exitoMensaje" role="alert">
        {{ session('exito') }}
    </div>
    @endif
    
    @if(session('error'))
    <div class="alert alert-danger" id="errorMensaje" role="alert">
        {{ session('error') }}
    </div>
    @endif
    
    <h1>Hola, {{ $usuario->nombre }}</h1>
    <p>Correo: {{ $usuario->correo }}</p>

    <button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#editModal">
        Editar datos
    </button>
   <form method="POST" action="{{ route('cerrarSesion') }}">
    @csrf
    <button type="submit" class="btn btn-danger ">
        Cerrar sesión
    </button>
</form>
</div>

<!-- Edit User Modal -->
<div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editModalLabel">Editar usuario</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="POST" action="{{ route('actualizarUsuario') }}">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="nombre" class="form-label">Nombre</label>
                        <input type="text" class="form-control" id="nombre" name="nombre" 
                            value="{{ $usuario->nombre }}">
                    </div>
                    <input type="hidden" id="correo" name="correo" value="{{ $usuario->correo }}">
                   
                    <div class="mb-3">
                        <label for="correo" class="form-label">Correo</label>
                        <input type="text" class="form-control"  name="correo"
                            value="{{ $usuario->correo }}" disabled>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Guardar cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(function(){
            var exitoMensaje = document.getElementById('exitoMensaje');
            if (exitoMensaje) {
                exitoMensaje.style.display = 'none';
            }
        }, 2000);

        setTimeout(function(){
            var errorMensaje = document.getElementById('errorMensaje');
            if (errorMensaje) {
                errorMensaje.style.display = 'none';
            }
        }, 2000);
    });
</script>

@endsection