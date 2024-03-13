<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Log;
use App\Models\Usuario;

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
        
    if (!$request->hasValidSignature()) {
        return redirect()->route('inicioSesion')->with(['mensaje' => 'Acceso no autorizado, inicie sesión.']);
    }
    Log::info('Request en mostrarFormulario2FA: ' . $request->usuario);
    $usuario2FA = Usuario::where('id', $request->usuario)->first();
    Log::info('Usuario en mostrarFormulario2FA: ' . $usuario2FA);
    if (!$usuario2FA) {
        Log::info('Usuario no encontrado en mostrarFormulario2FA');
        return redirect()->route('inicioSesion')->with(['mensaje' => 'Acceso no autorizado.']);
    }
    Log::info('Usuario en mostrarFormulario2FA: ' . $usuario2FA);
    $rutaFirmada = URL::temporarySignedRoute('verificacion.2fa', now()->addMinutes(5), ['id' => $usuario2FA->id]);
    Log::info('Ruta firmada en mostrarFormulario2FA: ' . $rutaFirmada);
    return view('Auth.2FA', ['usuario' => $usuario2FA, 'rutaFirmada' => $rutaFirmada]);
    
    }

    public function mostrarInicioAdministrador(Request $request)
    {
    try{
       $usuario = Auth::user();
        if(Auth::check() && !$usuario->roles()&& !$request->hasValidSignature()){
            Log::info('Acceso no autorizado en mostrarInicioAdministrador');
            return redirect()->route('inicioSesion')->with(['mensaje' => 'Acceso no autorizado, inicie sesión.']);
        }
        $url = URL::current();
        Log::info('URL en mostrarInicioAdministrador: ' . $url);
       
        return view('Auth.inicioAdmin', ['usuario' => $usuario]);
    }catch(InvalidSignatureException $e){
        Log::info('Error en mostrarInicioAdministrador: ' . $e->getMessage());
        return redirect()->route('inicioSesion')->with(['mensaje' => 'Acceso no autorizado, inicie sesión.']);
    }catch(\Exception $e){
        Log::info('Error en mostrarInicioAdministrador: ' . $e->getMessage());
        return redirect()->route('inicioSesion')->with(['mensaje' => 'Acceso no autorizado, inicie sesión.']);
    }
    }

    public function mostrarInicioUsuario(Request $request)
    {
        try{
        $usuario = Auth::user();
        if(Auth::check() && !$usuario->roles()&& !$request->hasValidSignature()){
            Log::info('Acceso no autorizado en mostrarInicioUsuario');
            return redirect()->route('inicioSesion')->with(['mensaje' => 'Acceso no autorizado, inicie sesión.']);
        }
        return view('Auth.inicioUsuario', ['usuario' => $usuario]);
    }catch(InvalidSignatureException $e){
        Log::info('Error en mostrarInicioUsuario: ' . $e->getMessage());
        return redirect()->route('inicioSesion')->with(['mensaje' => 'Acceso no autorizado, inicie sesión.']);
    }catch(\Exception $e){
        Log::info('Error en mostrarInicioUsuario: ' . $e->getMessage());
        return redirect()->route('inicioSesion')->with(['mensaje' => 'Acceso no autorizado, inicie sesión.']);
    }
    }

    public function mensajeCorreo(Request $request)
    {
        if (!$request->hasValidSignature()) {
               return redirect()->route('inicioSesion')->with(['mensaje' => 'Acceso no autorizado, inicie sesión.']);
        }

        return view('Correo.2FA');
    }

}
