<?php

namespace Winged\Date;

/**
 * Class Microtime
 * @package Winged\Microtime
 */
class Microtime
{

    private static $microtime;
    private static $partials = [];

    /**
     * Returns the execution time until the call of this method if the $ return parameter is true. Adds the time in a partial as well.
     * @param bool $return
     * @return bool|null
     */
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

    /**
     * Returns last time of partials
     * @return mixed
     */
    public static function last()
    {
        return self::$microtime;
    }

    /**
     * Returns the execution time until the call of this method if the $ return parameter is true. Adds the time in a partial as well.
     * @return mixed
     */
    public static function partial()
    {
        self::init();
        return self::last();
    }

    /**
     * Creates a partial and compares with the last partial before this. Thus returning the time difference between them.
     * Returns the diference time bet
     * @return string
     */
    public static function diff()
    {
        $end = end(self::$partials);
        $now = self::partial();
        $mili = (double)$now - (double)$end;
        return number_format($mili . '000', 3);
    }
}