<?php

namespace App\Support;

class RutHelper
{
    /**
     * Limpia el RUT: remueve todo excepto números y la letra K.
     */
    public static function clean($rut): string
    {
        if (!$rut) return '';
        return strtoupper(preg_replace('/[^0-9kK]/', '', $rut));
    }

    /**
     * Formatea el RUT para vista: 12.345.678-K
     */
    public static function format($rut): string
    {
        $rut = self::clean($rut);
        if (strlen($rut) < 2) return $rut;

        $dv = substr($rut, -1);
        $numero = substr($rut, 0, -1);
        return number_format($numero, 0, '', '.') . '-' . $dv;
    }

    /**
     * Opcional: Para validar si un RUT es auténtico antes de guardar
     */
    public static function validate($rut): bool
    {
        $rut = self::clean($rut);
        if (strlen($rut) < 2) return false;

        $dv = substr($rut, -1);
        $numero = substr($rut, 0, -1);

        return strtoupper($dv) === self::calculateDV($numero);
    }

    public static function calculateDV($numero): string
    {
        $numero = preg_replace('/[^0-9]/', '', $numero);
        $factor = 2;
        $suma = 0;

        for ($i = strlen($numero) - 1; $i >= 0; $i--) {
            $suma += $numero[$i] * $factor;
            $factor = $factor === 7 ? 2 : $factor + 1;
        }

        $resto = $suma % 11;
        $dv = 11 - $resto;

        if ($dv == 11) return '0';
        if ($dv == 10) return 'K';

        return (string)$dv;
    }
}
