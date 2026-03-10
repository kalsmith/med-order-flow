<?php

namespace App\Helpers;

class RutHelper
{
    public static function validate($rut)
    {
        $rut = preg_replace('/[^k0-9]/i', '', $rut);
        if (strlen($rut) < 2) return false;

        $dv  = substr($rut, -1);
        $numero = substr($rut, 0, strlen($rut) - 1);
        $i = 2;
        $suma = 0;
        foreach (array_reverse(str_split($numero)) as $v) {
            if ($i == 8) $i = 2;
            $suma += $v * $i;
            $i++;
        }
        $dvr = 11 - ($suma % 11);

        if ($dvr == 11) $dvr = 0;
        if ($dvr == 10) $dvr = 'K';

        return strtoupper($dv) == strtoupper($dvr);
    }
}
