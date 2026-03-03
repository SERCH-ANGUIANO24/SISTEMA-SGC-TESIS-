<?php
// app/Http/Controllers/Auditoria/SolicitudMejoraController.php
namespace App\Http\Controllers\Auditoria;

use App\Http\Controllers\Controller;
use App\Models\SolicitudMejora;
use App\Models\Auditoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SolicitudMejoraController extends Controller
{
    public function index()
    {
        $auditorias = Auditoria::orderBy('fecha_auditoria', 'desc')->get();
        return view('auditoria.solicitudes.index', compact('auditorias'));
    }

    public function getData(Request $request)
    {
        $query = SolicitudMejora::with('auditoria');

        if ($request->filled('estatus')) {
            $query->where('estatus', $request->estatus);
        }

        $solicitudes = $query->orderBy('fecha_solicitud', 'desc')->get();

        return response()->json($solicitudes);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre_solicitud' => 'required|string|max:255',
            'tipo_auditoria' => 'required|in:Interna,Externa',
            'folio_solicitud' => 'required|string|unique:solicitudes_mejora',
            'responsable_accion' => 'required|string|max:255',
            'fecha_solicitud' => 'required|date',
            'fecha_aplicacion' => 'required|date',
            'actividades_verificacion' => 'required|string',
            'fecha_verificacion' => 'required|date',
            'estatus' => 'required|in:Cerrado,En Proceso',
            'auditoria_id' => 'nullable|exists:auditorias,id',
            'archivo' => 'required|file|mimes:pdf,doc,docx,xls,xlsx|max:10240'
        ]);

        if ($request->hasFile('archivo')) {
            $file = $request->file('archivo');
            $path = $file->store('solicitudes-mejora', 'public');
            
            $validated['archivo_path'] = $path;
            $validated['archivo_nombre'] = $file->getClientOriginalName();
        }

        $solicitud = SolicitudMejora::create($validated);

        return response()->json(['success' => true, 'data' => $solicitud]);
    }

    public function update(Request $request, $id)
    {
        $solicitud = SolicitudMejora::findOrFail($id);

        $validated = $request->validate([
            'nombre_solicitud' => 'required|string|max:255',
            'tipo_auditoria' => 'required|in:Interna,Externa',
            'folio_solicitud' => 'required|string|unique:solicitudes_mejora,folio_solicitud,' . $id,
            'responsable_accion' => 'required|string|max:255',
            'fecha_solicitud' => 'required|date',
            'fecha_aplicacion' => 'required|date',
            'actividades_verificacion' => 'required|string',
            'fecha_verificacion' => 'required|date',
            'estatus' => 'required|in:Cerrado,En Proceso',
            'auditoria_id' => 'nullable|exists:auditorias,id'
        ]);

        $solicitud->update($validated);

        return response()->json(['success' => true, 'data' => $solicitud]);
    }

    public function destroy($id)
    {
        $solicitud = SolicitudMejora::findOrFail($id);
        
        if ($solicitud->archivo_path) {
            Storage::disk('public')->delete($solicitud->archivo_path);
        }
        
        $solicitud->delete();

        return response()->json(['success' => true]);
    }

    public function download($id)
    {
        $solicitud = SolicitudMejora::findOrFail($id);
        
        if (!Storage::disk('public')->exists($solicitud->archivo_path)) {
            return response()->json(['error' => 'Archivo no encontrado'], 404);
        }

        return Storage::disk('public')->download($solicitud->archivo_path, $solicitud->archivo_nombre);
    }
}