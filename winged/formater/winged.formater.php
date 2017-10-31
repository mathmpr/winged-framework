<?php

class CoreFormater
{

    public static function intToCurrency($int = 0, $length = 2, $left = ',', $right = '.')
    {
        $currency = (string)($int / 100);
        return number_format($currency, $length, $right, $left);
    }

}