<?php

class CoreBuffer{

    public static $ob_buffer;

    public static function start()
    {
        if (!self::$ob_buffer) {
            if (ob_start('mb_output_handler')) {
                self::$ob_buffer = true;
            }
        }
    }

    public static function getKill()
    {
        if (self::$ob_buffer > 0) {
            $content = ob_get_contents();
            self::kill();
            self::$ob_buffer = false;
            return $content;
        }
        return false;
    }

    public static function getFlush()
    {
        if (self::$ob_buffer > 0) {
            $content = ob_get_contents();
            ob_flush();
            self::$ob_buffer = false;
            return $content;
        }
        return false;
    }

    public static function get()
    {
        if (self::$ob_buffer > 0) {
            $content = ob_get_contents();;
            return $content;
        }
        return false;
    }

    public static function flush()
    {
        if (self::$ob_buffer > 0) {
            ob_flush();
            self::$ob_buffer = false;
        }
    }

    public static function flushKill()
    {
        if (self::$ob_buffer > 0) {
            ob_end_flush();
            self::$ob_buffer = false;
        }
    }

    public static function kill()
    {
        if (self::$ob_buffer > 0) {
            ob_end_clean();
            self::$ob_buffer = false;
        }
    }

    public static function reset()
    {
        self::kill();
        self::start();
    }

}