<?php

namespace Winged\Error;

/**
 *
 * Class ShutdownCallback
 * @package Winged\Error
 */
class ShutdownCallback
{
    /**
     * Executed when an error of any level happens
     * @param $errno
     * @param $errstr
     * @param $errfile
     * @param $errline
     * @param array $errcontext
     */
    public static function errorHandler($errno, $errstr, $errfile, $errline, $errcontext = [])
    {
        $erros = [
            "1" => "E_ERROR",
            "2" => "E_WARNING",
            "4" => "E_PARSE",
            "8" => "E_NOTICE",
            "16" => "E_CORE_ERROR",
            "32" => "E_CORE_WARNING",
            "64" => "E_COMPILE_ERROR",
            "128" => "E_COMPILE_WARNING",
            "256" => "E_USER_ERROR",
            "512" => "E_USER_WARNING",
            "1024" => "E_USER_NOTICE",
            "6143" => "E_ALL",
            "2048" => "E_STRICT",
            "4096" => "E_RECOVERABLE_ERROR",
            "8192" => "E_DEPRECATED"
        ];
        Error::push($erros[$errno], $errstr, $errfile, $errline, $errcontext);
    }

    /**
     * Executed when a fatal error happens
     */
    public static function shutdownHandler()
    {
        $error = error_get_last();
        if (!empty($error)) {
            Error::_die($error["message"], $error["line"], $error["file"], $error["line"]);
        }
    }
}