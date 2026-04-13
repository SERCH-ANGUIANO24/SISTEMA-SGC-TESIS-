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

// COMANDO ARTISAN QUE SE EJECUTA AUTOMÁTICAMENTE (VÍA SCHEDULER)
// ENVÍA RECORDATORIOS DE SOLICITUDES DE MEJORA EN DOS MOMENTOS:
// - BLOQUE 1: EL DÍA 27 DE CADA MES (3 DÍAS ANTES DEL VENCIMIENTO MENSUAL)
// - BLOQUE 2: EL DÍA 12 HÁBIL DESDE LA FECHA DE INFORME (3 DÍAS ANTES DEL LÍMITE DE 15 DÍAS HÁBILES)
class RecordatorioSolicitudesMejora extends Command
{
    // NOMBRE DEL COMANDO PARA EJECUTARLO CON: php artisan solicitudes:recordatorio
    protected $signature   = 'solicitudes:recordatorio';
    protected $description = 'Envía recordatorio 3 días antes del límite de fecha de aplicación (día 27) y recordatorio día 12 hábil';

    public function handle()
    {
        // OBTIENE EL SERVICIO DE NOTIFICACIONES DEL CONTENEDOR DE LARAVEL
        $notif = app(NotificacionService::class);

        // ── BLOQUE 1: (3 DIAS ANTES DEL 27 DE CADA MES) ───────────────
        //PARA PROBAR EL ENVIO AUTOMATICO DE CORREOS AUTOMATICOS QUE LE AVISEN AL USUARIO QUE FALTAN 3 DIAS PARA ENTREGAR SU SOLICITUD DE MEJORA
        //SE DEBE DE CAMBIAR if (now()->day === 27) POR if(true)

        // SOLO SE EJECUTA SI HOY ES DÍA 27 DEL MES
        if (now()->day === 27) {

            // BUSCA SOLICITUDES EN PROCESO CON FECHA DE APLICACIÓN EN EL MES Y AÑO ACTUAL
            $solicitudes = SolicitudMejora::where('estatus', 'En Proceso')
                ->whereNotNull('fecha_aplicacion')
                ->whereMonth('fecha_aplicacion', now()->month)
                ->whereYear('fecha_aplicacion', now()->year)
                ->get();

            if ($solicitudes->isNotEmpty()) {

                // OBTIENE EL MAPA DE PROCESOS Y SUS DEPARTAMENTOS
                $mapaProcesos = $this->getMapaProcesos();

                foreach ($solicitudes as $solicitud) {
                    $proceso     = $solicitud->procesos_auditados;

                    // FORMATEA LA FECHA LÍMITE EN ESPAÑOL (EJ: 30 de mayo de 2025)
                    $fechaLimFmt = Carbon::parse($solicitud->fecha_aplicacion)
                                         ->locale('es')->isoFormat('D [de] MMMM [de] YYYY');

                    // OBTIENE LOS DEPARTAMENTOS ASOCIADOS AL PROCESO
                    $departamentosDelProceso = $mapaProcesos[$proceso] ?? [];

                    // BUSCA USUARIOS ACTIVOS QUE PERTENEZCAN AL PROCESO O A SUS DEPARTAMENTOS
                    // unique('id') EVITA NOTIFICAR DOS VECES AL MISMO USUARIO
                    $usuariosANotificar = User::where('is_active', true)
                        ->where(function ($query) use ($proceso, $departamentosDelProceso) {
                            $query->where('proceso', $proceso);
                            if (!empty($departamentosDelProceso)) {
                                $query->orWhereIn('departamento', $departamentosDelProceso);
                            }
                        })
                        ->get()
                        ->unique('id');

                    // ENVÍA LA NOTIFICACIÓN Y CORREO A CADA USUARIO ENCONTRADO
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

        // FECHA DE HOY PARA COMPARAR CON EL DÍA 12 HÁBIL CALCULADO
        $hoy = Carbon::today();

        // BUSCA SOLICITUDES QUE TENGAN FECHA DE INFORME PERO AÚN NO TENGAN ESTATUS ASIGNADO
        $solicitudesSinEstatus = SolicitudMejora::whereNotNull('fecha_informe')
            ->where(function ($q) {
                $q->whereNull('estatus')->orWhere('estatus', '');
            })
            ->get();

        $mapaProcesos = $this->getMapaProcesos();
        $recordatoriosEnviados = 0;

        foreach ($solicitudesSinEstatus as $solicitud) {

            // TOMA LA FECHA DE INFORME COMO PUNTO DE INICIO PARA CONTAR DÍAS HÁBILES
            $fechaInicio = Carbon::parse($solicitud->fecha_informe);

            // Calcular día 12 hábil desde fecha_informe
            // AVANZA DÍA POR DÍA SALTANDO SÁBADOS Y DOMINGOS HASTA LLEGAR AL DÍA 12 HÁBIL
            $diasHabiles = 0;
            $fechaDia12  = $fechaInicio->copy();
            while ($diasHabiles < 12) {
                $fechaDia12->addDay();
                if (!in_array($fechaDia12->dayOfWeek, [Carbon::SATURDAY, Carbon::SUNDAY])) {
                    $diasHabiles++;
                }
            }

            // Calcular día 15 hábil (fecha límite final)
            // AVANZA DÍA POR DÍA SALTANDO SÁBADOS Y DOMINGOS HASTA LLEGAR AL DÍA 15 HÁBIL (FECHA LÍMITE)
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

            // SI HOY NO ES EL DÍA 12 HÁBIL, SALTA ESTA SOLICITUD Y PASA A LA SIGUIENTE
            if (!$hoy->isSameDay($fechaDia12)) {
                continue;
            }

            $proceso            = $solicitud->procesos_auditados;
            $tipoSolicitud      = $solicitud->tipo_solicitud ?? 'Solicitud';

            // FORMATEA LA FECHA LÍMITE EN ESPAÑOL (EJ: 30 de mayo de 2025)
            $fechaLimiteFormato = $fechaLimite->locale('es')->isoFormat('D [de] MMMM [de] YYYY');

            // SI LA SOLICITUD NO TIENE PROCESO ASIGNADO, SE OMITE
            if (!$proceso) continue;

            // Obtener todos los procesos relacionados por departamento
            // OBTIENE PROCESOS QUE COMPARTEN DEPARTAMENTOS CON EL PROCESO DE ESTA SOLICITUD
            $procesosRelacionados    = $this->getProcesosDelDepartamento($proceso);
            $departamentosDelProceso = $mapaProcesos[$proceso] ?? [];

            // BUSCA USUARIOS ACTIVOS DE LOS PROCESOS Y DEPARTAMENTOS RELACIONADOS
            // unique('id') EVITA NOTIFICAR DOS VECES AL MISMO USUARIO
            $usuariosANotificar = User::where('is_active', true)
                ->where(function ($query) use ($procesosRelacionados, $departamentosDelProceso) {
                    $query->whereIn('proceso', $procesosRelacionados);
                    if (!empty($departamentosDelProceso)) {
                        $query->orWhereIn('departamento', $departamentosDelProceso);
                    }
                })
                ->get()
                ->unique('id');

            // ENVÍA LA NOTIFICACIÓN Y CORREO A CADA USUARIO ENCONTRADO
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

    // DEVUELVE UN ARREGLO QUE MAPEA CADA PROCESO CON SUS DEPARTAMENTOS
    // PRIMERO CARGA LOS PROCESOS ESTÁTICOS Y LUEGO AGREGA LOS PERSONALIZADOS DE LA BD
    private function getMapaProcesos(): array
    {
        // MAPA ESTÁTICO DE PROCESOS Y SUS DEPARTAMENTOS CORRESPONDIENTES
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
            'Sistema de Gestión de la Calidad'   => ['Rectoría', 'Auditoría', 'Coordinador del SGC'],
        ];

        // CARGA PROCESOS PERSONALIZADOS DESDE LA TABLA procesos_custom EN LA BASE DE DATOS
        $procesosCustom = DB::table('procesos_custom')
            ->select('proceso', 'departamento')
            ->get();

        // AGREGA CADA PROCESO CUSTOM AL MAPA (SI NO EXISTE LO CREA, SI YA EXISTE AGREGA EL DEPARTAMENTO)
        foreach ($procesosCustom as $pc) {
            $p = trim($pc->proceso);
            $d = trim($pc->departamento);
            if (!$p || !$d) continue;
            if (!isset($mapa[$p])) $mapa[$p] = [];
            if (!in_array($d, $mapa[$p])) $mapa[$p][] = $d;
        }

        return $mapa;
    }

        // DADO UN PROCESO, DEVUELVE TODOS LOS PROCESOS QUE COMPARTEN AL MENOS UN DEPARTAMENTO CON ÉL
        // SIRVE PARA NOTIFICAR A USUARIOS DE PROCESOS RELACIONADOS, NO SOLO AL PROCESO DIRECTO
    private function getProcesosDelDepartamento(string $proceso): array
    {
        $mapaProcesos = $this->getMapaProcesos();

        // Obtener los departamentos del proceso seleccionado
        // OBTIENE LOS DEPARTAMENTOS QUE PERTENECEN AL PROCESO DADO
        $departamentosDelProceso = $mapaProcesos[$proceso] ?? [];

        // SI EL PROCESO NO TIENE DEPARTAMENTOS EN EL MAPA, RETORNA SOLO ÉL MISMO
        if (empty($departamentosDelProceso)) {
            return [$proceso];
        }

        // Buscar todos los procesos que comparten esos mismos departamentos
        // RECORRE EL MAPA BUSCANDO PROCESOS QUE COMPARTAN ALGÚN DEPARTAMENTO CON EL PROCESO DADO
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