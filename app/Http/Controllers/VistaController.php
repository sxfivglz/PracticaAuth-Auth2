<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;

class VistaController extends Controller
{
    public function mostrarFormularioRegistro()
    {
        return view('registro');
    }

      public function mostrarFormularioInicioSesion()
    {
        return view('inicioSesion');
    }

    public function mostrarFormulario2FA()
    {
    return view('Auth.2FA');
    }
    public function mostrarInicioAdministrador(){
    $usuario = Auth::user();
    return view('Auth.inicioAdmin', ['usuario' => $usuario]);
    }   

    public function mostrarInicioUsuario(){
        $usuario = Auth::user();
    return view('Auth.inicioUsuario',['usuario' => $usuario]);
    }

    public function mensajeCorreo(){
        return view('correo.2FA');
    }
   
}
