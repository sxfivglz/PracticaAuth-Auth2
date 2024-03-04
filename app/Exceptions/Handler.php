<?php

namespace App\Exceptions;

use Illuminate\Validation\ValidationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\QueryException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Throwable;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Exceptions\ThrottleRequestsException;


class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }
    public function render($request, Throwable $exception)
{
    if ($exception instanceof InvalidCredentialsException) {
        return response()->json(['error' => $exception->getMessage()], 401);
        
    }

    if ($exception instanceof MethodNotAllowedHttpException) {
        Log::error("Error de petición http: " . $exception->getMessage(). " en la ruta: " . $request->fullUrl(). " con la IP: " . $request->ip());
        return redirect()->back()->with(['error' => 'Acción no permitida.']);

    }

    if ($exception instanceof NotFoundHttpException) {
        Log::error("Error de petición http: " . $exception->getMessage(). " en la ruta: " . $request->fullUrl(). " con la IP: " . $request->ip());
        return redirect()->back()->with(['error' => 'Recurso no encontrado.']);
    }

    if ($exception instanceof ModelNotFoundException) {
        Log::error("No se encontró el modelo: " . $exception->getMessage(). " en la ruta: " . $request->fullUrl(). " con la IP: " . $request->ip());
        return redirect()->back()->with(['error' => 'Recurso no encontrado.']);
    }
   if ($exception instanceof ThrottleRequestsException) {
    $retryAfter = $exception->getHeaders()['Retry-After'] ?? null;
    $remainingAttempts = $exception->getHeaders()['X-RateLimit-Remaining'] ?? null;

    if ($remainingAttempts !== null && $retryAfter !== null) {
        // $retryAfterInMinutes = round($retryAfter / 60);
        // $mensajeError = "Demasiadas solicitudes. Inténtelo de nuevo en $retryAfterInMinutes minutos.";
      //Mostrar los minutos y segundos restantes en 60 segundos
        $retryAfterInMinutes = floor($retryAfter / 60);
        $retryAfterInSeconds = $retryAfter % 60;
        $mensajeError = "Demasiadas solicitudes. Inténtelo de nuevo en $retryAfterInMinutes minutos y $retryAfterInSeconds segundos.";
        

        
    } else {
        $mensajeError = 'Demasiadas solicitudes. Inténtelo de nuevo más tarde.';
    }
    Log::error("Error de petición http: " . $mensajeError. " en la ruta: " . $request->fullUrl(). " con la IP: " . $request->ip().' '. $mensajeError);
    session()->flash('throttle_error', $mensajeError);

    return redirect()->back();
}


    return parent::render($request, $exception);
}
  
}
