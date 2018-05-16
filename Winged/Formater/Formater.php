<?php

namespace Winged\Formater;

class Formater
{
    const TO_UPER = 1;
    const TO_LOWER = 2;
    const KEEP_FORMAT = 3;
    const UC_FIRST = 4;
    const UC_FIRST_ALL = 5;

    public static function intToCurrency($int = 0, $length = 2, $left = ',', $right = '.')
    {
        $currency = (string)($int / 100);
        return number_format($currency, $length, $right, $left);
    }

    public static function removeAccents($str, $lowerCase = Formater::KEEP_FORMAT)
    {
        $conv = ['À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A', 'Å' => 'A', 'Ç' => 'C', 'È' => 'E', 'É' => 'E', 'Ê' => 'E', 'Ë' => 'E', 'Ì' => 'I', 'Í' => 'I', 'Î' => 'I', 'Ï' => 'I', 'Ñ' => 'N', 'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'O', 'Ù' => 'U', 'Ú' => 'U', 'Û' => 'U', 'Ü' => 'U', 'Ý' => 'Y', 'ß' => 's', 'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'a', 'ç' => 'c', 'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e', 'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i', 'ñ' => 'n', 'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o', 'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ü' => 'u', 'ý' => 'y', 'ÿ' => 'y', 'Ā' => 'A', 'ā' => 'a', 'Ă' => 'A', 'ă' => 'a', 'Ą' => 'A', 'ą' => 'a', 'Ć' => 'C', 'ć' => 'c', 'Ĉ' => 'C', 'ĉ' => 'c', 'Ċ' => 'C', 'ċ' => 'c', 'Č' => 'C', 'č' => 'c', 'Ď' => 'D', 'ď' => 'd', 'Đ' => 'D', 'đ' => 'd', 'Ē' => 'E', 'ē' => 'e', 'Ĕ' => 'E', 'ĕ' => 'e', 'Ė' => 'E', 'ė' => 'e', 'Ę' => 'E', 'ę' => 'e', 'Ě' => 'E', 'ě' => 'e', 'Ĝ' => 'G', 'ĝ' => 'g', 'Ğ' => 'G', 'ğ' => 'g', 'Ġ' => 'G', 'ġ' => 'g', 'Ģ' => 'G', 'ģ' => 'g', 'Ĥ' => 'H', 'ĥ' => 'h', 'Ħ' => 'H', 'ħ' => 'h', 'Ĩ' => 'I', 'ĩ' => 'i', 'Ī' => 'I', 'ī' => 'i', 'Ĭ' => 'I', 'ĭ' => 'i', 'Į' => 'I', 'į' => 'i', 'İ' => 'I', 'ı' => 'i', 'Ĳ' => 'IJ', 'ĳ' => 'ij', 'Ĵ' => 'J', 'ĵ' => 'j', 'Ķ' => 'K', 'ķ' => 'k', 'ĸ' => 'k', 'Ĺ' => 'L', 'ĺ' => 'l', 'Ļ' => 'L', 'ļ' => 'l', 'Ľ' => 'L', 'ľ' => 'l', 'Ŀ' => 'L', 'ŀ' => 'l', 'Ł' => 'L', 'ł' => 'l', 'Ń' => 'N', 'ń' => 'n', 'Ņ' => 'N', 'ņ' => 'n', 'Ň' => 'N', 'ň' => 'n', 'ŉ' => 'N', 'Ŋ' => 'n', 'ŋ' => 'N', 'Ō' => 'O', 'ō' => 'o', 'Ŏ' => 'O', 'ŏ' => 'o', 'Ő' => 'O', 'ő' => 'o', 'Œ' => 'OE', 'œ' => 'oe', 'Ŕ' => 'R', 'ŕ' => 'r', 'Ŗ' => 'R', 'ŗ' => 'r', 'Ř' => 'R', 'ř' => 'r', 'Ś' => 'S', 'ś' => 's', 'Ŝ' => 'S', 'ŝ' => 's', 'Ş' => 'S', 'ş' => 's', 'Š' => 'S', 'š' => 's', 'Ţ' => 'T', 'ţ' => 't', 'Ť' => 'T', 'ť' => 't', 'Ŧ' => 'T', 'ŧ' => 't', 'Ũ' => 'U', 'ũ' => 'u', 'Ū' => 'U', 'ū' => 'u', 'Ŭ' => 'U', 'ŭ' => 'u', 'Ů' => 'U', 'ů' => 'u', 'Ű' => 'U', 'ű' => 'u', 'Ų' => 'U', 'ų' => 'u', 'Ŵ' => 'W', 'ŵ' => 'w', 'Ŷ' => 'Y', 'ŷ' => 'y', 'Ÿ' => 'Y', 'Ź' => 'Z', 'ź' => 'z', 'Ż' => 'Z', 'ż' => 'z', 'Ž' => 'Z', 'ž' => 'z', 'ſ' => 's'];

        $string = strtr($str, $conv);
        switch ($lowerCase) {
            case Formater::TO_UPER:
                return mb_strtoupper($string, 'UTF-8');
                break;
            case Formater::TO_LOWER:
                return mb_strtolower($string, 'UTF-8');
                break;
            case Formater::KEEP_FORMAT:
                return $string;
                break;
            case Formater::UC_FIRST:
                return mb_ucfirst($string);
                break;
            case Formater::UC_FIRST_ALL:
                $exp = explode(' ', $string);
                foreach ($exp as $key => $e) {
                    $exp[$key] = mb_ucfirst($e);
                }
                return join(' ', $exp);
                break;
            default:
                return $string;
                break;
        }
    }


