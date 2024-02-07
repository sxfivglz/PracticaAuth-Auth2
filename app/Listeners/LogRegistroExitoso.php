<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Events\RegistroExitoso;
use Iluminate\Support\Facades\Log;

class LogRegistroExitoso implements ShouldQueue
{
  public function handle(RegistroExitoso $event)
  {
    Log::info('Se ha registrado un nuevo usuario: ' . $event->usuario->correo. ' con la IP: ' . $event->request->ip());
  }
}
