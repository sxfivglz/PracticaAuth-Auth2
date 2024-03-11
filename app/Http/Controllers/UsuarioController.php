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
    use Illuminate\Support\Facades\Route;
    use Illuminate\Cache\RateLimiter;
    use Illuminate\Support\Facades\Cookie;

    class UsuarioController extends Controller

    {
      
    /*---------------------------------------------------------------------*/
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
                'contrasena' => bcrypt($request->contrasena),
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
        } catch(QueryException $e) {
            Log::error('Error al registrar al usuario - Error de consulta: ' . $e->getMessage(), [
                'nombre' => $request->nombre,
                'correo' => $request->correo,
                'location' => 'registrarUsuario',
            ]);

            return redirect()->route('registro')->with(['error' => 'Algo salió mal durante el registro. Contacte al administrador.']);
        }
        }

            /*---------------------------------------------------------------------*/

            public function iniciarSesion(Request $request)
             {
                Log::info('Intento de inicio de sesión');
            try {

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
            //Verificar si el usuario existe 
            $usuario_existe = Usuario::where('correo', $request->correo)->first();
            if (!$usuario_existe) {
                Log::error('Error al iniciar sesión: El usuario no existe.');
                return redirect()->route('inicioSesion')->withErrors(['error' => 'Error al iniciar sesión.'])->withInput();
            }else if(!Hash::check($request->contrasena, $usuario_existe->contrasena)){
                Log::error('Error al iniciar sesión: La contraseña no es válida.');
                return redirect()->route('inicioSesion')->withErrors(['error' => 'Error al iniciar sesión.'])->withInput();
            }
             $credentials = $request->only('correo', 'contrasena');
            $usuario = ['correo' => $credentials['correo'], 'password' => ($credentials['contrasena'])];

        
            $query = Usuario::join('roles', 'usuarios.rol_id', '=', 'roles.id')
            ->select('usuarios.id', 'usuarios.nombre', 'usuarios.correo', 'usuarios.contrasena', 'usuarios.rol_id', 'roles.nombre as rol')
            ->where('usuarios.correo', $usuario['correo'])
            ->first(); 
            
                if ($query->rol == 'administrador') {
                    $codigo2fa = Crypt::encrypt(random_int(100000, 999999));
                    Rol::where('nombre', 'administrador')->update(['codigo_2fa' => $codigo2fa]);
                    try {
                        //Convertir $query a objeto
                        
                        $this->sendVerificationEmail($query, Crypt::decrypt($codigo2fa));
                        $rutaFirmada = URL::temporarySignedRoute('verificacion.2fa', now()->addMinutes(2), ['usuario' => $query->id]);
                        return redirect()->away($rutaFirmada);

                    } catch (\Swift_TransportException $e) {
                        Log::error('Error al enviar el correo electrónico: ' . $e->getMessage());
                        return redirect()->route('inicioSesion')->withErrors(['error' => 'Error al iniciar sesión. Contacte al administrador'])->withInput();
                    } catch (\Exception $e) {
                        Log::error('Error al enviar el correo electrónico: ' . $e->getMessage());
                        return redirect()->route('inicioSesion')->withErrors(['error' => 'Error al iniciar sesión. Contacta al administrador'])->withInput();
                    }
                } else {
                
                    if (Auth::attempt($usuario)) {
                    $id = Auth::id();
                    Log::info('Usuario comun');

                    $rutaFirmada = URL::temporarySignedRoute('inicioUsuario', now()->addMinutes(2), ['usuario' => $id]);
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
        }catch (QueryException $e) {
            Log::error('Error al intentar iniciar sesión - Error de consulta: ' . $e->getMessage());
            return redirect()->route('inicioSesion')->withErrors(['error' => 'Error al intentar iniciar sesión.'])->withInput();
        }catch (PDOException $e) {
            Log::error('Error al intentar iniciar sesión - Error de base de datos: ' . $e->getMessage());
            return redirect()->route('inicioSesion')->withErrors(['error' => 'Error al intentar iniciar sesión.'])->withInput();
        }

        }

            /*---------------------------------------------------------------------*/

        private function sendVerificationEmail($user, $codigo2fa)
        {
            Mail::to($user->correo)->send(new TwoFactorAuthenticationMail($codigo2fa, ['nombre' => $user->nombre, 'correo' => $user->correo]));
            Log::info('Correo electrónico enviado con éxito a ' . $user->correo);
          
        
        }

            /*---------------------------------------------------------------------*/

            public function verificar2FA(Request $request)
        {
        try {
            $codigoRecibido = $request->codigo_2fa;
            $usuario = Usuario::join('roles', 'usuarios.rol_id', '=', 'roles.id')
                ->where('usuarios.id', $request->id)
                ->select('usuarios.id', 'usuarios.correo', 'roles.codigo_2fa')
                ->first();

            $codigoEnDB = Crypt::decrypt($usuario->codigo_2fa);
            if ($codigoRecibido == $codigoEnDB) {
                $usuario = Usuario::find($usuario->id);
                Auth::login($usuario);
                Log::info('El usuario administrador: '.$usuario->correo .' ha iniciado sesión correctamente.');
                $rutaFirmada = URL::temporarySignedRoute('inicioAdministrador', now()->addMinutes(2), ['usuario' => $usuario->id]);
                return redirect()->away($rutaFirmada);
            } else {
                $rutaFirmada = URL::temporarySignedRoute('verificacion.2fa', now()->addMinutes(2), ['usuario' => $usuario->id]);
                return redirect()->away($rutaFirmada)->with(['error' => 'Código 2FA incorrecto.']);
            }

        } catch (\Exception $e) {
            // Maneja cualquier excepción que pueda ocurrir durante la desencriptación o comparación
            Log::error('Error al verificar el código 2FA: ' . $e->getMessage());
            $rutaFirmada = URL::temporarySignedRoute('verificacion.2fa', now()->addMinutes(2), ['usuario' => $usuario->id]);
            return redirect()->away($rutaFirmada)->with(['error' => 'Error al verificar el código 2FA.']);
        }
    }
        
            /*---------------------------------------------------------------------*/

            public function reenviarCodigo2FA(Request $request)
        {
            try{
                
            $usuario = Usuario::join('roles', 'usuarios.rol_id', '=', 'roles.id')
                ->where('usuarios.id', $request->id)
                ->select('usuarios.id', 'usuarios.correo', 'roles.codigo_2fa')
                ->first();
            $codigo2fa = Crypt::encrypt(random_int(100000, 999999));
            
            Rol::where('nombre', 'administrador')->update(['codigo_2fa' => $codigo2fa]);

            $this->sendVerificationEmail($usuario, Crypt::decrypt($codigo2fa));

            Log::info('Correo electrónico reenviado con éxito a ' . $usuario->correo);
              $url = URL::temporarySignedRoute('verificacion.2fa', now()->addMinutes(5), ['usuario' => $usuario->id]);
            Log::info('URL firmada: ' . $url);
            return redirect()->to($url)->with(['exito-codigo' => 'Código reenviado.']);

        } catch (\Exception $e) {
            Log::error('Error al reenviar el código 2FA: ' . $e->getMessage());
            $rutaFirmada = URL::temporarySignedRoute('verificacion.2fa', now()->addMinutes(2), ['usuario' => $usuario->id]);
            return redirect()->away($rutaFirmada)->with(['error' => 'Error al reenviar el código 2FA.']);
        } catch (\Swift_TransportException $e) {
            Log::error('Error al enviar el correo electrónico: ' . $e->getMessage());
                 $rutaFirmada = URL::temporarySignedRoute('verificacion.2fa', now()->addMinutes(2), ['usuario' => $usuario->id]);
            return redirect()->away($rutaFirmada)->with(['error' => 'Error al reenviar el código 2FA.']);
        } catch (\Exception $e) {
            Log::error('Error al enviar el correo electrónico: ' . $e->getMessage());
                   $rutaFirmada = URL::temporarySignedRoute('verificacion.2fa', now()->addMinutes(2), ['usuario' => $usuario->id]);
            return redirect()->away($rutaFirmada)->with(['error' => 'Error al reenviar el código 2FA.']);
        }
    
        }
        /*---------------------------------------------------------------------*/

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


            /*---------------------------------------------------------------------*/

        public function cerrarSesion(request $request)
        {
        try {
        Auth::logout();
        Cookie::forget('app_session');
        return redirect()->route('inicioSesion')->with(['exito' => 'Sesión cerrada']);
        } catch (\Exception $e) {
        Log::error('Error al cerrar sesión: ' . $e->getMessage());
        return redirectback()->with(['error' => 'Error al cerrar sesión']);
        }
        }

}
