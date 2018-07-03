<?php
namespace Winged\Validator;
use Winged\Date\Date;
use Winged\Formater\Formater;

/**
 * Class validation for forms, combine this class with the rules method of the models
 * Class Validator
 * @package Winged\Validator
 */
class Validator
{

    /**
     * Tests whether the input is a valid CPF
     * @param null $cpf
     * @return bool
     */
    public static function cpf($cpf = null)
    {
        if (empty($cpf)) {
            return false;
        }
        $cpf = str_replace('-', '', Formater::removeSymbols($cpf));
        if (strlen($cpf) != 11) {
            return false;
        } else if ($cpf == '00000000000' ||
            $cpf == '11111111111' ||
            $cpf == '22222222222' ||
            $cpf == '33333333333' ||
            $cpf == '44444444444' ||
            $cpf == '55555555555' ||
            $cpf == '66666666666' ||
            $cpf == '77777777777' ||
            $cpf == '88888888888' ||
            $cpf == '99999999999'
        ) {
            return false;
        } else {
            for ($t = 9; $t < 11; $t++) {
                for ($d = 0, $c = 0; $c < $t; $c++) {
                    $d += $cpf{$c} * (($t + 1) - $c);
                }
                $d = ((10 * $d) % 11) % 10;
                if ($cpf{$c} != $d) {
                    return false;
                }
            }
            return true;
        }
    }

    /**
     * Tests whether the input is a valid CNPJ
     * @param null $cnpj
     * @return bool
     */
    public static function cnpj($cnpj = null)
    {
        $cnpj = preg_replace('/[^0-9]/', '', (string)$cnpj);
        if (strlen($cnpj) != 14)
            return false;
        for ($i = 0, $j = 5, $soma = 0; $i < 12; $i++) {
            $soma += $cnpj{$i} * $j;
            $j = ($j == 2) ? 9 : $j - 1;
        }
        $resto = $soma % 11;
        if ($cnpj{12} != ($resto < 2 ? 0 : 11 - $resto))
            return false;
        for ($i = 0, $j = 6, $soma = 0; $i < 13; $i++) {
            $soma += $cnpj{$i} * $j;
            $j = ($j == 2) ? 9 : $j - 1;
        }
        $resto = $soma % 11;
        return $cnpj{13} == ($resto < 2 ? 0 : 11 - $resto);
    }

    /**
     * Tests whether the input length is greater than the comparison parameter
     * @param null $entry
     * @param bool $comp
     * @return bool
     */
    public static function lengthLarger($entry = null, $comp = false)
    {
        if (empty($entry) || empty($comp)) {
            return false;
        }
        if (strlen($entry) > $comp) {
            return true;
        }
        return false;
    }

    /**
     * Tests whether the input length is greater or equal than the comparison parameter
     * @param null $entry
     * @param bool $comp
     * @return bool
     */
    public static function lengthLargerOrEqual($entry = null, $comp = false)
    {
        if (empty($entry) || empty($comp)) {
            return false;
        }
        if (strlen($entry) >= $comp) {
            return true;
        }
        return false;
    }

    /**
     * Tests whether the input length is smaller than the comparison parameter
     * @param null $entry
     * @param bool $comp
     * @return bool
     */
    public static function lengthSmaller($entry = null, $comp = false)
    {
        if (empty($entry) || empty($comp)) {
            return false;
        }
        if (strlen($entry) < $comp) {
            return true;
        }
        return false;
    }

    /**
     * Tests whether the input length is smaller or equal than the comparison parameter
     * @param null $entry
     * @param bool $comp
     * @return bool
     */
    public static function lengthSmallerOrEqual($entry = null, $comp = false)
    {
        if (empty($entry) || empty($comp)) {
            return false;
        }
        if (strlen($entry) <= $comp) {
            return true;
        }
        return false;
    }

    /**
     * Tests whether the input length is greater than the comparison parameter
     * @param null $entry
     * @param bool $comp
     * @return bool
     */
    public static function lengthEqual($entry = null, $comp = false)
    {
        if (empty($entry) || empty($comp)) {
            return false;
        }
        if (strlen($entry) === $comp) {
            return true;
        }
        return false;
    }

    /**
     * Tests whether the input length is between than the comparison parameters
     * @param null $entry
     * @param bool $smaller
     * @param bool $larger
     * @return bool
     */
    public static function lengthBetween($entry = null, $smaller = false, $larger = false)
    {
        if (empty($entry) || empty($smaller) || empty($larger)) {
            return false;
        }
        if (strlen($entry) > $smaller && strlen($entry) < $larger) {
            return true;
        }
        return false;
    }

