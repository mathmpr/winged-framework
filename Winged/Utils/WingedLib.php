<?php

namespace Winged;

/**
 * Class WingedLib
 */
class WingedLib
{
    /**
     * @param $str
     * @return string
     */
    public static function convertslash($str)
    {
        return trim(str_replace("\\", "/", $str));
    }

    /**
     * @param $str
     * @param bool $give
     * @return bool|string
     */
    public static function dotslash($str, $give = false)
    {
        $str = trim($str);
        if ($str != "") {
            if ($give) {
                if ($str[strlen($str) - 1] != "/") {
                    $str .= "/";
                }
                if ($str[0] != "." && $str[1] != "/") {
                    $str = "./" . $str;
                }
                return trim($str);
            } else {
                if ($str[strlen($str) - 1] == "/") {
                    $str = substr_replace($str, '', strlen($str) - 1, 1);
                }
                return trim(str_replace("./", "", $str));
            }
        }
        return false;
    }

    /**
     * @param $str
     * @return array
     */
    public static function slashexplode($str)
    {
        if (strlen($str) >= 2) {
            if ($str[0] == "." && $str[1] == "/") {
                $str = substr_replace($str, '', 0, 2);
            }
        }
        if (strlen($str) > 0) {
            if ($str[0] == "/") {
                $str = substr_replace($str, '', 0, 1);
            }
            if ($str[strlen($str) - 1] == "/") {
                $str = substr_replace($str, '', strlen($str) - 1, 1);
            }
            if (trim($str) != "") {
                return explode("/", trim($str));
            }
        }
        return [];
    }

    /**
     * @param $arr
     * @return array
     */
    public static function resetarray($arr)
    {
        $new = [];
        foreach ($arr as $key => $value) {
            array_push($new, $value);
        }
        return $new;
    }

}