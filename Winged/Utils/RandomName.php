<?php

namespace Winged\Utils;

/**
 * Class RandomName
 */
class RandomName
{
    /**
     * @param string $size
     * @param bool $repeat
     * @param bool $toupper
     * @return string
     */
    public static function generate($size = "sssiii", $repeat = true, $toupper = true)
    {
        $l = ["a", "b", "c", "d",
            "e", "f", "g", "h",
            "i", "j", "k", "l",
            "m", "n", "o", "p",
            "q", "r", "s", "t",
            "u", "v", "w", "x",
            "y", "z"];
        $size = strtolower($size);
        $size = str_replace(" ", "", $size);
        $token = [];
        $stoken = "";
        for ($x = 0; $x < strlen($size); $x++) {
            if ($size[$x] == "s") {
                $r = rand(0, 25);
                if ($repeat == false) {
                    while (in_array($r, $token) && $x <= 25) {
                        $r = rand(0, 25);
                    }
                }
                $token[] = $r;
            } else if ($size[$x] == "i") {
                $r = rand(0, 9);
                if ($repeat == false) {
                    while (in_array($r, $token) && $x <= 25) {
                        $r = rand(0, 9);
                    }
                }
                $token[] = $r;
            }
        }
        for ($x = 0; $x < strlen($size); $x++) {
            if ($size[$x] == "s") {
                $stoken = $stoken . $l[$token[$x]];
            } else if ($size[$x] == "i") {
                $stoken = $stoken . $token[$x];
            }
        }
        if ($toupper == true) {
            return strtoupper($stoken);
        } else {
            return $stoken;
        }
    }
}