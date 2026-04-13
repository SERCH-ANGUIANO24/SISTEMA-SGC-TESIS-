<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\ProcesoCustom;
use App\Helpers\HistorialVersionesHelper;

// CONTROLADOR QUE GESTIONA TODAS LAS OPERACIONES SOBRE LOS USUARIOS DEL SISTEMA
// PERMITE VER, CREAR, EDITAR, ACTIVAR/DESACTIVAR Y ELIMINAR USUARIOS
class UsuariosController extends Controller
{
    public function index()
    {
        // REGISTRA EN EL HISTORIAL QUE ALGUIEN VISUALIZÓ EL LISTADO DE USUARIOS
        HistorialVersionesHelper::ver('USUARIOS', null, 'listado');

        // OBTIENE TODOS LOS USUARIOS EXCEPTO EL USUARIO ACTUALMENTE LOGUEADO
        // SOLO MUESTRA USUARIOS CON ROL: admin, user O auditor_lider
        // LOS ADMINS APARECEN PRIMERO Y EL RESTO SE ORDENA ALFABÉTICAMENTE POR NOMBRE
        $usuarios = User::where('id', '!=', auth()->id())
                        ->whereIn('role', ['admin', 'user', 'auditor_lider'])
                        ->orderByRaw("role = 'admin' DESC")
                        ->orderBy('name')
                        ->get();

        // OBTIENE TODOS LOS PROCESOS PERSONALIZADOS ORDENADOS ALFABÉTICAMENTE
        // SE USAN PARA MOSTRAR LAS OPCIONES EN EL FORMULARIO DE CREACIÓN DE USUARIO
        $procesosCustom = ProcesoCustom::orderBy('proceso')->get();

        // RETORNA LA VISTA CON LA LISTA DE USUARIOS Y LOS PROCESOS DISPONIBLES
        return view('usuarios.index', compact('usuarios', 'procesosCustom'));
    }

