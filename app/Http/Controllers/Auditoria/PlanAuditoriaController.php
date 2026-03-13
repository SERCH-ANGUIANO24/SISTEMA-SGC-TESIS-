<?php
// app/Http/Controllers/Auditoria/PlanAuditoriaController.php
namespace App\Http\Controllers\Auditoria;

use App\Http\Controllers\Controller;
use App\Models\Auditoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PlanAuditoriaController extends Controller
{
    public function index()
    {
        $anios = Auditoria::select('anio')->distinct()->orderBy('anio', 'desc')->pluck('anio');
        $userRole = Auth::user()->role;
        
        return view('auditoria.plan.index', compact('anios', 'userRole'));
    }
}