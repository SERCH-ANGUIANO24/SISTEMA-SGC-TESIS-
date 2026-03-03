<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\ProcesoCustom;

class UsuariosController extends Controller
{
    /**
     * Lista todos los usuarios excepto el superadmin autenticado.
     * El superadmin ve a todos (admin + users normales).
     */
    public function index()
    {
        $usuarios = User::where('id', '!=', auth()->id())
                        ->whereIn('role', ['admin', 'user'])
                        ->orderByRaw("role = 'admin' DESC") // admin primero
                        ->orderBy('name')
                        ->get();

        $procesosCustom = ProcesoCustom::orderBy('proceso')->get();

        return view('usuarios.index', compact('usuarios', 'procesosCustom'));
    }

    /**
     * Cambia el estado activo/inactivo de un usuario.
     */
    public function estado(User $usuario)
    {
        if ($usuario->isSuperAdmin()) {
            return back()->with('error', 'No se puede modificar la cuenta de superadministrador.');
        }

        $usuario->update(['is_active' => !$usuario->is_active]);

        $accion = $usuario->is_active ? 'activada' : 'desactivada';
        return back()->with('success', "La cuenta de {$usuario->name} fue {$accion} correctamente.");
    }

    /**
     * Elimina un usuario.
     */
    public function destroy(User $usuario)
    {
        if ($usuario->isSuperAdmin()) {
            return back()->with('error', 'No se puede eliminar la cuenta de superadministrador.');
        }

        if ($usuario->id === auth()->id()) {
            return back()->with('error', 'No puedes eliminar tu propia cuenta.');
        }

        $nombre = $usuario->name;
        $usuario->delete();

        return back()->with('success', "La cuenta de {$nombre} fue eliminada correctamente.");
    }

    /**
     * Actualiza nombre, email y/o contraseña del admin.
     * Solo disponible para cuentas con role 'admin'.
     */
    public function updateAdmin(Request $request, User $usuario)
    {
        // Superadmin puede editar cualquier cuenta excepto a sí mismo por esta vía
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

        $data = [
            'name'  => $validated['name'],
            'email' => $validated['email'],
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($validated['password']);
        }

        $usuario->update($data);

        return back()->with('success', "La cuenta de {$usuario->name} fue actualizada correctamente.");
    }
}