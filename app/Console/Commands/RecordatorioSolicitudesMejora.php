<?php

//ESTE ARCHIVO PROGRAMA EL ENVUIO AUTOMATICO DE CORREOS ELECTRONICOS Y NOTIFICACIONES CUANDO FALTAN 3 DIAS PARA QUE SE CUMPLA EL PLAZO DE 15 DIAS 
//PARA ENTREGAR LA SOLICITUD DE MEJORA Y SE SUBA A LA PLATAFORMA
//TAMBIEN SE ENVIAN LAS NOTIFICACIONES POR AUTOMATICO 3 DIAS ANTES DE QUE SE CUMPLA EL PLAZO DE VENCIMIENTO DE 27 DIAS DE CADA MES
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SolicitudMejora;
use App\Models\User;
use App\Services\NotificacionService;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RecordatorioSolicitudesMejora extends Command
{
    protected $signature   = 'solicitudes:recordatorio';
    protected $description = 'Envía recordatorio 3 días antes del límite de fecha de aplicación (día 27) y recordatorio día 12 hábil';

    public function handle()
    {
        $notif = app(NotificacionService::class);

        // ── BLOQUE 1: Lo que ya tenías (día 27 del mes) ───────────────
        //PARA PROBAR EL ENVIO AUTOMATICO DE CORREOS AUTOMATICOS QUE LE AVISEN AL USUARIO QUE FALTAN 3 DIAS PARA ENTREGAR SU SOLICITUD DE MEJORA
        //SE DEBE DE CAMBIAR if (now()->day === 27) POR if(true)
        if (now()->day === 27) {

            $solicitudes = SolicitudMejora::where('estatus', 'En Proceso')
                ->whereNotNull('fecha_aplicacion')
                ->whereMonth('fecha_aplicacion', now()->month)
                ->whereYear('fecha_aplicacion', now()->year)
                ->get();

            if ($solicitudes->isNotEmpty()) {

                $mapaProcesos = $this->getMapaProcesos();

                foreach ($solicitudes as $solicitud) {
                    $proceso     = $solicitud->procesos_auditados;
                    $fechaLimFmt = Carbon::parse($solicitud->fecha_aplicacion)
                                         ->locale('es')->isoFormat('D [de] MMMM [de] YYYY');

                    $departamentosDelProceso = $mapaProcesos[$proceso] ?? [];

                    $usuariosANotificar = User::where('is_active', true)
                        ->where(function ($query) use ($proceso, $departamentosDelProceso) {
                            $query->where('proceso', $proceso);
                            if (!empty($departamentosDelProceso)) {
                                $query->orWhereIn('departamento', $departamentosDelProceso);
                            }
                        })
                        ->get()
                        ->unique('id');

                    foreach ($usuariosANotificar as $usuario) {
                        $notif->enviar(
                            userId:     $usuario->id,
                            titulo:     '⚠️ Recordatorio: Fecha límite de solicitud en 3 días',
                            mensaje:    'Tu solicitud de tipo "' . $solicitud->tipo_solicitud . '" ' .
                                        'para el proceso "' . $proceso . '" vence el ' . $fechaLimFmt . '.' . PHP_EOL . PHP_EOL .
                                        '⏰ Tienes 3 días para completarla.' . PHP_EOL .
                                        'No. Identificación: ' . ($solicitud->folio_solicitud ?? '-') . PHP_EOL .
                                        'Responsable: ' . ($solicitud->responsable_accion ?? 'No establecido') . PHP_EOL . PHP_EOL .
                                        'Ingresa al sistema para revisarla antes de que venza el plazo.',
                            tipo:       'advertencia',
                            icono:      'bi-exclamation-triangle',
                            url:        route('auditoria.solicitudes.index'),
                            email:      true,
                            docId:      (string) $solicitud->id,
                            tipoEvento: 'recordatorio_solicitud'
                        );
                    }

                    $this->info('Recordatorio día 27 enviado para solicitud ID: ' . $solicitud->id);
                }
            } else {
                $this->info('No hay solicitudes con fecha límite próxima.');
            }
        } else {
            $this->info('Hoy no es día 27, se omite el bloque mensual.');
        }

        // ── BLOQUE 2: Nuevo — recordatorio día 12 hábil ───────────────
        $hoy = Carbon::today();

        $solicitudesSinEstatus = SolicitudMejora::whereNotNull('fecha_informe')
            ->where(function ($q) {
                $q->whereNull('estatus')->orWhere('estatus', '');
            })
            ->get();

        $mapaProcesos = $this->getMapaProcesos();
        $recordatoriosEnviados = 0;

        foreach ($solicitudesSinEstatus as $solicitud) {

            $fechaInicio = Carbon::parse($solicitud->fecha_informe);

            // Calcular día 12 hábil desde fecha_informe
            $diasHabiles = 0;
            $fechaDia12  = $fechaInicio->copy();
            while ($diasHabiles < 12) {
                $fechaDia12->addDay();
                if (!in_array($fechaDia12->dayOfWeek, [Carbon::SATURDAY, Carbon::SUNDAY])) {
                    $diasHabiles++;
                }
            }

            // Calcular día 15 hábil (fecha límite final)
            $diasHabiles15 = 0;
            $fechaLimite   = $fechaInicio->copy();
            while ($diasHabiles15 < 15) {
                $fechaLimite->addDay();
                if (!in_array($fechaLimite->dayOfWeek, [Carbon::SATURDAY, Carbon::SUNDAY])) {
                    $diasHabiles15++;
                }
            }

            // Solo enviar si hoy es exactamente el día 12 hábil
            //PARA PROBAR EL ENVIO DE NOTIFICAIONES EN AUTOMATICO, SE SUSTITUYR If(!$hoy->isSameDay($fechaDia12)) por if(false)
            if (!$hoy->isSameDay($fechaDia12)) {
                continue;
            }

            $proceso            = $solicitud->procesos_auditados;
            $tipoSolicitud      = $solicitud->tipo_solicitud ?? 'Solicitud';
            $fechaLimiteFormato = $fechaLimite->locale('es')->isoFormat('D [de] MMMM [de] YYYY');

            if (!$proceso) continue;

            // Obtener todos los procesos relacionados por departamento
            $procesosRelacionados    = $this->getProcesosDelDepartamento($proceso);
            $departamentosDelProceso = $mapaProcesos[$proceso] ?? [];

            $usuariosANotificar = User::where('is_active', true)
                ->where(function ($query) use ($procesosRelacionados, $departamentosDelProceso) {
                    $query->whereIn('proceso', $procesosRelacionados);
                    if (!empty($departamentosDelProceso)) {
                        $query->orWhereIn('departamento', $departamentosDelProceso);
                    }
                })
                ->get()
                ->unique('id');

            foreach ($usuariosANotificar as $usuario) {
                $notif->enviar(
                    userId:     $usuario->id,
                    titulo:     '⏰ Recordatorio: tu solicitud de mejora vence en 3 días',
                    mensaje:    'Te recordamos que la solicitud de tipo "' . $tipoSolicitud . '" ' .
                                'para el proceso "' . $proceso . '" está próxima a vencer.' . PHP_EOL . PHP_EOL .
                                '⏱ Plazo de atención:' . PHP_EOL .
                                'Faltan 3 días hábiles para el vencimiento.' . PHP_EOL .
                                'Fecha límite: ' . $fechaLimiteFormato . PHP_EOL .
                                'No. Identificación: ' . ($solicitud->folio_solicitud ?? '-') . PHP_EOL . PHP_EOL .
                                'Por favor ingresa al sistema y atiende esta solicitud antes de que venza el plazo.',
                    tipo:       'advertencia',
                    icono:      'bi-alarm',
                    url:        route('auditoria.solicitudes.index'),
                    email:      true,
                    docId:      (string) $solicitud->id,
                    tipoEvento: 'recordatorio_solicitud'
                );
            }

            $recordatoriosEnviados++;
            $this->info("Recordatorio día 12 enviado para solicitud ID: {$solicitud->id}");
        }

        $this->info("Recordatorios día 12 hábil enviados: {$recordatoriosEnviados}");
    }

    // ── MÉTODO PRIVADO: mapa proceso → departamentos ──────────────
    private function getMapaProcesos(): array
    {
        $mapa = [
            'Planeación'                         => ['Rectoría', 'Dirección Académica', 'Dirección de Administración y Finanzas'],
            'Preinscripción'                     => ['Servicios Escolares'],
            'Inscripción'                        => ['Servicios Escolares'],
            'Reinscripción'                      => ['Servicios Escolares'],
            'Titulación'                         => ['Servicios Escolares'],
            'Enseñanza/Aprendizaje'              => ['Dirección Académica'],
            'Contratación o Control de Personal' => ['Recursos Humanos'],
            'Vinculación'                        => ['Vinculación'],
            'TI'                                 => ['Sistemas Computacionales'],
            'Gestión de Recursos'                => ['Recursos Financieros', 'Almacén'],
            'Laboratorios y Talleres'            => ['Encargado/a de Laboratorios'],
            'Centro de Información'              => ['Biblioteca'],
        ];

        $procesosCustom = DB::table('procesos_custom')
            ->select('proceso', 'departamento')
            ->get();

        foreach ($procesosCustom as $pc) {
            $p = trim($pc->proceso);
            $d = trim($pc->departamento);
            if (!$p || !$d) continue;
            if (!isset($mapa[$p])) $mapa[$p] = [];
            if (!in_array($d, $mapa[$p])) $mapa[$p][] = $d;
        }

        return $mapa;
    }

        private function getProcesosDelDepartamento(string $proceso): array
    {
        $mapaProcesos = $this->getMapaProcesos();

        // Obtener los departamentos del proceso seleccionado
        $departamentosDelProceso = $mapaProcesos[$proceso] ?? [];

        if (empty($departamentosDelProceso)) {
            return [$proceso];
        }

        // Buscar todos los procesos que comparten esos mismos departamentos
        $procesosRelacionados = [$proceso];

        foreach ($mapaProcesos as $p => $departamentos) {
            foreach ($departamentosDelProceso as $depto) {
                if (in_array($depto, $departamentos) && !in_array($p, $procesosRelacionados)) {
                    $procesosRelacionados[] = $p;
                }
            }
        }

        return $procesosRelacionados;
    }
}