<?php

namespace App\Http\Controllers\Notificaciones;

use App\Http\Controllers\Controller;
use App\Models\Notificacion;
use Illuminate\Http\{Request, JsonResponse};
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class NotificacionController extends Controller
{
    // ──────────────────────────────────────────────────────────────
    // DASHBOARD PRINCIPAL
    // Muestra todas las notificaciones del usuario con filtros y paginación
    // ──────────────────────────────────────────────────────────────
    public function index(): View
    {
        $notificaciones = Notificacion::deUsuario(Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $totalNoLeidas = Notificacion::deUsuario(Auth::id())
            ->noLeidas()
            ->count();

        return view('notificaciones.index', compact(
            'notificaciones',
            'totalNoLeidas'
        ));
    }

    // ──────────────────────────────────────────────────────────────
    // MARCAR UNA NOTIFICACIÓN COMO LEÍDA
    // Llamado por AJAX desde el dashboard y el navbar
    // ──────────────────────────────────────────────────────────────
    public function marcarLeida(Notificacion $n): JsonResponse
    {
        // Verificar que la notificación pertenece al usuario autenticado
        abort_unless($n->user_id === Auth::id(), 403, 'Acceso no autorizado');

        $n->update(['leida' => true]);

        return response()->json([
            'ok'      => true,
            'message' => 'Notificación marcada como leída',
        ]);
    }

    // ──────────────────────────────────────────────────────────────
    // MARCAR TODAS LAS NOTIFICACIONES COMO LEÍDAS
    // ──────────────────────────────────────────────────────────────
    public function marcarTodasLeidas(): JsonResponse
    {
        $count = Notificacion::deUsuario(Auth::id())
            ->noLeidas()
            ->update(['leida' => true]);

        return response()->json([
            'ok'      => true,
            'count'   => $count,
            'message' => $count . ' notificaciones marcadas como leídas',
        ]);
    }

    // ──────────────────────────────────────────────────────────────
    // ELIMINAR UNA NOTIFICACIÓN
    // ──────────────────────────────────────────────────────────────
    public function destroy(Notificacion $n): JsonResponse
    {
        abort_unless($n->user_id === Auth::id(), 403, 'Acceso no autorizado');

        $n->delete();

        return response()->json([
            'ok'      => true,
            'message' => 'Notificación eliminada',
        ]);
    }

    // ──────────────────────────────────────────────────────────────
    // LIMPIAR TODAS LAS NOTIFICACIONES LEÍDAS
    // ──────────────────────────────────────────────────────────────
    public function limpiar(): JsonResponse
    {
        $count = Notificacion::deUsuario(Auth::id())
            ->where('leida', true)
            ->delete();

        return response()->json([
            'ok'      => true,
            'count'   => $count,
            'message' => $count . ' notificaciones eliminadas',
        ]);
    }

    // ──────────────────────────────────────────────────────────────
    // API: CONTEO DE NO LEÍDAS
    // Usada por el badge del navbar cada 30 segundos (polling)
    // ──────────────────────────────────────────────────────────────
    public function conteo(): JsonResponse
    {
        $count = Notificacion::deUsuario(Auth::id())
            ->noLeidas()
            ->count();

        return response()->json(['count' => $count]);
    }

    // ──────────────────────────────────────────────────────────────
    // API: ÚLTIMAS 5 NOTIFICACIONES
    // Usada por el dropdown del navbar para mostrar preview
    // ──────────────────────────────────────────────────────────────
    public function ultimas(): JsonResponse
    {
        $notifs = Notificacion::deUsuario(Auth::id())
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get([
                'id',
                'titulo',
                'mensaje',
                'tipo',
                'icono',
                'leida',
                'url',
                'tipo_evento',
                'created_at',
            ]);

        return response()->json($notifs);
    }

    // ──────────────────────────────────────────────────────────────
    // API: NOTIFICACIONES PAGINADAS CON FILTROS (opcional)
    // Para filtrar por tipo o estado desde el dashboard via AJAX
    // ──────────────────────────────────────────────────────────────
    public function filtrar(Request $request): JsonResponse
    {
        $query = Notificacion::deUsuario(Auth::id());

        if ($request->filled('tipo')) {
            $query->where('tipo', $request->tipo);
        }

        if ($request->estado === 'no-leida') {
            $query->noLeidas();
        } elseif ($request->estado === 'leida') {
            $query->where('leida', true);
        }

        $notifs = $query->orderBy('created_at', 'desc')->paginate(15);

        return response()->json($notifs);
    }
}
