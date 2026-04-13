<?php

namespace App\Http\Controllers\Auditoria;

use App\Http\Controllers\Controller;
use App\Models\Auditoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

// CONTROLADOR QUE GESTIONA LA PÁGINA PRINCIPAL DEL PLAN DE AUDITORÍA
// ES UN CONTROLADOR SIMPLE QUE SOLO MUESTRA LA VISTA CON LOS AÑOS DISPONIBLES
class PlanAuditoriaController extends Controller
{
    public function index()
    {
        // Cambiar a: sin soft deletes
        // OBTIENE TODOS LOS AÑOS DISTINTOS EN QUE HAY AUDITORÍAS REGISTRADAS
        // SE ORDENAN DE MÁS RECIENTE A MÁS ANTIGUO PARA EL FILTRO DE LA VISTA
        $anios = Auditoria::select('anio')
            ->distinct()
            ->orderBy('anio', 'desc')
            ->pluck('anio');
            
        // OBTIENE EL ROL DEL USUARIO ACTUALMENTE LOGUEADO
        // SE PASA A LA VISTA PARA CONTROLAR QUÉ ACCIONES PUEDE VER SEGÚN SU ROL
        $userRole = Auth::user()->role;
        
        // RETORNA LA VISTA DEL PLAN DE AUDITORÍA CON LOS AÑOS DISPONIBLES Y EL ROL DEL USUARIO
        return view('auditoria.plan.index', compact('anios', 'userRole'));
    }
}