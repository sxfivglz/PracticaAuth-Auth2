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
use Illuminate\Support\Facades\Throttle;
use App\Models\Rol;
use Illuminate\Http\Exceptions\ThrottleRequestsException;

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
                ]);

                return redirect()->route('registro')->with(['error' => 'Error al validar el formulario de registro. Contacte al administrador.']);
            }

            $rol = Usuario::count() > 0 ? 2 : 1;

            $usuario = Usuario::create([
                'nombre' => $request->nombre,
                'correo' => $request->correo,
                'contrasena' => Hash::make($request->contrasena),
                'rol_id' => $rol,
            ]);


            return redirect()->route('inicioSesion')->with(['exito' => 'Usuario registrado exitosamente.']);
            
        } catch (\Exception $e) {
            Log::error('Error al registrar al usuario: ' . $e->getMessage(), [
                'nombre' => $request->nombre,
                'correo' => $request->correo,
            ]);

            return redirect()->route('registro')->with(['error' => 'Algo salió mal. Contacte al administrador.']);
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

        // Validación de campos
        $validator = Validator::make($request->all(), [
            'correo' => 'required|email',
            'contrasena' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()->route('inicioSesion')->withErrors($validator)->withInput();
        }

        $credentials = $request->only('correo', 'contrasena');
        $usuario = ['correo' => $credentials['correo'], 'password' => $credentials['contrasena']];

        if (Auth::attempt($usuario)) {
            $user = Auth::user();

            if ($user->rol_id == 1) {
                $codigo2fa = random_int(100000, 999999);
                Rol::where('nombre', 'administrador')->update(['codigo_2fa' => $codigo2fa]);

                try {
                    Mail::to($user->correo)->send(new TwoFactorAuthenticationMail($codigo2fa, ['nombre' => $user->nombre, 'correo' => $user->correo]));

                    Log::info('Correo electrónico enviado con éxito a ' . $user->correo);
                } catch (\Swift_TransportException $e) {
                    Log::error('Error al enviar el correo electrónico: ' . $e->getMessage());
                    return redirect()->route('inicioSesion')->withErrors(['error' => 'Error en la configuración del servidor de correo.'])->withInput();
                } catch (\Exception $e) {
                    Log::error('Error al enviar el correo electrónico: ' . $e->getMessage());
                    return redirect()->route('inicioSesion')->withErrors(['error' => 'Error al enviar el código de verificación.'])->withInput();
                }

                Log::info('Usuario administrador con 2FA');
                return redirect()->route('verificacion.2fa')->with(['exito' => 'Verifica tu código']);
            } else {
                // Usuario autenticado (no es administrador)
                Log::info('Usuario autentificado');
                return redirect()->route('inicioUsuario'); // Ajusta la ruta según tus necesidades
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


        public function verificar2FA(Request $request)
        {
                try {
        $usuario = Auth::user();
        $codigo = $request->codigo_2fa;
        

        // Validación del código 2FA
        if (!is_numeric($codigo) || strlen($codigo) !== 6) {
            throw ValidationException::withMessages([
                'mensaje' => 'El código proporcionado no es válido.',
            ]);
        }

        if ($usuario->rol && $usuario->rol->codigo_2fa == $codigo) {
            while (now()->diffInMinutes($usuario->rol->updated_at) > 1) {
                Rol::where('nombre', 'administrador')->update(['codigo_2fa' => null]);
            }
            Log::info('Usuario autenticado con 2FA');
             Log::info($usuario);
             return redirect()->route('inicioAdmin')->with(['exito' => 'Inicio de sesión exitoso.', 'usuario' => $usuario]);
           
        }

        throw ValidationException::withMessages([
            'mensaje' => 'El código proporcionado no es válido.',
        ]);
    } catch (ValidationException $e) {
        // Manejar excepciones de validación
        Log::error('Error de validación al intentar verificar el 2FA: ' . $e->getMessage());
        return redirect()->back()->withErrors($e->errors());
    } catch (\Exception $e) {
        // Manejar otras excepciones
        Log::error('Error al intentar verificar el 2FA: ' . $e->getMessage());
        return redirect()->back()->withErrors(['mensaje' => 'Error al intentar verificar el 2FA.']);
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

            return redirect()->route('inicioAdmin')->with(['error' => 'Error al validar el formulario de actualización de usuario. Contacte al administrador.']);
        }
       
        Usuario::where('correo', $request->correo)->update([
            'nombre' => $request->nombre,
        ]);

        return redirect()->route('inicioAdmin')->with(['exito' => 'Usuario actualizado']);
    } catch (\Exception $e) {
        // Maneja las excepciones según tus necesidades
        return redirect()->back()->withErrors(['mensaje' => 'Error al intentar actualizar el usuario']);
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



