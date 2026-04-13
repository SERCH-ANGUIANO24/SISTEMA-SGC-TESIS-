<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ThemeController extends Controller
{
    /**
     * UPDATE
     * ACTUALIZA EL COLOR DE TEMA DEL USUARIO AUTENTICADO.
     * VALIDA QUE EL COLOR SEA UN HEXADECIMAL VÁLIDO (EJEMPLO: #FF5733)
     * Y LO GUARDA EN LA BASE DE DATOS.
     */
    public function update(Request $request)
    {
        // VALIDA QUE theme_color SEA UN STRING CON FORMATO HEXADECIMAL VÁLIDO (#RRGGBB)
        $request->validate([
            'theme_color' => ['required', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
        ]);

        // OBTIENE EL USUARIO ACTUALMENTE AUTENTICADO
        $user = Auth::user();

        // ASIGNA EL NUEVO COLOR DE TEMA AL USUARIO
        $user->theme_color = $request->theme_color;

        // GUARDA LOS CAMBIOS EN LA BASE DE DATOS
        $user->save();

        // RETORNA RESPUESTA EXITOSA CON EL COLOR DE TEMA ACTUALIZADO
        return response()->json([
            'success'     => true,
            'theme_color' => $user->theme_color,
        ]);
    }
}