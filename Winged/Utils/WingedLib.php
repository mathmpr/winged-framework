<?php

namespace Winged\Utils;

/**
 * Class WingedLib
 */
class WingedLib
{

    public static function clearDocumentRoot()
    {
        $exp = explode(':/', DOCUMENT_ROOT);
        if(count7($exp) === 2){
            return $exp[1];
        }
        return $exp[0];
    }

    /**
     * @param string $path
     * @return string
     */
    public static function normalizePath($path = '')
    {
        if ($path === '') {
            return './';
        }
        if (strlen($path) > 0) {
            $path = trim(str_replace("\\", "/", $path));
            $path = trim(str_replace("/./", "/", $path));
            if ($path[0] === '/') {
                $path = '.' . $path;
            }
            if ($path[strlen($path) - 1] != '/') {
                $path .= '/';
            }
            if ((!is_int(stripos($path, './'))) || (is_int(stripos($path, './')) && ((int)stripos($path, './')) > 0)) {
                $path = './' . $path;
            }
            $path = trim(str_replace("/./", "/", $path));
            return trim($path);
        }
        return './';
    }

    /**
     * @param string $path
     * @return array|bool
     */
    public static function explodePath($path = '')
    {
        $path = self::clearPath($path);
        if ($path) {
            $path = explode('/', $path);
        }
        return $path;
    }

    public static function clearPath($path)
    {
        $path = self::normalizePath($path);
        $path = str_replace('./', '', $path);
        if (strlen($path) > 0) {
            $path = substr_replace($path, '', strlen($path) - 1, 1);
            return trim($path);
        }
        return false;
    }

    public static function convertslash($str)
    {
        return trim(str_replace("\\", "/", $str));
    }
}