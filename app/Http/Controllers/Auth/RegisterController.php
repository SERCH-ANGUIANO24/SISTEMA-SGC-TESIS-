<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\Registered;
use App\Models\ProcesoCustom;

/*
|--------------------------------------------------------------------------
| CONTROLADOR: REGISTER (REGISTRO DE NUEVOS USUARIOS)
|--------------------------------------------------------------------------
| ESTE CONTROLADOR SE ENCARGA DE TODO LO RELACIONADO CON EL
| REGISTRO DE NUEVOS USUARIOS EN LA APLICACIÓN.
|
| TIENE DOS FUNCIONES PRINCIPALES:
|   1. MOSTRAR EL FORMULARIO DE REGISTRO
|   2. PROCESAR EL REGISTRO (VALIDAR DATOS Y CREAR EL USUARIO)
*/

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | FUNCIÓN: showRegistrationForm
    |--------------------------------------------------------------------------
    | ¿QUÉ HACE?
    |   MUESTRA LA PÁGINA CON EL FORMULARIO DONDE SE INGRESAN
    |   LOS DATOS PARA CREAR UNA NUEVA CUENTA DE USUARIO.
    |
    | ¿CUÁNDO SE USA?
    |   CUANDO SE VISITA LA PÁGINA DE REGISTRO DE NUEVOS USUARIOS.
    |
    | ¿QUÉ DEVUELVE?
    |   LA VISTA (PANTALLA) UBICADA EN: pages/auth/register
    */
    public function showRegistrationForm()
    {
        return view('pages.auth.register');
    }

    /*
    |--------------------------------------------------------------------------
    | FUNCIÓN: register
    |--------------------------------------------------------------------------
    | ¿QUÉ HACE?
    |   RECIBE TODOS LOS DATOS DEL FORMULARIO DE REGISTRO, LOS VALIDA
    |   Y CREA UN NUEVO USUARIO EN LA BASE DE DATOS.
    |
    | ¿QUÉ RECIBE?
    |   - $request: CONTIENE TODOS LOS CAMPOS DEL FORMULARIO:
    |       · name            → NOMBRE DEL USUARIO
    |       · email           → CORREO ELECTRÓNICO
    |       · password        → CONTRASEÑA
    |       · proceso         → PROCESO SELECCIONADO DEL LISTADO
    |       · departamento    → DEPARTAMENTO SELECCIONADO DEL LISTADO
    |       · nuevo_proceso      → TEXTO LIBRE SI ELIGIÓ "OTRO" PROCESO
    |       · nuevo_departamento → TEXTO LIBRE SI ELIGIÓ "OTRO" DEPARTAMENTO
    |
    | PASO A PASO:
    |   1. DETECTA SI EL USUARIO ELIGIÓ LA OPCIÓN "OTRO" PARA EL PROCESO
    |
    |   2. ARMA LAS REGLAS DE VALIDACIÓN BASE (NOMBRE, EMAIL, CONTRASEÑA)
    |        - SI ELIGIÓ "OTRO"  → VALIDA LOS CAMPOS DE TEXTO LIBRE
    |                              (nuevo_proceso y nuevo_departamento)
    |        - SI NO ELIGIÓ OTRO → VALIDA LOS SELECTS NORMALES
    |                              (proceso y departamento)
    |
    |   3. RESUELVE CUÁL ES EL PROCESO Y DEPARTAMENTO FINAL:
    |        - SI ELIGIÓ "OTRO"  → USA LO QUE ESCRIBIÓ EN LOS CAMPOS DE TEXTO
    |        - SI NO ELIGIÓ OTRO → USA LO QUE SELECCIONÓ EN EL DROPDOWN
    |
    |   4. SI ES UN PROCESO NUEVO, LO GUARDA EN LA TABLA "procesos_custom"
    |      PARA QUE ESTÉ DISPONIBLE EN EL FUTURO (SOLO SI NO EXISTÍA ANTES)
    |
    |   5. CREA EL NUEVO USUARIO EN LA BASE DE DATOS CON:
    |        · NOMBRE, EMAIL, PROCESO, DEPARTAMENTO Y CONTRASEÑA ENCRIPTADA
    |
    |   6. DISPARA EL EVENTO DE REGISTRO (PUEDE ENVIAR EMAIL DE VERIFICACIÓN)
    |
    |   7. GUARDA EN SESIÓN LOS DATOS DEL REGISTRO PARA MOSTRAR
    |      UN MENSAJE DE ÉXITO CON LA INFORMACIÓN DEL USUARIO CREADO
    |
    |   8. REDIRIGE A LA LISTA DE USUARIOS CON MENSAJE DE CONFIRMACIÓN
    |
    | ¿QUÉ DEVUELVE?
    |   REDIRIGE A: admin.usuarios.index
    |   CON MENSAJE: "USUARIO {NOMBRE} REGISTRADO CORRECTAMENTE"
    */
    public function register(Request $request)
    {
        $esNuevoProceso = $request->proceso === '__otro__';

        $rules = [
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];

        // SI ELIGE OTRO SE ACTIVAN OTROS CAMPOS LIBRES DE TECTO
        // SI NO SE VALIDA CON ALGUNA DE LASOPCIONES DE LA LISTA DEL FORMULARIO
        if ($esNuevoProceso) {
            $rules['nuevo_proceso']      = ['required', 'string', 'max:255'];
            $rules['nuevo_departamento'] = ['required', 'string', 'max:255'];
        } else {
            $rules['proceso']      = ['required', 'string', 'max:255'];
            $rules['departamento'] = ['required', 'string', 'max:255'];
        }

        $request->validate($rules);

        $proceso      = $esNuevoProceso ? trim($request->nuevo_proceso)      : $request->proceso;
        $departamento = $esNuevoProceso ? trim($request->nuevo_departamento) : $request->departamento;

        // SI ES UN PROCESO NUEVO SE GUARDA EN LA TABLA DE PROCESO_CUSTOM
        if ($esNuevoProceso) {
            ProcesoCustom::firstOrCreate(
                ['proceso' => $proceso, 'departamento' => $departamento]
            );
        }

        // CREAR USUARIO
        $user = User::create([
            'name'         => $request->name,
            'email'        => $request->email,
            'proceso'      => $proceso,
            'departamento' => $departamento,
            'password'     => Hash::make($request->password),
        ]);

        // SE DISPARA EL EVENTO DE REGISTRO
        event(new Registered($user));

        // GUARDA DATOS D ESEION PARA CONFIRMAR QUE SE HA CREADO LA CUNET EN LA PLATAFORMA
        session()->flash('registration_success', true);
        session()->flash('registered_name',         $user->name);
        session()->flash('registered_email',        $user->email);
        session()->flash('registered_proceso',      $user->proceso);
        session()->flash('registered_departamento', $user->departamento);
        session()->flash('status', '¡Registro exitoso! Tu cuenta ha sido creada.');

        return redirect()->route('admin.usuarios.index')
            ->with('success', "Usuario {$user->name} registrado correctamente.");
    }
}