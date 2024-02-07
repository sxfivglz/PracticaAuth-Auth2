<?php

namespace App\Listeners;

use App\Events\InicioSesionExitoso;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class LogInicioSesionExitoso implements ShouldQueue
{
    public function handle(InicioSesionExitoso $event)
    {
        Log::info('Inicio de sesión exitoso: ' . $event->user->correo . ' con la IP: ' . $event->request->ip());
    }
}