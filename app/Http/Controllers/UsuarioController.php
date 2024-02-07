<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\Usuario;
use App\Rules\ReCaptcha;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use App\Mail\TwoFactorAuthenticationMail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Mail;
use App\Models\Rol;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use PDOException;
use Illuminate\Routing\Exceptions\InvalidSignatureException;

class UsuarioController extends Controller
{
   public function registrarUsuario(Request $request)
    {
    try {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|max:255',
            'correo' => 'required|email|max:255|unique:usuarios',
            'contrasena' => 'required|min:8|',
            'recaptcha' => ['required', new ReCaptcha],
        ], [
            'nombre.required' => 'Se necesita el nombre.',
            'nombre.max' => 'El nombre no debe exceder los 255 caracteres.',
            'correo.required' => 'Se necesita el correo.',
            'correo.email' => 'El correo debe ser válido.',
            'correo.max' => 'El correo no debe exceder los 255 caracteres.',
            'correo.unique' => 'El correo ya se encuentra registrado.',
            'contrasena.required' => 'Se necesita una contraseña.',
            'contrasena.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'recaptcha.required' => 'Se necesita el reCAPTCHA.',
        ]);

        if ($validator->fails()) {
            $errorMessage = $validator->errors();

            Log::error('Error al validar el formulario de registro: ' . $errorMessage, [
                'nombre' => $request->nombre,
                'correo' => $request->correo,
                'location' => 'registrarUsuario',
            ]);

            return redirect()->route('registro')->with(['error' => 'Error al validar el formulario de registro. Contacte al administrador.']);
        }

        // Verificar si hay roles registrados en la tabla
        if (Rol::count() === 0) {
            Log::emergency('No hay roles registrados al intentar registrar un usuario.', [
                'nombre' => $request->nombre,
                'correo' => $request->correo,
                'location' => 'registrarUsuario',
            ]);

            return redirect()->route('registro')->with(['error' => 'Surgió un problema. Contacte al administrador.']);
        }

        // Asignar el rol al usuario
        $rol = Usuario::count() > 0 ? 2 : 1;

        $usuario = Usuario::create([
            'nombre' => $request->nombre,
            'correo' => $request->correo,
            'contrasena' => Hash::make($request->contrasena),
            'rol_id' => $rol,
        ]);