    public static function removeSymbols($str, $lowerCase = Formater::KEEP_FORMAT)
    {

        $perms = ['À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'Ç', 'È', 'É', 'Ê', 'Ë', 'Ì', 'Í', 'Î', 'Ï', 'Ñ', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ù', 'Ú', 'Û', 'Ü', 'Ý', 'ß', 'à', 'á', 'â', 'ã', 'ä', 'å', 'ç', 'è', 'é', 'ê', 'ë', 'ì', 'í', 'î', 'ï', 'ñ', 'ò', 'ó', 'ô', 'õ', 'ö', 'ù', 'ú', 'û', 'ü', 'ý', 'ÿ', 'Ā', 'ā', 'Ă', 'ă', 'Ą', 'ą', 'Ć', 'ć', 'Ĉ', 'ĉ', 'Ċ', 'ċ', 'Č', 'č', 'Ď', 'ď', 'Đ', 'đ', 'Ē', 'ē', 'Ĕ', 'ĕ', 'Ė', 'ė', 'Ę', 'ę', 'Ě', 'ě', 'Ĝ', 'ĝ', 'Ğ', 'ğ', 'Ġ', 'ġ', 'Ģ', 'ģ', 'Ĥ', 'ĥ', 'Ħ', 'ħ', 'Ĩ', 'ĩ', 'Ī', 'ī', 'Ĭ', 'ĭ', 'Į', 'į', 'İ', 'ı', 'Ĳ', 'ĳ', 'Ĵ', 'ĵ', 'Ķ', 'ķ', 'ĸ', 'Ĺ', 'ĺ', 'Ļ', 'ļ', 'Ľ', 'ľ', 'Ŀ', 'ŀ', 'Ł', 'ł', 'Ń', 'ń', 'Ņ', 'ņ', 'Ň', 'ň', 'ŉ', 'Ŋ', 'ŋ', 'Ō', 'ō', 'Ŏ', 'ŏ', 'Ő', 'ő', 'Œ', 'œ', 'Ŕ', 'ŕ', 'Ŗ', 'ŗ', 'Ř', 'ř', 'Ś', 'ś', 'Ŝ', 'ŝ', 'Ş', 'ş', 'Š', 'š', 'Ţ', 'ţ', 'Ť', 'ť', 'Ŧ', 'ŧ', 'Ũ', 'ũ', 'Ū', 'ū', 'Ŭ', 'ŭ', 'Ů', 'ů', 'Ű', 'ű', 'Ų', 'ų', 'Ŵ', 'ŵ', 'Ŷ', 'ŷ', 'Ÿ', 'Ź', 'ź', 'Ż', 'ż', 'Ž', 'ž', 'ſ', 'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', ' ', '1', '2', '3', '4', '5', '6', '7', '8', '9', '0', '-'];

        $string = preg_replace('/[^\w\s\-]+/u', '', $str);
        $regex = sprintf('/[^%s]/u', preg_quote(join($perms), '/'));
        $string = preg_replace($regex, '', $string);

        while (is_int(strpos($string, '--'))) {
            $string = str_replace('--', '-', $string);
        }

        switch ($lowerCase) {
            case Formater::TO_UPER:
                return mb_strtoupper($string, 'UTF-8');
                break;
            case Formater::TO_LOWER:
                return mb_strtolower($string, 'UTF-8');
                break;
            case Formater::KEEP_FORMAT:
                return $string;
                break;
            case Formater::UC_FIRST:
                return mb_ucfirst($string);
                break;
            case Formater::UC_FIRST_ALL:
                $exp = explode(' ', $string);
                foreach ($exp as $key => $e) {
                    $exp[$key] = mb_ucfirst($e);
                }
                return join(' ', $exp);
                break;
            default:
                return $string;
                break;
        }
    }

    public static function removeSpaces($str, $lowerCase = Formater::KEEP_FORMAT)
    {
        while (is_int(strpos($str, '  '))) {
            $str = str_replace('  ', ' ', $str);
        }
        $string = str_replace(' ', '-', trim($str));

        while (is_int(strpos($string, '--'))) {
            $string = str_replace('--', '-', $string);
        }

        switch ($lowerCase) {
            case Formater::TO_UPER:
                return mb_strtoupper($string, 'UTF-8');
                break;
            case Formater::TO_LOWER:
                return mb_strtolower($string, 'UTF-8');
                break;
            case Formater::KEEP_FORMAT:
                return $string;
                break;
            case Formater::UC_FIRST:
                return mb_ucfirst($string);
                break;
            case Formater::UC_FIRST_ALL:
                $exp = explode(' ', $string);
                foreach ($exp as $key => $e) {
                    $exp[$key] = mb_ucfirst($e);
                }
                return join(' ', $exp);
                break;
            default:
                return $string;
                break;
        }
    }

    public static function toUrl($str, $lowerCase = Formater::TO_LOWER)
    {
        $url = self::removeSpaces(self::removeSymbols(self::removeAccents($str, $lowerCase), $lowerCase), $lowerCase);
        return $url;
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