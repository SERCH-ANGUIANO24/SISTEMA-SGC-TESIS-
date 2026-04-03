<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\ProcesoCustom;
use App\Helpers\HistorialVersionesHelper;

class UsuariosController extends Controller
{
    public function index()
    {
        // Registrar visualización del listado
        HistorialVersionesHelper::ver('USUARIOS', null, 'listado');

        $usuarios = User::where('id', '!=', auth()->id())
                        ->whereIn('role', ['admin', 'user'])
                        ->orderByRaw("role = 'admin' DESC")
                        ->orderBy('name')
                        ->get();

        $procesosCustom = ProcesoCustom::orderBy('proceso')->get();

        return view('usuarios.index', compact('usuarios', 'procesosCustom'));
    }

    /**
     * REGISTRAR NUEVO USUARIO - CON HISTORIAL
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'proceso' => 'nullable|string|max:255',
            'departamento' => 'nullable|string|max:255',
            'nuevo_proceso' => 'nullable|string|max:255',
            'nuevo_departamento' => 'nullable|string|max:255',
        ]);

        // Determinar proceso y departamento finales
        $proceso = $request->proceso;
        $departamento = $request->departamento;
        
        if ($request->proceso === '__otro__') {
            $proceso = $request->nuevo_proceso;
            $departamento = $request->nuevo_departamento;
            
            // Guardar el nuevo proceso y departamento en procesos_custom
            if ($proceso && $departamento) {
                $exists = ProcesoCustom::where('proceso', $proceso)
                    ->where('departamento', $departamento)
                    ->exists();
                    
                if (!$exists) {
                    ProcesoCustom::create([
                        'proceso' => $proceso,
                        'departamento' => $departamento
                    ]);
                    
                    // Registrar en historial - Creación de proceso
                    $procesoData = (object)['nombre' => $proceso];
                    HistorialVersionesHelper::crear('PROCESOS', $procesoData);
                    
                    // Registrar en historial - Creación de departamento
                    $deptoData = (object)[
                        'proceso' => $proceso,
                        'departamento' => $departamento
                    ];
                    HistorialVersionesHelper::crear('DEPARTAMENTOS', $deptoData);
                }
            }
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'user',
            'is_active' => true,
            'proceso' => $proceso,
            'departamento' => $departamento,
        ]);

        // REGISTRAR EN HISTORIAL - CREACIÓN DE USUARIO
        HistorialVersionesHelper::crear('USUARIOS', $user);

        return redirect()->route('admin.usuarios.index')
            ->with('success', 'Usuario registrado correctamente.');
    }

    /**
     * CAMBIAR ESTADO (ACTIVAR/DESACTIVAR) - CON HISTORIAL
     */
    public function estado(User $usuario)
    {
        if ($usuario->isSuperAdmin()) {
            return back()->with('error', 'No se puede modificar la cuenta de superadministrador.');
        }

        $datosAnteriores = $usuario->toArray();
        $usuario->update(['is_active' => !$usuario->is_active]);

        // REGISTRAR EN HISTORIAL - CAMBIO DE ESTADO
        HistorialVersionesHelper::editar('USUARIOS', $usuario, $datosAnteriores, $usuario->toArray());

        $accion = $usuario->is_active ? 'activada' : 'desactivada';
        return back()->with('estado_success', "La cuenta de {$usuario->name} fue {$accion} correctamente.");
    }

    /**
     * ELIMINAR USUARIO - CON HISTORIAL
     */
    public function destroy(User $usuario)
    {
        if ($usuario->isSuperAdmin()) {
            return back()->with('error', 'No se puede eliminar la cuenta de superadministrador.');
        }

        if ($usuario->id === auth()->id()) {
            return back()->with('error', 'No puedes eliminar tu propia cuenta.');
        }

        $datosUsuario = $usuario->toArray();
        $nombre = $usuario->name;
        $usuario->delete();

        // REGISTRAR EN HISTORIAL - ELIMINACIÓN DE USUARIO
        HistorialVersionesHelper::eliminar('USUARIOS', (object)$datosUsuario);

        return back()->with('success', "La cuenta de {$nombre} fue eliminada correctamente.");
    }

    /**
     * EDITAR USUARIO - CON HISTORIAL
     */
    public function updateAdmin(Request $request, User $usuario)
    {
        if ($usuario->isSuperAdmin()) {
            abort(403, 'No se puede editar la cuenta de superadministrador.');
        }

        $rules = [
            'name'  => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email,' . $usuario->id],
        ];

        if ($request->filled('password')) {
            $rules['password']              = ['required', 'min:8', 'confirmed'];
            $rules['password_confirmation'] = ['required'];
        }

        $validated = $request->validate($rules);

        $datosAnteriores = $usuario->toArray();

        $data = [
            'name'  => $validated['name'],
            'email' => $validated['email'],
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($validated['password']);
        }

        $usuario->update($data);

        // REGISTRAR EN HISTORIAL - EDICIÓN DE USUARIO
        HistorialVersionesHelper::editar('USUARIOS', $usuario, $datosAnteriores, $usuario->toArray());

        return back()->with('success', "La cuenta de {$usuario->name} fue actualizada correctamente.");
    }
}