        Log::info('Usuario registrado correctamente', [
            'nombre' => $request->nombre,
            'correo' => $request->correo,
            'rol_asignado' => $rol,
            'location' => 'registrarUsuario',
        ]);
        return redirect()->route('inicioSesion')->with(['exito' => 'Usuario registrado exitosamente.']);

    } catch (PDOException $e) {
        Log::error('Error al registrar al usuario - Error de base de datos: ' . $e->getMessage(), [
            'nombre' => $request->nombre,
            'correo' => $request->correo,
            'location' => 'registrarUsuario',
        ]);

        return redirect()->route('registro')->with(['error' => 'Algo salió mal durante el registro. Contacte al administrador.']);
    } catch (ValidationException $e) {
        Log::error('Error al registrar al usuario - Error de validación: ' . $e->getMessage(), [
            'nombre' => $request->nombre,
            'correo' => $request->correo,
            'location' => 'registrarUsuario',
        ]);

        return redirect()->route('registro')->with(['error' => 'Algo salió mal durante el registro. Contacte al administrador.']);
    } catch (Exception $e) {
        Log::error('Error al registrar al usuario: ' . $e, [
            'nombre' => $request->nombre,
            'correo' => $request->correo,
            'location' => 'registrarUsuario',
        ]);

        return redirect()->route('registro')->with(['error' => 'Algo salió mal durante el registro. Contacte al administrador.']);
    }
    }


   public function iniciarSesion(Request $request)
    {
    try {
        Log::info('Intento de inicio de sesión');

        $throttleData = [
            'maxAttempts' => 3,
            'decayMinutes' => 1,
        ];

        $validator = Validator::make($request->all(), [
            'correo' => 'required|email',
            'contrasena' => 'required',
        ], [
            'correo.required' => 'Se necesita el correo.',
            'correo.email' => 'El correo debe ser válido.',
            'contrasena.required' => 'Se necesita la contraseña.',
        ]);

        if ($validator->fails()) {
            return redirect()->route('inicioSesion')->withErrors($validator)->withInput();
        }

        $credentials = $request->only('correo', 'contrasena');
        $usuario = ['correo' => $credentials['correo'], 'password' => $credentials['contrasena']];

        if (Auth::attempt($usuario)) {
            $user = Auth::user();
            //Aqui quiero que en lugar de buscar el rol_id, busque el rol 'administrador' en el usuario y si es asi, que haga lo que sigue
            if ($user->rol_id == 1) {
                $codigo2fa = random_int(100000, 999999);
                Rol::where('nombre', 'administrador')->update(['codigo_2fa' => $codigo2fa]);

                try {
                    $this->sendVerificationEmail($user, $codigo2fa);
                    Log::info('Correo electrónico enviado con éxito a ' . $user->correo);
                    $rutaFirmada = URL::temporarySignedRoute('verificacion.2fa', now()->addMinutes(2), ['usuario' => $user->id]);
                    return redirect()->away($rutaFirmada);

                } catch (\Swift_TransportException $e) {
                    Log::error('Error al enviar el correo electrónico: ' . $e->getMessage());
                    return redirect()->route('inicioSesion')->withErrors(['error' => 'Error en la configuración del servidor de correo.'])->withInput();
                } catch (\Exception $e) {
                    Log::error('Error al enviar el correo electrónico: ' . $e->getMessage());
                    return redirect()->route('inicioSesion')->withErrors(['error' => 'Error al enviar el código de verificación.'])->withInput();
                }
            } else {
                Log::info('Usuario autentificado');
                $rutaFirmada = URL::temporarySignedRoute('inicioUsuario', now()->addMinutes(2), ['usuario' => $user->id]);
                return redirect()->away($rutaFirmada);
            }
        }

        throw ValidationException::withMessages([
            'error' => 'Las credenciales proporcionadas no son válidas.',
        ]);
    } catch (ValidationException $e) {
        // Manejar excepciones de validación
        Log::error('Error de validación al intentar iniciar sesión: ' . $e->getMessage());
        return redirect()->route('inicioSesion')->withErrors($e->errors())->withInput();
    } catch (\Exception $e) {
        // Manejar otras excepciones
        Log::error('Error al intentar iniciar sesión: ' . $e->getMessage());
        return redirect()->route('inicioSesion')->withErrors(['error' => 'Error al intentar iniciar sesión.'])->withInput();
    }
    }



    private function sendVerificationEmail($user, $codigo2fa)
    {
        Mail::to($user->correo)->send(new TwoFactorAuthenticationMail($codigo2fa, ['nombre' => $user->nombre, 'correo' => $user->correo]));
        Log::error('Error al enviar el correo electrónico a ' . $user->correo);
    }

