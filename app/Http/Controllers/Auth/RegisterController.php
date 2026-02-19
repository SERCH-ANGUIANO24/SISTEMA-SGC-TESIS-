<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\Registered;

class RegisterController extends Controller
{
    /**
     * Muestra el formulario de registro
     */
    public function showRegistrationForm()
    {
        return view('pages.auth.register'); // O 'pages.auth.register' según tu estructura
    }

    /**
     * Procesa el registro
     */
    public function register(Request $request)
    {
        // Validar datos
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'proceso' => ['required', 'string', 'max:255'],
            'departamento' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        // Crear usuario con los nuevos campos
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'proceso' => $request->proceso,
            'departamento' => $request->departamento,
            'password' => Hash::make($request->password),
        ]);

        // Disparar evento de registro
        event(new Registered($user));

        // Guardar datos en sesión para mostrar en el mensaje de éxito
        session()->flash('registration_success', true);
        session()->flash('registered_name', $user->name);
        session()->flash('registered_email', $user->email);
        session()->flash('registered_proceso', $user->proceso);
        session()->flash('registered_departamento', $user->departamento);
        session()->flash('status', '¡Registro exitoso! Tu cuenta ha sido creada.');

        // Redirigir a la misma página de registro para mostrar el mensaje de éxito
        return redirect()->route('register');
    }
}