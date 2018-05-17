<?php

namespace Winged\Http;

class Session
{

    private static $inited = false;

    public static function init()
    {
        if (!isset($_SESSION)) {
            session_start();
            self::$inited = true;
        }
    }

    public static function set($key, $value)
    {
        self::init();
        if (!array_key_exists($key, $_SESSION)) {
            $_SESSION[$key] = $value;
            return $value;
        }
        return false;
    }

    public static function update($key, $value)
    {
        self::init();
        if (array_key_exists($key, $_SESSION)) {
            $_SESSION[$key] = $value;
            return $value;
        }
        return false;
    }

    public static function always($key, $value)
    {
        self::init();
        $_SESSION[$key] = $value;
        return $value;
    }

    public static function get($key)
    {
        self::init();
        if (array_key_exists($key, $_SESSION)) {
            return $_SESSION[$key];
        }
        return false;
    }

    public static function remove($key)
    {
        self::init();
        if (array_key_exists($key, $_SESSION)) {
            unset($_SESSION[$key]);
            return true;
        }
        return false;
    }

    public static function finish()
    {
        self::init();
        foreach ($_SESSION as $key => $value) {
            unset($_SESSION[$key]);
        }
        session_destroy();
        return true;
    }

}

