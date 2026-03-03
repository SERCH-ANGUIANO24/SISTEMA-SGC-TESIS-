<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\Registered;
use App\Models\ProcesoCustom;

class RegisterController extends Controller
{
    /**
     * Muestra el formulario de registro
     */
    public function showRegistrationForm()
    {
        return view('pages.auth.register');
    }

    /**
     * Procesa el registro
     */
    public function register(Request $request)
    {
        $esNuevoProceso = $request->proceso === '__otro__';

        // Validación base
        $rules = [
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];

        // Si elige "Otro" valida los campos de texto libre,
        // si no valida los selects normales
        if ($esNuevoProceso) {
            $rules['nuevo_proceso']      = ['required', 'string', 'max:255'];
            $rules['nuevo_departamento'] = ['required', 'string', 'max:255'];
        } else {
            $rules['proceso']      = ['required', 'string', 'max:255'];
            $rules['departamento'] = ['required', 'string', 'max:255'];
        }

        $request->validate($rules);

        // Resolver proceso y departamento finales
        $proceso      = $esNuevoProceso ? trim($request->nuevo_proceso)      : $request->proceso;
        $departamento = $esNuevoProceso ? trim($request->nuevo_departamento) : $request->departamento;

        // Si es proceso nuevo, guardarlo en la tabla procesos_custom
        if ($esNuevoProceso) {
            ProcesoCustom::firstOrCreate(
                ['proceso' => $proceso, 'departamento' => $departamento]
            );
        }

        // Crear usuario — igual que antes
        $user = User::create([
            'name'         => $request->name,
            'email'        => $request->email,
            'proceso'      => $proceso,
            'departamento' => $departamento,
            'password'     => Hash::make($request->password),
        ]);

        // Disparar evento de registro
        event(new Registered($user));

        // Guardar datos en sesión para mostrar en el mensaje de éxito
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