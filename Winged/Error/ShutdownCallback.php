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
     * @return bool
     */
    public static function errorHandler($errno, $errstr, $errfile, $errline, $errcontext = [])
    {
        $errors = [
            1 => "E_ERROR",
            2 => "E_WARNING",
            4 => "E_PARSE",
            8 => "E_NOTICE",
            16 => "E_CORE_ERROR",
            32 => "E_CORE_WARNING",
            64 => "E_COMPILE_ERROR",
            128 => "E_COMPILE_WARNING",
            256 => "E_USER_ERROR",
            512 => "E_USER_WARNING",
            1024 => "E_USER_NOTICE",
            6143 => "E_ALL",
            2048 => "E_STRICT",
            4096 => "E_RECOVERABLE_ERROR",
            8192 => "E_DEPRECATED"
        ];
        $list = SilencedErrors::getListOfSilencedFunctions();
        $_config = false;
        foreach ($list as $key => $config) {
            if (is_int(stripos($errstr, $key))) {
                $_config = $config;
            }
        }
        if (!$_config) {
            Error::push($errors[$errno], $errstr, $errfile, $errline, $errcontext);
            return true;
        }
        if ($_config['fatal']) {
            Error::push($errors[$errno], $errstr, $errfile, $errline, $errcontext);
        }
        return true;
    }

    /**
     * Executed when a fatal error happens
     * @return bool
     */
    public static function shutdownHandler()
    {
        $errors = [
            1 => "E_ERROR",
            2 => "E_WARNING",
            4 => "E_PARSE",
            8 => "E_NOTICE",
            16 => "E_CORE_ERROR",
            32 => "E_CORE_WARNING",
            64 => "E_COMPILE_ERROR",
            128 => "E_COMPILE_WARNING",
            256 => "E_USER_ERROR",
            512 => "E_USER_WARNING",
            1024 => "E_USER_NOTICE",
            6143 => "E_ALL",
            2048 => "E_STRICT",
            4096 => "E_RECOVERABLE_ERROR",
            8192 => "E_DEPRECATED"
        ];
        $error = error_get_last();
        if (!empty($error)) {
            $list = SilencedErrors::getListOfSilencedFunctions();
            $_config = false;
            foreach ($list as $key => $config) {
                if (is_int(stripos($error['message'], $key))) {
                    $_config = $config;
                }
            }
            if (!$_config) {
                Error::_die($errors[$error['type']], $error["message"], $error["line"], $error["file"], $error["line"]);
                return true;
            }
            if ($_config['fatal']) {
                Error::_die($errors[$error['type']], $error["message"], $error["line"], $error["file"], $error["line"]);
            }
        }
        return true;
    }
}