<?php

namespace Winged\Date;

/**
 * Class Microtime
 * @package Winged\Microtime
 */
class Microtime
{

    public static $microtime;
    public static $partials = [];
    public static $begin = null;
    private static $part = false;

    /**
     * Returns the execution time until the call of this method if the $ return parameter is true. Adds the time in a partial as well.
     * @return bool|null
     */
    public static function init()
    {
        if (self::$begin === null) {
            self::$begin = (int)server('request_time');
        }
        $micro = numeric_is(number_format(microtime(true) - ((int)self::$begin), 3));
        self::$microtime = $micro;
        self::$partials[] = $micro;
        return $micro;
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
        self::$part = true;
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
        if (!self::$part) {
            $end = end(self::$partials);
            $now = self::partial();
        } else {
            self::$part = false;
            $end = self::$partials[count7(self::$partials) - 2];
            $now = end(self::$partials);
        }
        $mili = (float)$now - (float)$end;
        return number_format($mili, 3);
    }
}