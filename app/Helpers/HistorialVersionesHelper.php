<?php

namespace App\Helpers;

use App\Models\HistorialVersiones;
use Illuminate\Support\Facades\Log;

class HistorialVersionesHelper
{
    public static function registrar($modulo, $accion, $elemento, $descripcionAdicional = null, $datosAnteriores = null, $datosNuevos = null)
    {
        try {
            $user = auth()->user();
            if (!$user) {
                return null;
            }

            $nombreElemento = self::obtenerNombreElemento($elemento);
            $descripcion = self::construirDescripcion($accion, $modulo, $nombreElemento, $descripcionAdicional);
            $nivelImportancia = self::determinarImportancia($accion, $modulo, $elemento);

            return HistorialVersiones::create([
                'usuario_nombre' => $user->name ?? 'Sistema',
                'usuario_id' => $user->id ?? null,
                'usuario_email' => $user->email ?? null,
                'usuario_rol' => $user->role ?? 'sistema',
                'modulo' => strtoupper($modulo),
                'accion' => strtoupper($accion),
                'descripcion' => $descripcion,
                'nivel_importancia' => $nivelImportancia,
                'datos_anteriores' => $datosAnteriores,
                'datos_nuevos' => $datosNuevos,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'registro_id' => self::obtenerRegistroId($elemento),
                'tabla_afectada' => self::obtenerTabla($elemento),
                'elemento_nombre' => $nombreElemento
            ]);
        } catch (\Exception $e) {
            Log::error('Error registrando en historial: ' . $e->getMessage());
            return null;
        }
    }

    public static function crear($modulo, $elemento, $descripcionAdicional = null)
    {
        return self::registrar(
            $modulo,
            'CREAR',
            $elemento,
            $descripcionAdicional,
            null,
            self::convertirAArray($elemento)
        );
    }

    public static function editar($modulo, $elemento, $cambios = null, $descripcionAdicional = null)
    {
        $datosAnteriores = self::convertirAArray($elemento, true);
        $datosNuevos = $cambios ?? self::convertirAArray($elemento);

        return self::registrar(
            $modulo,
            'EDITAR',
            $elemento,
            $descripcionAdicional,
            $datosAnteriores,
            $datosNuevos
        );
    }

    public static function eliminar($modulo, $elemento, $descripcionAdicional = null)
    {
        return self::registrar(
            $modulo,
            'ELIMINAR',
            $elemento,
            $descripcionAdicional,
            self::convertirAArray($elemento),
            null
        );
    }

    public static function restaurar($modulo, $elemento, $descripcionAdicional = null)
    {
        return self::registrar(
            $modulo,
            'RESTAURAR',
            $elemento,
            $descripcionAdicional,
            null,
            self::convertirAArray($elemento)
        );
    }

    public static function ver($modulo, $elemento = null, $tipoVista = 'listado')
    {
        return self::registrar(
            $modulo,
            'VER',
            $elemento ?? $tipoVista,
            "Visualizó: $tipoVista"
        );
    }

    public static function descargar($modulo, $archivo, $tipo = 'documento')
    {
        return self::registrar(
            $modulo,
            'DESCARGAR',
            $archivo,
            "Descargó $tipo"
        );
    }

    public static function login($user)
    {
        return self::registrar(
            'SISTEMA',
            'LOGIN',
            $user,
            'Inicio de sesión en el sistema'
        );
    }

    public static function logout($user)
    {
        return self::registrar(
            'SISTEMA',
            'LOGOUT',
            $user,
            'Cierre de sesión en el sistema'
        );
    }

    private static function obtenerNombreElemento($elemento)
    {
        if (is_object($elemento)) {
            $camposPosibles = ['nombre', 'titulo', 'name', 'codigo', 'descripcion', 'email', 'id'];
            foreach ($camposPosibles as $campo) {
                if (isset($elemento->$campo)) {
                    $valor = (string) $elemento->$campo;
                    // Si el valor es muy largo o parece una frase repetida, lo limitamos
                    return self::limpiarTexto($valor);
                }
            }
            if (method_exists($elemento, 'getNombreDescriptivo')) {
                return self::limpiarTexto($elemento->getNombreDescriptivo());
            }
            return "ID: " . ($elemento->id ?? 'N/A');
        }
        if (is_array($elemento)) {
            foreach (['nombre', 'titulo', 'name', 'codigo', 'descripcion', 'email'] as $campo) {
                if (isset($elemento[$campo])) {
                    return self::limpiarTexto((string) $elemento[$campo]);
                }
            }
            return "ID: " . ($elemento['id'] ?? 'N/A');
        }
        return self::limpiarTexto((string) $elemento);
    }

    private static function limpiarTexto($texto)
    {
        // Si el texto contiene repeticiones de la misma palabra más de 3 veces, lo truncamos
        $palabras = explode(' ', $texto);
        $conteo = array_count_values($palabras);
        foreach ($conteo as $palabra => $cantidad) {
            if ($cantidad > 3 && strlen($palabra) > 3) {
                // Probablemente es una repetición, devolvemos solo las primeras 5 palabras
                return implode(' ', array_slice($palabras, 0, 5)) . '...';
            }
        }
        return $texto;
    }

    private static function obtenerRegistroId($elemento)
    {
        if (is_object($elemento) && isset($elemento->id)) {
            return $elemento->id;
        }
        if (is_array($elemento) && isset($elemento['id'])) {
            return $elemento['id'];
        }
        return null;
    }

    private static function obtenerTabla($elemento)
    {
        if (is_object($elemento) && method_exists($elemento, 'getTable')) {
            return $elemento->getTable();
        }
        return null;
    }

    private static function convertirAArray($elemento, $original = false)
    {
        if (is_object($elemento)) {
            if ($original && method_exists($elemento, 'getOriginal')) {
                return $elemento->getOriginal();
            }
            if (method_exists($elemento, 'toArray')) {
                return $elemento->toArray();
            }
            return json_decode(json_encode($elemento), true);
        }
        if (is_array($elemento)) {
            return $elemento;
        }
        return ['valor' => $elemento];
    }

    private static function construirDescripcion($accion, $modulo, $nombreElemento, $descripcionAdicional)
    {
        $verbos = [
            'CREAR' => 'creó',
            'EDITAR' => 'editó',
            'ELIMINAR' => 'eliminó',
            'RESTAURAR' => 'restauró',
            'VER' => 'visualizó',
            'DESCARGAR' => 'descargó',
            'LOGIN' => 'inició sesión',
            'LOGOUT' => 'cerró sesión'
        ];
        $verbo = $verbos[$accion] ?? strtolower($accion);
        $moduloFormateado = ucfirst(strtolower($modulo));

        if (in_array($accion, ['LOGIN', 'LOGOUT'])) {
            $descripcion = "El usuario {$nombreElemento} {$verbo}";
        } else {
            $descripcion = "Se {$verbo} {$nombreElemento} en {$moduloFormateado}";
        }

        if ($descripcionAdicional) {
            $descripcion .= " - {$descripcionAdicional}";
        }

        return $descripcion;
    }

    private static function determinarImportancia($accion, $modulo, $elemento)
    {
        if (in_array($accion, ['ELIMINAR'])) {
            return 'critico';
        }
        if ($accion === 'EDITAR' && $modulo === 'USUARIOS') {
            return 'alto';
        }
        if ($accion === 'CREAR' && in_array($modulo, ['USUARIOS', 'AUDITORIAS'])) {
            return 'alto';
        }
        if (in_array($accion, ['CREAR', 'EDITAR', 'RESTAURAR'])) {
            return 'normal';
        }
        return 'bajo';
    }
}