    /**
     * REGISTRAR NUEVO USUARIO - CON HISTORIAL
     */
    public function store(Request $request)
    {
        // VALIDA TODOS LOS CAMPOS DEL FORMULARIO DE CREACIÓN DE USUARIO
        // EL EMAIL DEBE SER ÚNICO EN LA TABLA USERS
        // LA CONTRASEÑA DEBE TENER AL MENOS 8 CARACTERES Y SER CONFIRMADA
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'proceso' => 'nullable|string|max:255',
            'departamento' => 'nullable|string|max:255',
            'nuevo_proceso' => 'nullable|string|max:255',
            'nuevo_departamento' => 'nullable|string|max:255',
        ]);

        // TOMA EL PROCESO Y DEPARTAMENTO SELECCIONADOS EN EL FORMULARIO
        $proceso = $request->proceso;
        $departamento = $request->departamento;
        
        // SI EL USUARIO ELIGIÓ LA OPCIÓN "__otro__", SIGNIFICA QUE QUIERE CREAR UN PROCESO NUEVO
        if ($request->proceso === '__otro__') {
            // REEMPLAZA LOS VALORES CON LOS CAMPOS DE "NUEVO PROCESO" Y "NUEVO DEPARTAMENTO"
            $proceso = $request->nuevo_proceso;
            $departamento = $request->nuevo_departamento;
            
            // SI SE PROPORCIONARON AMBOS VALORES, VERIFICA SI YA EXISTEN EN LA BASE DE DATOS
            if ($proceso && $departamento) {
                $exists = ProcesoCustom::where('proceso', $proceso)
                    ->where('departamento', $departamento)
                    ->exists();
                    
                // SI NO EXISTE LA COMBINACIÓN, LA CREA Y REGISTRA EN EL HISTORIAL
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

        // CREA EL NUEVO USUARIO EN LA BASE DE DATOS CON LOS DATOS VALIDADOS
        // LA CONTRASEÑA SE ENCRIPTA CON HASH ANTES DE GUARDARSE
        // EL ROL POR DEFECTO ES 'user' Y LA CUENTA SE CREA ACTIVA
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

        // REDIRIGE AL LISTADO DE USUARIOS CON UN MENSAJE DE ÉXITO
        return redirect()->route('admin.usuarios.index')
            ->with('success', 'Usuario registrado correctamente.');
    }

    /**
     * CAMBIAR ESTADO (ACTIVAR/DESACTIVAR) - CON HISTORIAL
     */
    public function estado(User $usuario)
    {
        // PROTEGE LA CUENTA DEL SUPERADMIN: NO SE PUEDE CAMBIAR SU ESTADO
        if ($usuario->isSuperAdmin()) {
            return back()->with('error', 'No se puede modificar la cuenta de superadministrador.');
        }

        // GUARDA LOS DATOS ACTUALES DEL USUARIO ANTES DE HACER EL CAMBIO (PARA EL HISTORIAL)
        $datosAnteriores = $usuario->toArray();

        // INVIERTE EL ESTADO ACTUAL: SI ESTABA ACTIVO LO DESACTIVA, Y VICEVERSA
        $usuario->update(['is_active' => !$usuario->is_active]);

        // REGISTRAR EN HISTORIAL - CAMBIO DE ESTADO
        HistorialVersionesHelper::editar('USUARIOS', $usuario, $datosAnteriores, $usuario->toArray());

        // DETERMINA EL TEXTO DEL MENSAJE SEGÚN EL NUEVO ESTADO DEL USUARIO
        $accion = $usuario->is_active ? 'activada' : 'desactivada';
        return back()->with('estado_success', "La cuenta de {$usuario->name} fue {$accion} correctamente.");
    }

    /**
     * ELIMINAR USUARIO - CON HISTORIAL
     */
    public function destroy(User $usuario)
    {
        // PROTEGE LA CUENTA DEL SUPERADMIN: NO SE PUEDE ELIMINAR
        if ($usuario->isSuperAdmin()) {
            return back()->with('error', 'No se puede eliminar la cuenta de superadministrador.');
        }

        // EVITA QUE UN USUARIO PUEDA ELIMINARSE A SÍ MISMO
        if ($usuario->id === auth()->id()) {
            return back()->with('error', 'No puedes eliminar tu propia cuenta.');
        }

        // GUARDA LOS DATOS DEL USUARIO ANTES DE ELIMINARLO (PARA EL HISTORIAL)
        $datosUsuario = $usuario->toArray();
        $nombre = $usuario->name;

        // ELIMINA EL USUARIO DE LA BASE DE DATOS
        $usuario->delete();

        // REGISTRAR EN HISTORIAL - ELIMINACIÓN DE USUARIO
        HistorialVersionesHelper::eliminar('USUARIOS', (object)$datosUsuario);

        // REDIRIGE DE VUELTA CON UN MENSAJE DE ÉXITO INDICANDO EL NOMBRE DEL USUARIO ELIMINADO
        return back()->with('success', "La cuenta de {$nombre} fue eliminada correctamente.");
    }

    /**
     * EDITAR USUARIO - CON HISTORIAL
     */
    public function updateAdmin(Request $request, User $usuario)
    {
        // PROTEGE LA CUENTA DEL SUPERADMIN: NO SE PUEDE EDITAR
        if ($usuario->isSuperAdmin()) {
            abort(403, 'No se puede editar la cuenta de superadministrador.');
        }

        // DEFINE LAS REGLAS DE VALIDACIÓN BASE: NOMBRE Y EMAIL OBLIGATORIOS
        // EL EMAIL DEBE SER ÚNICO PERO IGNORA EL PROPIO EMAIL DEL USUARIO QUE SE ESTÁ EDITANDO
        $rules = [
            'name'  => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email,' . $usuario->id],
        ];

        // SI SE ENVIÓ UNA NUEVA CONTRASEÑA, AGREGA LAS REGLAS DE VALIDACIÓN PARA ELLA
        // DEBE TENER MÍNIMO 8 CARACTERES Y SER CONFIRMADA CON EL CAMPO DE CONFIRMACIÓN
        if ($request->filled('password')) {
            $rules['password']              = ['required', 'min:8', 'confirmed'];
            $rules['password_confirmation'] = ['required'];
        }

        $validated = $request->validate($rules);

        // GUARDA LOS DATOS ACTUALES DEL USUARIO ANTES DE EDITARLOS (PARA EL HISTORIAL)
        $datosAnteriores = $usuario->toArray();

        // PREPARA LOS DATOS QUE SE ACTUALIZARÁN: NOMBRE Y EMAIL SIEMPRE SE ACTUALIZAN
        $data = [
            'name'  => $validated['name'],
            'email' => $validated['email'],
        ];

        // SI SE PROPORCIONÓ UNA NUEVA CONTRASEÑA, LA ENCRIPTA Y LA AGREGA A LOS DATOS A GUARDAR
        if ($request->filled('password')) {
            $data['password'] = Hash::make($validated['password']);
        }

        // ACTUALIZA EL USUARIO EN LA BASE DE DATOS CON LOS NUEVOS DATOS
        $usuario->update($data);

        // REGISTRAR EN HISTORIAL - EDICIÓN DE USUARIO
        HistorialVersionesHelper::editar('USUARIOS', $usuario, $datosAnteriores, $usuario->toArray());

        // REDIRIGE DE VUELTA CON UN MENSAJE DE ÉXITO INDICANDO EL NOMBRE DEL USUARIO ACTUALIZADO
        return back()->with('success', "La cuenta de {$usuario->name} fue actualizada correctamente.");
    }
}