<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

/*

| CONTROLADOR: FORGOT PASSWORD (RECUPERACIÓN DE CONTRASEÑA)
| ESTE CONTROLADOR SE ENCARGA DE TODO LO RELACIONADO CON RECUPERAR
| LA CONTRASEÑA CUANDO EL USUARIO LA OLVIDA.
|
| TIENE DOS FUNCIONES PRINCIPALES:
|   1. MOSTRAR EL FORMULARIO DONDE EL USUARIO ESCRIBE SU EMAIL
|   2. ENVIAR EL CORREO CON EL ENLACE PARA RECUPERAR LA CONTRASEÑA
*/

class ForgotPasswordController extends Controller
{
    /*

    | FUNCIÓN: showLinkRequestForm

    | ¿QUÉ HACE?
    |   MUESTRA LA PÁGINA CON EL FORMULARIO DONDE EL USUARIO
    |   PUEDE ESCRIBIR SU CORREO ELECTRÓNICO PARA RECUPERAR SU CONTRASEÑA.
    |
    | ¿CUÁNDO SE USA?
    |   CUANDO EL USUARIO HACE CLIC EN "¿OLVIDASTE TU CONTRASEÑA?"
    |
    | ¿QUÉ DEVUELVE?
    |   LA VISTA (PANTALLA) UBICADA EN: pages/auth/forgot-password
    */
    public function showLinkRequestForm()
    {
        return view('pages.auth.forgot-password');
    }

    /*
    |--------------------------------------------------------------------------
    | FUNCIÓN: sendResetLinkEmail
    |--------------------------------------------------------------------------
    | ¿QUÉ HACE?
    |   RECIBE EL EMAIL DEL USUARIO, LO VALIDA Y LE ENVÍA UN CORREO
    |   CON UN ENLACE ESPECIAL PARA QUE PUEDA CREAR UNA NUEVA CONTRASEÑA.
    |
    | ¿QUÉ RECIBE?
    |   - $request: CONTIENE EL EMAIL QUE ESCRIBIÓ EL USUARIO EN EL FORMULARIO
    |
    | PASO A PASO:
    |   1. VERIFICA QUE EL CAMPO EMAIL NO ESTÉ VACÍO Y SEA UN CORREO VÁLIDO
    |   2. LE PIDE A LARAVEL QUE ENVÍE EL ENLACE DE RECUPERACIÓN AL EMAIL
    |   3. SI EL CORREO SE ENVIÓ BIEN  → REGRESA CON UN MENSAJE DE ÉXITO
    |   4. SI ALGO SALIÓ MAL           → REGRESA CON UN MENSAJE DE ERROR
    |
    | ¿QUÉ DEVUELVE?
    |   - ÉXITO : REGRESA A LA MISMA PÁGINA CON UN MENSAJE COMO
    |             "TE ENVIAMOS EL ENLACE, REVISA TU CORREO"
    |   - ERROR : REGRESA A LA MISMA PÁGINA CON EL ERROR EN EL CAMPO EMAIL,
    |             POR EJEMPLO SI EL EMAIL NO EXISTE EN LA BASE DE DATOS
    */
    public function sendResetLinkEmail(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        return $status === Password::RESET_LINK_SENT
            ? back()->with(['status' => __($status)])
            : back()->withErrors(['email' => __($status)]);
    }
}