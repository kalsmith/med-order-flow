<?php

namespace App\Support;

class RutHelper
{
    /**
     * Limpia el RUT para guardarlo en BD (sin puntos ni guion, solo números y K)
     */
    public static function clean($rut): string
    {
        return strtoupper(preg_replace('/[^0-9kK]/', '', $rut));
    }

    /**
     * Formatea el RUT para mostrar en la Vista (12.345.678-K)
     */
    public static function format($rut): string
    {
        $rut = self::clean($rut);
        if (strlen($rut) < 2) return $rut;

        $dv = substr($rut, -1);
        $numero = substr($rut, 0, -1);
        return number_format($numero, 0, '', '.') . '-' . $dv;
    }
}