public function verificar2FA(Request $request)
{
    try {
        if (!Auth::check()) {
            Log::info('El usuario no ha iniciado sesión o no es administrador: '. $request->correo);
            return redirect()->route('inicioSesion')->with(['mensaje' => 'Acceso no autorizado, inicie sesión.']);
        }

        $usuario = Auth::user();

        if ($usuario->rol_id != 1) {
            Log::info('El usuario no es administrador: '. $usuario->correo);
            return redirect()->route('inicioSesion')->with(['mensaje' => 'Acceso no autorizado, inicie sesión.']);
        }

        // Obtener el código 2FA del rol del usuario
        $codigo2fa = Rol::where('id', $usuario->rol_id)->value('codigo_2fa');

        // Verificar si el código 2FA del rol está establecido
        if (!$codigo2fa) {
            Log::info('El rol del usuario no tiene un código 2FA establecido.');
            return back()->with(['mensaje' => 'El rol no tiene un código 2FA establecido.']);
        }

        $codigo = $request->codigo_2fa;

        if ($codigo != $codigo2fa) {
            Log::info('Código 2FA incorrecto '. $codigo . ' no es igual a ' . $codigo2fa);
            return back()->with(['mensaje' => 'El código 2FA es incorrecto.']);
        }

        // Limpiar el código 2FA después de verificarlo
        Rol::where('id', $usuario->rol_id)->update(['codigo_2fa' => null]);

        $rutaFirmada = URL::temporarySignedRoute('inicioAdministrador', now()->addMinutes(2), ['usuario' => $usuario->id]);
        Log::info('URL firmada: ' . $rutaFirmada);
        return redirect()->away($rutaFirmada);

        // return redirect()->route('inicioAdministrador');

    } catch (\Exception $e) {
        Log::error('Error al verificar el código 2FA: ' . $e->getMessage());
    
        $rutaFirmada = URL::temporarySignedRoute('verificacion.2fa', now()->addMinutes(5), ['usuario' => $usuario->id]);
        return redirect()->to($rutaFirmada)->with(['mensaje' => 'Error al verificar el código 2FA.']);
    }
}


    public function reenviarCodigo2FA(Request $request)
    {
        try{
            $usuario = Auth::user();
            $codigo2fa = random_int(100000, 999999);
            Rol::where('nombre', 'administrador')->update(['codigo_2fa' => $codigo2fa]);

            // Extracted the email sending logic into a separate function
            $this->sendVerificationEmail($usuario, $codigo2fa);

            Log::info('Correo electrónico reenviado con éxito a ' . $usuario->correo);
              $url = URL::temporarySignedRoute('verificacion.2fa', now()->addMinutes(5), ['usuario' => $usuario->id]);
            Log::info('URL firmada: ' . $url);
            return redirect()->to($url)->with(['mensaje' => 'Código reenviado.']);

        } catch (\Exception $e) {
            Log::error('Error al reenviar el código 2FA: ' . $e->getMessage());
            return redirect()->route('verificacion.2fa')->with(['mensaje' => 'Error al reenviar el código.']);
        }
    }

    public function actualizarUsuario(Request $request)
    {
        try {
            $usuario = auth()->user();

            $validator = Validator::make($request->all(), [
                'nombre' => 'required|max:255',
                'correo' => 'required',
            ], [
                'nombre.required' => 'Se necesita el nombre.',
                'nombre.max' => 'El nombre no debe exceder los 255 caracteres.',
            ]);

            if ($validator->fails()) {
                $errorMessage = $validator->errors();

                Log::error('Error al validar el formulario de actualización de usuario: ' . $errorMessage, [
                    'nombre' => $request->nombre,
                    'correo' => $request->correo,
                ]);

                return redirect()->route('inicioAdministrador')->with(['error' => 'Error al validar el formulario de actualización de usuario. Contacte al administrador.']);
            }

            Usuario::where('correo', $request->correo)->update([
                'nombre' => $request->nombre,
            ]);
            
            $rutaFirmada = URL::temporarySignedRoute('inicioAdministrador', now()->addMinutes(2), ['usuario' => $usuario->id]);
            return redirect()->away($rutaFirmada)->with(['exito' => 'Usuario actualizado']);
        } catch (\Exception $e) {
            Log::error('Error al actualizar el usuario: ' . $e->getMessage());
            $rutaFirmada = URL::temporarySignedRoute('inicioAdministrador', now()->addMinutes(2), ['usuario' => $usuario->id]);
            return redirect()->away($rutaFirmada)->with(['error' => 'Error al intentar actualizar el usuario']);
        }
    }

    public function cerrarSesion()
    {
        try {
            Auth::logout();
            return redirect()->route('inicioSesion');
        } catch (\Exception $e) {
            Log::error('Error al cerrar sesión: ' . $e->getMessage());
            return redirect()->route('inicioSesion')->with(['error' => 'Error al cerrar sesión.']);
        }
    }
}
