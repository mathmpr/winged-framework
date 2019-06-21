<?php

namespace Winged\Error;

/**
 *
 * Class ShutdownCallback
 *
 * @package Winged\Error
 */
class ShutdownCallback
{

    public static $errors = [
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
        8192 => "E_DEPRECATED",
        30719 => 'E_ALL'
    ];

    /**
     * Executed when an error of any level happens
     *
     * @param $errno
     * @param $errstr
     * @param $errfile
     * @param $errline
     *
     * @return bool
     */
    public static function errorHandler($errno, $errstr, $errfile, $errline)
    {
        $list = SilencedErrors::getListOfSilencedFunctions();
        $_config = false;
        foreach ($list as $key => $config) {
            if (is_int(stripos($errstr, $key))) {
                $_config = $config;
            }
        }
        if (!$_config) {
            Error::push(self::$errors[$errno], $errstr, $errfile, $errline, self::getTrace());
            return true;
        }
        if ($_config['fatal']) {
            Error::push(self::$errors[$errno], $errstr, $errfile, $errline, self::getTrace());
        }
        return true;
    }

    /**
     * Executed on end of script execution
     */
    public static function shutdownHandler()
    {
        $error = error_get_last();
        if (!empty($error)) {
            $error = self::parseTrace($error);
            Error::push(self::$errors[$error['errno']], $error['errstr'], $error['errfile'], $error['errline'], $error['trace']);
        }
        Error::display();
    }

    /**
     * get stack trace as array
     *
     * @return array
     */
    public static function getTrace()
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        if ($trace) {
            unset($trace[0]);
            unset($trace[1]);
            $trace = array_values($trace);
            return $trace;
        }
        return [];
    }

    /**
     * parse last error into a formated error
     *
     * @param $error array
     *
     * @return array
     */
    public static function parseTrace($error)
    {
        $message = explode("\n", $error['message']);
        if(strlen($error['message']) >= 1024){
            array_pop($message);
            $message[] = '#90 [error have more than 1024 chars](n\a): [error have more than 1024 chars]->[error have more than 1024 chars]()';
        }
        $messageString = trim(explode('\' in ', $message[0])[0] . "'");
        unset($message[0]);
        unset($message[1]);
        unset($message[10]);
        unset($message[11]);
        $message = array_values($message);
        array_walk($message, function ($value, $key) use (&$message) {
            $message[$key] = substr($value, 3, strlen($value) - 1);
        });
        foreach ($message as $key => $value) {
            $line = false;
            $file = false;
            $mExp = explode(': ', $value);
            if (count($mExp) >= 2) {
                preg_match('#\((.*?)\)#', $mExp[0], $matchs);
                if (!empty($matchs)) {
                    $line = trim($matchs[1]);
                    $mExp[0] = str_replace('(' . $matchs[1] . ')', '', $mExp[0]);
                    $file = $mExp[0];
                } else {
                    $file = $mExp[0];
                    $line = 'internal';
                }
            }
            if($mExp[1] === 'Winged\Controller\Con'){
                pre_clear_buffer_die($message);
            }
            $exp = explode('->', $mExp[1]);
            if (count($exp) >= 2) {
                $type = '->';
                $class = $exp[0];
                preg_match('#\((.*?)\)#', $exp[1], $matchs);
                if (!empty($matchs)) {
                    $function = str_replace('(' . $matchs[1] . ')', '(', $exp[1]);
                    if (is_int(stripos($function, '(')) && is_int(stripos($function, ')'))) {
                        while (is_int(stripos($function, '(')) && is_int(stripos($function, ')'))) {
                            preg_match('#\((.*?)\)#', $function, $matchs);
                            $function = str_replace('(' . $matchs[1] . ')', '(', $function);
                            if (!is_int(stripos($function, '(')) && !is_int(stripos($function, ')'))) {
                                $function = str_replace('(' . $matchs[1] . ')', '', $function);
                            }
                        }
                        $function = str_replace('(', '', $function);
                    } else {
                        $function = str_replace('(' . $matchs[1] . ')', '', $exp[1]);
                    }
                } else {
                    $function = str_replace('()', '', $exp[1]);
                }
            } else {
                $exp = explode('::', $mExp[1]);
                if (count($exp) >= 2) {
                    $type = '::';
                    $class = $exp[0];
                    preg_match('#\((.*?)\)#', $exp[1], $matchs);
                    if (!empty($matchs)) {
                        $function = str_replace('(' . $matchs[1] . ')', '(', $exp[1]);
                        if (is_int(stripos($function, '(')) && is_int(stripos($function, ')'))) {
                            while (is_int(stripos($function, '(')) && is_int(stripos($function, ')'))) {
                                preg_match('#\((.*?)\)#', $function, $matchs);
                                $function = str_replace('(' . $matchs[1] . ')', '(', $function);
                                if (!is_int(stripos($function, '(')) && !is_int(stripos($function, ')'))) {
                                    $function = str_replace('(' . $matchs[1] . ')', '', $function);
                                }
                            }
                            $function = str_replace('(', '', $function);
                        } else {
                            $function = str_replace('(' . $matchs[1] . ')', '', $exp[1]);
                        }
                    } else {
                        $function = str_replace('()', '', $exp[1]);
                    }
                } else {
                    $type = 'procedural call';
                    $class = 'procedural call';
                    pre_clear_buffer_die($exp);
                    $function = $value[1][1];
                }
            }
            $message[$key] = [
                'file' => $file,
                'line' => $line,
                'function' => $function,
                'class' => $class,
                'type' => $type
            ];
        }
        return [
            'errno' => $error['type'],
            'errstr' => $messageString,
            'errfile' => $error['file'],
            'errline' => $error['line'],
            'trace' => $message
        ];
    }
}