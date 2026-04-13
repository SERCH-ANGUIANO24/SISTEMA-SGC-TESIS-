<?php

namespace App\Http\Controllers\Notificaciones;

use App\Http\Controllers\Controller;
use App\Models\Notificacion;
use Illuminate\Http\{Request, JsonResponse};
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/*
|--------------------------------------------------------------------------
| CONTROLADOR: NOTIFICACIONES
|--------------------------------------------------------------------------
| SE ENCARGA DE GESTIONAR TODAS LAS NOTIFICACIONES DEL USUARIO:
| MOSTRARLAS, MARCARLAS COMO LEÍDAS, ELIMINARLAS Y FILTRARLAS.
*/

class NotificacionController extends Controller
{
    // ──────────────────────────────────────────────────────────────
    // DASHBOARD PRINCIPAL
    // MUESTRA TODAS LAS NOTIFICACIONES DEL USUARIO CON FILTROS Y PAGINACION
    // ──────────────────────────────────────────────────────────────

    /*
    | MUESTRA TODAS LAS NOTIFICACIONES DEL USUARIO ORDENADAS POR FECHA.
    | TAMBIÉN CUENTA CUÁNTAS NO HAN SIDO LEÍDAS AÚN.
    | DEVUELVE LA VISTA: notificaciones/index
    */
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
    // LLAMADO POR AJAX DESDE EL DASHBOIARD Y ELL NAVBAR
    // ──────────────────────────────────────────────────────────────

    /*
    | VERIFICA QUE LA NOTIFICACIÓN PERTENEZCA AL USUARIO Y LA MARCA COMO LEÍDA.
    | SI EL USUARIO NO ES EL DUEÑO → DEVUELVE ERROR 403 (ACCESO DENEGADO)
    | SI TODO ESTÁ BIEN             → DEVUELVE RESPUESTA JSON DE ÉXITO
    */
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

    /*
    | MARCA TODAS LAS NOTIFICACIONES NO LEÍDAS DEL USUARIO COMO LEÍDAS.
    | DEVUELVE JSON CON CUÁNTAS NOTIFICACIONES FUERON ACTUALIZADAS.
    */
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

    /*
    | VERIFICA QUE LA NOTIFICACIÓN PERTENEZCA AL USUARIO Y LA ELIMINA.
    | SI EL USUARIO NO ES EL DUEÑO → DEVUELVE ERROR 403 (ACCESO DENEGADO)
    | SI TODO ESTÁ BIEN             → DEVUELVE RESPUESTA JSON DE ÉXITO
    */
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

    /*
    | ELIMINA TODAS LAS NOTIFICACIONES QUE YA FUERON LEÍDAS POR EL USUARIO.
    | DEVUELVE JSON CON CUÁNTAS NOTIFICACIONES FUERON ELIMINADAS.
    */
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

    /*
    | CUENTA CUÁNTAS NOTIFICACIONES NO LEÍDAS TIENE EL USUARIO.
    | USADA POR EL ÍCONO DEL NAVBAR PARA MOSTRAR EL NÚMERO EN ROJO.
    */
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

    /*
    | OBTIENE LAS ÚLTIMAS 5 NOTIFICACIONES DEL USUARIO.
    | USADA PARA MOSTRAR EL PREVIEW EN EL MENÚ DESPLEGABLE DEL NAVBAR.
    */
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
    // PARA FILTRAR POR TIPO O POR ESTADO VIA AJAX
    // ──────────────────────────────────────────────────────────────

    /*
    | FILTRA LAS NOTIFICACIONES DEL USUARIO POR TIPO Y/O ESTADO.
    |   - TIPO  : FILTRA POR CATEGORÍA (EJ: ALERTA, INFO, ETC.)
    |   - ESTADO: FILTRA POR "LEÍDA" O "NO LEÍDA"
    | DEVUELVE JSON CON LOS RESULTADOS PAGINADOS DE 15 EN 15.
    */
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