    /**
     * Tests whether the input length is between or equal than the comparison parameters
     * @param null $entry
     * @param bool $smaller
     * @param bool $larger
     * @return bool
     */
    public static function lengthBetweenOrEqual($entry = null, $smaller = false, $larger = false)
    {
        if (empty($entry) || empty($smaller) || empty($larger)) {
            return false;
        }
        if (strlen($entry) >= $smaller && strlen($entry) <= $larger) {
            return true;
        }
        return false;
    }

    /**
     * Tests whether the input is greater than the comparison parameter
     * @param null $entry
     * @param bool $comp
     * @return bool
     */
    public static function larger($entry = null, $comp = false)
    {
        if (empty($entry) || empty($comp)) {
            return false;
        }
        if ($entry > $comp) {
            return true;
        }
        return false;
    }

    /**
     * Tests whether the input is greater than the comparison parameter or equal
     * @param null $entry
     * @param bool $comp
     * @return bool
     */
    public static function largerOrEqual($entry = null, $comp = false)
    {
        if (empty($entry) || empty($comp)) {
            return false;
        }
        if ($entry >= $comp) {
            return true;
        }
        return false;
    }

    /**
     * Tests whether the input is smaller than the comparison parameter
     * @param null $entry
     * @param bool $comp
     * @return bool
     */
    public static function smaller($entry = null, $comp = false)
    {
        if (empty($entry) || empty($comp)) {
            return false;
        }
        if ($entry < $comp) {
            return true;
        }
        return false;
    }

    /**
     * Tests whether the input is smaller than the comparison parameter or equal
     * @param null $entry
     * @param bool $comp
     * @return bool
     */
    public static function smallerOrEqual($entry = null, $comp = false)
    {
        if (empty($entry) || empty($comp)) {
            return false;
        }
        if ($entry <= $comp) {
            return true;
        }
        return false;
    }

    /**
     * Tests if entry is equal of comparison parameter
     * @param null $entry
     * @param bool $comp
     * @return bool
     */
    public static function equals($entry = null, $comp = false)
    {
        if (empty($entry) || empty($comp)) {
            return false;
        }
        if ($entry === $comp) {
            return true;
        }
        return false;
    }

    /**
     * Tests if entry is different of comparison parameter
     * @param null $entry
     * @param bool $comp
     * @return bool
     */
    public static function different($entry = null, $comp = false)
    {
        if (empty($entry) || empty($comp)) {
            return false;
        }
        if ($entry !== $comp) {
            return true;
        }
        return false;
    }

    /**
     * Tests whether the entry is smaller the date passed as an argument or equal
     * @param null $entry
     * @param bool $smaller
     * @param bool $larger
     * @return bool
     */
    public static function between($entry = null, $smaller = false, $larger = false)
    {
        if (empty($entry) || empty($smaller) || empty($larger)) {
            return false;
        }
        if ($entry > $smaller && $entry < $larger) {
            return true;
        }
        return false;
    }

    /**
     * Tests whether the date of entry is smaller the date passed as an argument or equal
     * @param null $entry
     * @param bool $smaller
     * @param bool $larger
     * @return bool
     */
    public static function betweenEqual($entry = null, $smaller = false, $larger = false)
    {
        if (empty($entry) || empty($smaller) || empty($larger)) {
            return false;
        }
        if ($entry >= $smaller && $entry <= $larger) {
            return true;
        }
        return false;
    }

    /**
     * Tests whether the date of entry is greater the date passed as an argument
     * @param null $entry
     * @param bool $comp
     * @return bool
     */
    public static function dateGreater($entry = null, $comp = false)
    {
        if (empty($entry) || empty($comp)) {
            return false;
        }
        if ((new Date($entry))->greater($comp)) {
            return true;
        }
        return false;
    }

    /**
     * Tests whether the date of entry is smaller the date passed as an argument
     * @param null $entry
     * @param bool $comp
     * @return bool
     */
    public static function dateSmaller($entry = null, $comp = false)
    {
        if (empty($entry) || empty($comp)) {
            return false;
        }
        if ((new Date($entry))->smaller($comp)) {
            return true;
        }
        return false;
    }

    /**
     * Tests whether the date of entry is between the dates passed as an argument
     * @param null $entry
     * @param bool $begin
     * @param bool $final
     * @return bool
     */
    public static function dateBetween($entry = null, $begin = false, $final = false)
    {
        if (empty($entry) || empty($begin) || empty($final)) {
            return false;
        }
        if ((new Date($entry))->smaller($final) && (new Date($entry))->greater($begin)) {
            return true;
        }
        return false;
    }

    /**
     * Test if argument is a valid URL
     * @param $url string
     * @return bool
     */
    public static function isUrl($url){
        if (filter_var($url, FILTER_VALIDATE_URL) === true) {
            return true;
        }
        return false;
    }

}