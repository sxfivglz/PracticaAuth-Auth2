@extends('/layout')
@section('title', 'Verificación de Dos Factores')
@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Verificación de Dos Factores</div>

                <div class="card-body">
                    @if(session('mensaje'))
                    <div class="alert alert-danger">{{ session('mensaje') }}</div>
                    @endif

                    <form method="POST" action="{{ url('/verificacion-2fa') }}">
                        @csrf

                        <div class="form-group row">
                            <label for="codigo_2fa" class="col-md-4 col-form-label text-md-right">Código 2FA</label>

                            <div class="col-md-6">
                                <input id="codigo_2fa" type="text" class="form-control" name="codigo_2fa" required
                                    autofocus>
                            </div>
                        </div>

                        <div class="form-group row mb-0">
                            <div class="col-md-8 offset-md-4 mt-4">
                                <button type="submit" class="btn btn-primary">
                                    Verificar
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection