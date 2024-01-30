<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class VerificarRol
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
     public function handle($request, Closure $next, ...$roles)
    {
    //     try {
    //         foreach ($roles as $rol) {
    //             if (Auth::check() && Auth::user()->tieneRol($rol)) {
    //                 return $next($request);
    //             }
    //         }
    //         /*Mandarlo al archivo de logs */
    //         Log::error('El usuario no tiene los permisos necesarios para acceder a la ruta: ' . $request->path(), [
    //             'usuario' => Auth::user()->nombre,
    //             'rol' => Auth::user()->rol->nombre,
    //         ]);
    //         abort(403, 'No tienes los permisos necesarios.');
    //     } catch (\Exception $e) {
    //         Log::error('Error en VerificarRol: ' . $e->getMessage());
    //         return response()->json(['error' => 'Ocurrió un error.'], 500);
    //     }
    // }
}
}
