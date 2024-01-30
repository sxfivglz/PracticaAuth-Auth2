<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\VistaController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', [VistaController::class, 'mostrarFormularioRegistro'])->name('registro');
Route::post('/registrar', [UsuarioController::class , 'registrarUsuario'])->name('registrar');
Route::get('/inicioSesion', [VistaController::class, 'mostrarFormularioInicioSesion'])->name('inicioSesion');
Route::post('/iniciarSesion', [UsuarioController::class, 'iniciarSesion'])->name('iniciarSesion');


Route::middleware(['auth'])->group(function () {
    Route::get('/correo', [VistaController::class, 'mensajeCorreo'])->name('correo');
    Route::get('/verificacion-2fa', [VistaController::class,'mostrarFormulario2FA'])->name('verificacion.2fa');
    Route::post('/verificacion-2fa', [UsuarioController::class, 'verificar2FA'])->name('verificar.2fa');
    Route::get('/inicioAdmin', [VistaController::class, 'mostrarInicioAdministrador'])->name('inicioAdmin');
    Route::get('/inicioUsuario', [VistaController::class, 'mostrarInicioUsuario'])->name('inicioUsuario');
    Route::put('/actualizarUsuario', [UsuarioController::class, 'actualizarUsuario'])->name('actualizarUsuario');
    Route::get('/cerrarSesion', [UsuarioController::class, 'cerrarSesion'])->name('cerrarSesion');
});