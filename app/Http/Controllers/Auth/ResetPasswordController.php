<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

/*
|--------------------------------------------------------------------------
| CONTROLADOR: RESET PASSWORD (RESTABLECER CONTRASEÑA)
|--------------------------------------------------------------------------
| SE ENCARGA DE MOSTRAR EL FORMULARIO PARA CREAR UNA NUEVA CONTRASEÑA
| Y DE PROCESAR EL CAMBIO DE CONTRASEÑA DEL USUARIO.
*/

class ResetPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | FUNCIÓN: showResetForm
    |--------------------------------------------------------------------------
    | MUESTRA LA PANTALLA DONDE EL USUARIO ESCRIBE SU NUEVA CONTRASEÑA.
    | RECIBE EL TOKEN (CLAVE ÚNICA) Y EL EMAIL DESDE LA URL.
    | DEVUELVE LA VISTA: pages/auth/reset-password
    */
    public function showResetForm(Request $request, $token = null)
    {
        return view('pages.auth.reset-password')->with(
            ['token' => $token, 'email' => $request->email]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | FUNCIÓN: reset
    |--------------------------------------------------------------------------
    | VALIDA LOS DATOS Y GUARDA LA NUEVA CONTRASEÑA DEL USUARIO.
    |
    | PASO A PASO:
    |   1. VALIDA QUE EL TOKEN, EMAIL Y CONTRASEÑA SEAN CORRECTOS
    |   2. ENCRIPTA Y GUARDA LA NUEVA CONTRASEÑA EN LA BASE DE DATOS
    |   3. GENERA UN NUEVO TOKEN DE "RECUÉRDAME" POR SEGURIDAD
    |   4. SI TODO SALIÓ BIEN  → REDIRIGE AL LOGIN CON MENSAJE DE ÉXITO
    |   5. SI ALGO SALIÓ MAL   → REGRESA CON MENSAJE DE ERROR
    */
    public function reset(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));

                $user->save();
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('status', __($status))
            : back()->withErrors(['email' => [__($status)]]);
    }
}