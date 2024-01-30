<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use App\Events\RegistroExitoso;
use App\Events\InicioSesionExitoso;
use App\Listeners\LogRegistroExitoso;
use App\Listeners\LogInicioSesionExitoso;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
     protected $listen = [
        RegistroExitoso::class => [
            LogRegistroExitoso::class,
        ],

        InicioSesionExitoso::class => [
            LogInicioSesionExitoso::class,
        ],
    ];

    public function boot()
    {
        parent::boot();

    }
    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
