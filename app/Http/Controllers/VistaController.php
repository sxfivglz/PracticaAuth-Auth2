<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Log;
use Illuminate\Routing\Exceptions\InvalidSignatureException;

class VistaController extends Controller
{
   public function mostrarFormularioRegistro()
    {
        return view('registro');
    }

    public function mostrarFormularioInicioSesion(Request $request)
    {
        return view('inicioSesion');
    }

    public function mostrarFormulario2FA(Request $request)
{
    if(!Auth::check()){
        return redirect()->route('inicioSesion')->with(['error' => 'Acceso no autorizado, inicie sesión.']);
    }

    if (!$request->hasValidSignature()) {
        return redirect()->route('inicioSesion')->with(['mensaje' => 'Acceso no autorizado, inicie sesión.']);
    }

    $usuario = Auth::user();

    $rutaFirmada = URL::temporarySignedRoute('verificacion.2fa', now()->addMinutes(5), ['id' => $usuario->id]);
    Log::info('Ruta firmada en mostrarFormulario2FA: ' . $rutaFirmada);
    return view('Auth.2FA', ['usuario' => $usuario, 'rutaFirmada' => $rutaFirmada]);
}

    public function mostrarInicioAdministrador(Request $request)
    {
       $usuario = Auth::user();
        if (!$request->hasValidSignature()) {
            return redirect()->route('inicioSesion')->with(['mensaje' => 'Acceso no autorizado, inicie sesión.']);
        }

        $url = URL::current();
        Log::info('URL en mostrarInicioAdministrador: ' . $url);

       
        return view('Auth.inicioAdmin', ['usuario' => $usuario]);
    }

    public function mostrarInicioUsuario(Request $request)
    {
    
        if (!$request->hasValidSignature()) {
               return redirect()->route('inicioSesion')->with(['mensaje' => 'Acceso no autorizado, inicie sesión.']);
        }

        $usuario = Auth::user();
        return view('Auth.inicioUsuario', ['usuario' => $usuario]);
    }

    public function mensajeCorreo(Request $request)
    {
        if (!$request->hasValidSignature()) {
               return redirect()->route('inicioSesion')->with(['mensaje' => 'Acceso no autorizado, inicie sesión.']);
        }

        return view('correo.2FA');
    }

}
