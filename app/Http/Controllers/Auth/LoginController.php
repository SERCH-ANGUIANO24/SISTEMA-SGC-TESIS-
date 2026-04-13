<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| CONTROLADOR: LOGIN (INICIO DE SESIÓN)
|--------------------------------------------------------------------------
| ESTE CONTROLADOR SE ENCARGA DE TODO LO RELACIONADO CON EL
| INICIO Y CIERRE DE SESIÓN DE LOS USUARIOS EN LA APLICACIÓN.
|
| TIENE TRES FUNCIONES PRINCIPALES:
|   1. MOSTRAR EL FORMULARIO DE LOGIN (PANTALLA DE INICIO DE SESIÓN)
|   2. PROCESAR EL LOGIN (VERIFICAR USUARIO Y CONTRASEÑA)
|   3. CERRAR LA SESIÓN DEL USUARIO (LOGOUT)
*/

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | FUNCIÓN: showLoginForm
    |--------------------------------------------------------------------------
    | ¿QUÉ HACE?
    |   MUESTRA LA PÁGINA CON EL FORMULARIO DONDE EL USUARIO
    |   ESCRIBE SU CORREO Y CONTRASEÑA PARA ENTRAR AL SISTEMA.
    |
    | ¿CUÁNDO SE USA?
    |   CUANDO EL USUARIO VISITA LA PÁGINA DE INICIO DE SESIÓN.
    |
    | ¿QUÉ DEVUELVE?
    |   LA VISTA (PANTALLA) UBICADA EN: pages/auth/login
    */
    public function showLoginForm()
    {
        return view('pages.auth.login');
    }

    /*
    |--------------------------------------------------------------------------
    | FUNCIÓN: login
    |--------------------------------------------------------------------------
    | ¿QUÉ HACE?
    |   RECIBE EL EMAIL Y CONTRASEÑA DEL USUARIO, LOS VERIFICA
    |   Y DECIDE SI PUEDE ENTRAR AL SISTEMA O NO.
    |
    | ¿QUÉ RECIBE?
    |   - $request: CONTIENE EL EMAIL, CONTRASEÑA Y SI MARCÓ "RECUÉRDAME"
    |
    | PASO A PASO:
    |   1. VALIDA QUE EL EMAIL Y CONTRASEÑA NO ESTÉN VACÍOS Y TENGAN FORMATO CORRECTO
    |   2. INTENTA AUTENTICAR AL USUARIO CON SUS CREDENCIALES
    |   3. SI LAS CREDENCIALES SON CORRECTAS, VERIFICA QUE LA CUENTA ESTÉ ACTIVA:
    |        - SI LA CUENTA ESTÁ DESACTIVADA → CIERRA SESIÓN Y MANDA ERROR
    |        - SI LA CUENTA ESTÁ ACTIVA      → REGENERA LA SESIÓN Y ENTRA AL DASHBOARD
    |   4. SI LAS CREDENCIALES SON INCORRECTAS → REGRESA CON MENSAJE DE ERROR
    |
    | ¿QUÉ DEVUELVE?
    |   - ÉXITO            : REDIRIGE AL DASHBOARD O A LA PÁGINA QUE INTENTABA VER
    |   - CUENTA INACTIVA  : REGRESA AL LOGIN CON ERROR "TU CUENTA ESTÁ DESACTIVADA"
    |   - CREDENCIALES MAL : REGRESA AL LOGIN CON ERROR "LAS CREDENCIALES NO COINCIDEN"
    */
    public function login(Request $request)
    {
        // Validar credenciales
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // Intentar autenticar
        if (Auth::attempt($credentials, $request->filled('remember'))) {

            // Verificar si la cuenta está activa
            if (!Auth::user()->is_active) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return back()->withErrors([
                    'email' => 'Tu cuenta está desactivada. Contacta al administrador.',
                ])->onlyInput('email');
            }

            $request->session()->regenerate();

            // Redirigir a dashboard o página previa
            return redirect()->intended('dashboard');
        }

        // Si falla la autenticación
        return back()->withErrors([
            'email' => 'Las credenciales proporcionadas no coinciden con nuestros registros.',
        ])->onlyInput('email');
    }

    /*
    |--------------------------------------------------------------------------
    | FUNCIÓN: logout
    |--------------------------------------------------------------------------
    | ¿QUÉ HACE?
    |   CIERRA LA SESIÓN DEL USUARIO DE FORMA SEGURA,
    |   LIMPIA TODOS SUS DATOS DE SESIÓN Y LO MANDA AL INICIO.
    |
    | ¿QUÉ RECIBE?
    |   - $request: LA PETICIÓN ACTUAL DEL USUARIO
    |
    | PASO A PASO:
    |   1. CIERRA LA SESIÓN DEL USUARIO (Auth::logout)
    |   2. INVALIDA (BORRA) TODOS LOS DATOS DE LA SESIÓN ACTUAL
    |   3. REGENERA EL TOKEN DE SEGURIDAD PARA EVITAR ATAQUES
    |   4. REDIRIGE AL USUARIO A LA PÁGINA DE INICIO "/"
    |
    | ¿QUÉ DEVUELVE?
    |   REDIRIGE A LA PÁGINA PRINCIPAL "/" CON LA SESIÓN COMPLETAMENTE CERRADA
    */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}