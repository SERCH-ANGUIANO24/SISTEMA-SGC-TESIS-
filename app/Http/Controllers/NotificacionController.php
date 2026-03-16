<?php

namespace App\Http\Controllers;

use App\Models\Notificacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificacionController extends Controller
{
    // ── DASHBOARD PRINCIPAL ──────────────────────────────────────
    public function index()
    {
        $notificaciones = Notificacion::deUsuario(Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $totalNoLeidas = Notificacion::deUsuario(Auth::id())
            ->noLeidas()->count();

        return view('notificaciones.index', compact('notificaciones', 'totalNoLeidas'));
    }

    // ── MARCAR UNA COMO LEÍDA ────────────────────────────────────
    public function marcarLeida(Notificacion $n)
    {
        abort_unless($n->user_id === Auth::id(), 403);
        $n->update(['leida' => true]);
        return response()->json(['ok' => true]);
    }

    // ── MARCAR TODAS COMO LEÍDAS ─────────────────────────────────
    public function marcarTodasLeidas()
    {
        Notificacion::deUsuario(Auth::id())->noLeidas()->update(['leida' => true]);
        return response()->json(['ok' => true]);
    }

    // ── ELIMINAR UNA NOTIFICACIÓN ────────────────────────────────
    public function destroy(Notificacion $n)
    {
        abort_unless($n->user_id === Auth::id(), 403);
        $n->delete();
        return response()->json(['ok' => true]);
    }

    // ── LIMPIAR TODAS LAS LEÍDAS ─────────────────────────────────
    public function limpiar()
    {
        Notificacion::deUsuario(Auth::id())->where('leida', true)->delete();
        return response()->json(['ok' => true]);
    }

    // ── API: CONTEO DE NO LEÍDAS (para el badge del navbar) ──────
    public function conteo()
    {
        $count = Notificacion::deUsuario(Auth::id())->noLeidas()->count();
        return response()->json(['count' => $count]);
    }

    // ── API: ÚLTIMAS 5 NOTIFICACIONES (para el dropdown navbar) ──
    public function ultimas()
    {
        $notifs = Notificacion::deUsuario(Auth::id())
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get([
                'id','titulo','mensaje','tipo','icono','leida','url','created_at'
            ]);
        return response()->json($notifs);
    }
}
