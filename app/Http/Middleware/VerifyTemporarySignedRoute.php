<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Routing\Middleware\ValidateSignature;
use Illuminate\Support\Facades\Log;
use Illuminate\Routing\Exceptions\InvalidSignatureException;

class VerifyTemporarySignedRoute extends ValidateSignature
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, ...$args)
    {
        try {
            return parent::handle($request, $next, ...$args);
        } catch (InvalidSignatureException $e) {
            Log::error('Ruta rechazada por firma no válida: ' . $request->fullUrl() . ' - ' . $e->getMessage(), ['ip' => $request->ip()]);
            return redirect()->route('inicioSesion')->with('error', 'Acceso no autorizado, inicie sesión.');
        }
    }
}
