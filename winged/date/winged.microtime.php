<?php

class Microtime
{

    private static $microtime;
    private static $partials = [];

    public static function init($return = false)
    {
        $micro = microtime();
        list($mili, $time) = explode(' ', $micro);
        if (isset($mili) && isset($time)) {
            $mili = str_replace(',', '.', number_format($mili, 3));
            if ($return) return $mili;
            self::$microtime = $mili;
            self::$partials[] = $mili;
            return true;
        }
        return null;
    }

    public static function initial()
    {
        return self::$microtime;
    }

    public static function partial()
    {
        self::$partials[] = self::init(true);
        return end(self::$partials);
    }

    public static function diff()
    {
        $end = end(self::$partials);
        self::$partials[] = self::init(true);
        $mili = (double)end(self::$partials) - (double)$end;
        return number_format($mili . '000', 3);
    }
}