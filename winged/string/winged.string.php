<?php

class CoreString
{
    public static function removeAccents($str, $lowerCase = true)
    {
        $nom = array(
            'á', 'à', 'é', 'è', 'ó', 'ò', 'í', 'ì', 'ú', 'ù', 'ö', 'ü', 'ë', 'ä', 'ï', 'ç', 'ã', 'õ', 'ê', 'â', 'î', 'ô', 'û', 'ñ', 'ý', 'ÿ', 'Á', 'À', 'É', 'È', 'Ó', 'Ò', 'Í', 'Ì', 'Ú', 'Ù', 'Ö', 'Ü', 'Ë', 'Ä', 'Ï', 'Ç', 'Ã', 'Õ', 'Ê', 'Â', 'Î', 'Ô', 'Û', 'Ñ', 'Ý'
        );
        $con = array(
            'a', 'a', 'e', 'e', 'o', 'o', 'i', 'i', 'u', 'u', 'o', 'u', 'e', 'a', 'i', 'c', 'a', 'o', 'e', 'a', 'i', 'o', 'u', 'n', 'y', 'y', 'A', 'A', 'E', 'E', 'O', 'O', 'I', 'I', 'U', 'U', 'O', 'U', 'E', 'A', 'I', 'C', 'A', 'O', 'E', 'A', 'I', 'O', 'U', 'N', 'Y'
        );
        if ($lowerCase == true) {
            return strtolower(str_replace($nom, $con, trim($str)));
        } else {
            return str_replace($nom, $con, trim($str));
        }
    }

    public static function removeWhiteSpaces($str, $lowerCase = true)
    {
        $str = trim($str);
        while (is_int(stripos($str, '  '))) {
            $str = str_replace('  ', ' ', $str);
        }
        $str = str_replace(' ', '-', trim($str));
        $nom = array(
            '”', '“', ':', '_', "'", '"', '*', '(', ')', '´', '`', '~', '¨', '¬', '<', '>', '.', ';', ',', '[', ']', '{', '}', '+', '=', '¹', '²', '³', '/', '\\', '?', '!', '@', '#', '$', '%', '&', 'º', 'ª', '£', '¢', '|', '—'
        );

        if ($lowerCase == true) {
            $str = strtolower(str_replace($nom, "-", trim($str)));
        } else {
            $str = str_replace($nom, "-", trim($str));
        }

        while (is_int(stripos($str, '--'))) {
            $str = trim(str_replace('--', '-', trim($str)));
        }

        if (endstr($str) == '-') {
            $str = trim(substr_replace($str, '', strlen($str) - 1, 1));
        }

        if (begstr($str) == '-') {
            $str = trim(substr_replace($str, '', 0, 1));
        }

        return $str;

    }

    public static function toUrl($str, $lowerCase = true)
    {
        $url = trim(self::removeWhiteSpaces(self::removeAccents($str, $lowerCase), $lowerCase));
        return trim($url);
    }
}

if (!function_exists('mb_ucfirst')) {
    function mb_ucfirst($str, $encoding = 'UTF-8', $lower_str_end = false)
    {
        $first_letter = mb_strtoupper(mb_substr($str, 0, 1, $encoding), $encoding);
        if ($lower_str_end) {
            $str_end = mb_strtolower(mb_substr($str, 1, mb_strlen($str, $encoding), $encoding), $encoding);
        } else {
            $str_end = mb_substr($str, 1, mb_strlen($str, $encoding), $encoding);
        }
        $str = $first_letter . $str_end;
        return $str;
    }
}