<?php

namespace Winged\Buffer;

/**
 * This class create a buffer of output HTML. It is possible to create a buffer only
 * Class Buffer
 */
class Buffer{

    public static $ob_buffer;

    /**
     * Start buffer
     */
    public static function start()
    {
        if (!self::$ob_buffer) {
            if (ob_start('mb_output_handler')) {
                self::$ob_buffer = true;
            }
        }
    }

    /**
     * Try to return content of buffer and finish the buffer
     * @return bool|string
     */
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

    /**
     * Try to return content of buffer and output / free buffer
     * @return bool|string
     */
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

    /**
     * Try to return content of buffer
     * @return bool|string
     */
    public static function get()
    {
        if (self::$ob_buffer > 0) {
            $content = ob_get_contents();;
            return $content;
        }
        return false;
    }

    /**
     * Try output / free buffer
     */
    public static function flush()
    {
        if (self::$ob_buffer > 0) {
            ob_flush();
            self::$ob_buffer = false;
        }
    }

    /**
     * Try output / free buffer and finish him
     */
    public static function flushKill()
    {
        if (self::$ob_buffer > 0) {
            ob_end_flush();
            self::$ob_buffer = false;
        }
    }

    /**
     * Destroy buffer
     */
    public static function kill()
    {
        if (self::$ob_buffer > 0) {
            ob_end_clean();
            self::$ob_buffer = false;
        }
    }

    /**
     * Restart buffer
     */
    public static function reset()
    {
        self::kill();
        self::start();
    }
}