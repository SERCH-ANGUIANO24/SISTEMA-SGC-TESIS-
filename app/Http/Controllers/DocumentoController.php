<?php
// app/Http/Controllers/DocumentoController.php

namespace App\Http\Controllers;

use App\Services\NotificacionService;
use App\Models\{Documento, User};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DocumentoController extends Controller
{
    // MÉTODO store() — cuando el usuario sube un archivo
    public function store(Request $request, NotificacionService $notif)
    {
        $request->validate([
            'nombre'   => 'required|string|max:255',
            'archivo'  => 'required|file|mimes:pdf,doc,docx,xls,xlsx|max:25600',
            'categoria'=> 'required|string',
        ]);

        // Guardar archivo en storage
        $path = $request->file('archivo')->store('documentos', 'public');

        // Crear registro en base de datos
        $documento = Documento::create([
            'nombre'    => $request->nombre,
            'ruta'      => $path,
            'categoria' => $request->categoria,
            'user_id'   => Auth::id(),
            'estado'    => 'pendiente',
        ]);

        // ── NOTIFICAR A TODOS LOS ADMINISTRADORES ─────────────────
        $admins = User::whereIn('role', ['superadmin', 'admin'])
                       ->where('activo', true)->get();

        foreach ($admins as $admin) {
            $notif->enviar(
                userId:     $admin->id,
                titulo:     'Documento pendiente de validación',
                mensaje:    Auth::user()->name . ' subió el archivo "' .
                            $documento->nombre . '". Requiere revisión y validación.',
                tipo:       'info',
                icono:      'bi-file-earmark-arrow-up',
                url:        route('documentos.show', $documento->id),
                email:      true,
                docId:      (string) $documento->id,
                tipoEvento: 'subida'
            );
        }

        return redirect()->route('documentos.index')
            ->with('success', 'Documento subido correctamente. El administrador será notificado.');
    }

        // MÉTODO aprobar() — admin valida el documento como aprobado
    public function aprobar(Request $request, Documento $documento, NotificacionService $notif)
    {
        $documento->update([
            'estado'      => 'aprobado',
            'validado_por'=> Auth::id(),
            'validado_at' => now(),
        ]);

        // ── NOTIF 1: Al usuario que subió el documento ─────────────
        $notif->enviar(
            userId:     $documento->user_id,
            titulo:     'Tu documento ha sido aprobado ✓',
            mensaje:    'El documento "' . $documento->nombre . '" fue revisado y ' .
                        'aprobado por ' . Auth::user()->name . '. Ya está disponible en el sistema.',
            tipo:       'exito',
            icono:      'bi-file-earmark-check',
            url:        route('documentos.show', $documento->id),
            email:      true,
            docId:      (string) $documento->id,
            tipoEvento: 'aprobado'
        );

        // ── NOTIF 2: A TODOS los usuarios (doc disponible) ─────────
        $notif->enviarATodos(
            titulo:     'Nuevo documento disponible: ' . $documento->nombre,
            mensaje:    'El documento "' . $documento->nombre . '" ha sido autorizado y ' .
                        'está disponible para consulta en Gestión Documental.',
            tipo:       'info',
            icono:      'bi-file-earmark-check2',
            url:        route('documentos.show', $documento->id),
            email:      true,
            docId:      (string) $documento->id,
            tipoEvento: 'publicado'
        );

        return redirect()->back()
            ->with('success', 'Documento aprobado. Todos los usuarios fueron notificados.');
    }

        // MÉTODO rechazar() — admin rechaza con observaciones
    public function rechazar(Request $request, Documento $documento, NotificacionService $notif)
    {
        $request->validate([
            'observaciones' => 'required|string|min:10|max:1000',
        ]);

        $documento->update([
            'estado'        => 'rechazado',
            'observaciones' => $request->observaciones,
            'validado_por'  => Auth::id(),
            'validado_at'   => now(),
        ]);

        // ── NOTIFICAR SOLO AL USUARIO QUE SUBIÓ EL ARCHIVO ─────────
        // Construimos el mensaje incluyendo las observaciones
        $mensajeNotif = 'El documento "' . $documento->nombre . '" no fue aprobado por ' .
                        Auth::user()->name . '.' . PHP_EOL . PHP_EOL .
                        'Observaciones: ' . $request->observaciones;

        $notif->enviar(
            userId:     $documento->user_id,
            titulo:     'Tu documento requiere correcciones',
            mensaje:    $mensajeNotif,
            tipo:       'error',
            icono:      'bi-file-earmark-x',
            url:        route('documentos.show', $documento->id),
            email:      true,
            docId:      (string) $documento->id,
            tipoEvento: 'rechazado'
        );

        return redirect()->back()
            ->with('warning', 'Documento rechazado. El usuario fue notificado con las observaciones.');
    }
}


