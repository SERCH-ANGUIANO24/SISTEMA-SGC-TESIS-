<?php

namespace App\Http\Controllers;

use App\Models\HistorialVersiones;
use App\Models\User;
use App\Helpers\HistorialVersionesHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HistorialVersionesController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');

        $this->middleware(function ($request, $next) {
            $user = auth()->user();
            
            if ($request->route()->getName() === 'historial-versiones.mis-actividades') {
                return $next($request);
            }

            if ($user->role !== 'superadmin') {
                if ($request->ajax()) {
                    return response()->json(['error' => 'No autorizado'], 403);
                }
                abort(403, 'Solo el superadministrador puede acceder al historial completo.');
            }

            return $next($request);
        });
    }

    public function index(Request $request)
    {
        HistorialVersionesHelper::ver('HISTORIAL', null, 'dashboard');

        $query = HistorialVersiones::with('usuario');

        // --- EXCLUIR REGISTROS NO DESEADOS ---
        $query->whereNotIn('modulo', ['HISTORIAL', 'DASHBOARD']);
        $query->where('descripcion', 'not like', '%visualizó%');
        // --------------------------------------

        $modulo = $request->get('modulo');
        $accion = $request->get('accion');
        $usuario_id = $request->get('usuario_id');
        $fecha_inicio = $request->get('fecha_inicio', now()->subDays(30)->format('Y-m-d'));
        $fecha_fin = $request->get('fecha_fin', now()->format('Y-m-d'));
        $importancia = $request->get('importancia');

        if ($modulo && $modulo !== 'todos') {
            $query->where('modulo', $modulo);
        }

        if ($accion && $accion !== 'todos') {
            $query->where('accion', $accion);
        }

        if ($usuario_id && $usuario_id !== 'todos') {
            $query->where('usuario_id', $usuario_id);
        }

        if ($fecha_inicio && $fecha_fin) {
            $query->whereBetween('created_at', [
                $fecha_inicio . ' 00:00:00',
                $fecha_fin . ' 23:59:59'
            ]);
        }

        if ($importancia && $importancia !== 'todos') {
            $query->where('nivel_importancia', $importancia);
        }

        $actividades = $query->orderByDesc('created_at')->paginate(20);

        $estadisticas = [
            'total_hoy' => HistorialVersiones::whereDate('created_at', today())->count(),
            'total_semana' => HistorialVersiones::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'total_mes' => HistorialVersiones::whereMonth('created_at', now()->month)
                                            ->whereYear('created_at', now()->year)
                                            ->count(),
            'total_general' => HistorialVersiones::count(),
        ];

        $modulos = HistorialVersiones::select('modulo')
            ->distinct()
            ->orderBy('modulo')
            ->pluck('modulo');

        $usuarios = User::orderBy('name')->get(['id', 'name']);

        $acciones = ['CREAR', 'EDITAR', 'ELIMINAR', 'RESTAURAR', 'VER', 'DESCARGAR', 'MOVIR', 'VALIDAR'];
        $importancias = ['bajo', 'normal', 'alto', 'critico'];

        return view('historial_versiones.index', compact(
            'actividades',
            'estadisticas',
            'modulos',
            'usuarios',
            'acciones',
            'importancias',
            'modulo',
            'accion',
            'usuario_id',
            'fecha_inicio',
            'fecha_fin',
            'importancia'
        ));
    }

    public function misActividades()
    {
        $user = auth()->user();

        $actividades = HistorialVersiones::where('usuario_id', $user->id)
            ->orderByDesc('created_at')
            ->paginate(20);

        $totalHoy = HistorialVersiones::where('usuario_id', $user->id)
            ->whereDate('created_at', today())
            ->count();

        $totalSemana = HistorialVersiones::where('usuario_id', $user->id)
            ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
            ->count();

        $totalMes = HistorialVersiones::where('usuario_id', $user->id)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        return view('historial_versiones.mis-actividades', compact('actividades', 'totalHoy', 'totalSemana', 'totalMes'));
    }

    public function show($id)
    {
        $actividad = HistorialVersiones::with('usuario')->findOrFail($id);
        HistorialVersionesHelper::ver('HISTORIAL', $actividad, 'detalle');
        return view('historial_versiones.show', compact('actividad'));
    }

    public function restaurar($id)
    {
        $historial = HistorialVersiones::findOrFail($id);

        if ($historial->accion !== 'ELIMINAR') {
            return redirect()->back()->with('error', 'Solo se pueden restaurar elementos eliminados.');
        }

        $modelMap = [
            'anexos' => \App\Models\Anexos::class,
            'documentos' => \App\Models\Documental::class,
            'matriz' => \App\Models\Matriz::class,
            'formatos' => \App\Models\Formato::class,
        ];

        $tabla = $historial->tabla_afectada;
        $modelClass = $modelMap[$tabla] ?? null;

        if (!$modelClass || !class_exists($modelClass)) {
            return redirect()->back()->with('error', "No se puede restaurar: modelo no encontrado para la tabla '$tabla'.");
        }

        if (!in_array('Illuminate\Database\Eloquent\SoftDeletes', class_uses($modelClass))) {
            return redirect()->back()->with('error', 'El modelo no soporta restauración (SoftDeletes requerido).');
        }

        try {
            $model = $modelClass::withTrashed()->find($historial->registro_id);

            if (!$model) {
                return redirect()->back()->with('error', 'El registro a restaurar no existe.');
            }

            if ($model->trashed()) {
                $model->restore();
                HistorialVersionesHelper::restaurar($historial->modulo, $model);
                return redirect()->back()->with('success', 'Elemento restaurado correctamente.');
            } else {
                return redirect()->back()->with('info', 'El elemento no estaba eliminado.');
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al restaurar: ' . $e->getMessage());
        }
    }

    public function datosGraficos(Request $request)
    {
        $dias = $request->get('dias', 30);
        $data = [];
        $labels = [];

        for ($i = $dias; $i >= 0; $i--) {
            $fecha = now()->subDays($i)->format('Y-m-d');
            $labels[] = now()->subDays($i)->format('d/m');
            $data[] = HistorialVersiones::whereDate('created_at', $fecha)->count();
        }

        return response()->json(['labels' => $labels, 'data' => $data]);
    }

    public function exportar(Request $request)
    {
        return redirect()->back()->with('info', 'Función de exportación en desarrollo');
    }

    public function limpiar(Request $request)
    {
        if (auth()->user()->role !== 'superadmin') {
            return redirect()->back()->with('error', 'No autorizado');
        }

        $dias = $request->get('dias', 90);
        $fechaLimite = now()->subDays($dias);

        $eliminados = HistorialVersiones::where('created_at', '<', $fechaLimite)->delete();

        return redirect()->back()->with('success', "Se eliminaron {$eliminados} registros antiguos");
    }
}