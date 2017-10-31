<?php

class CoreValidator
{

    public function cpf($cpf = null)
    {
        if (empty($cpf)) {
            return false;
        }
        $cpf = preg_replace('[^0-9]', '', $cpf);
        $cpf = str_pad($cpf, 11, '0', STR_PAD_LEFT);
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

    public function cnpj($cnpj = null)
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

    public function lengthLarger($entry = null, $comp = false)
    {
        if (empty($entry) || empty($comp)) {
            return false;
        }
        if (strlen($entry) > $comp) {
            return true;
        }
        return false;
    }

    public function lengthLargerOrEqual($entry = null, $comp = false)
    {
        if (empty($entry) || empty($comp)) {
            return false;
        }
        if (strlen($entry) >= $comp) {
            return true;
        }
        return false;
    }

    public function lengthSmaller($entry = null, $comp = false)
    {
        if (empty($entry) || empty($comp)) {
            return false;
        }
        if (strlen($entry) < $comp) {
            return true;
        }
        return false;
    }

    public function lengthSmallerOrEqual($entry = null, $comp = false)
    {
        if (empty($entry) || empty($comp)) {
            return false;
        }
        if (strlen($entry) <= $comp) {
            return true;
        }
        return false;
    }

    public function lengthEqual($entry = null, $comp = false)
    {
        if (empty($entry) || empty($comp)) {
            return false;
        }
        if (strlen($entry) === $comp) {
            return true;
        }
        return false;
    }

    public function lengthBetween($entry = null, $smaller = false, $larger = false)
    {
        if (empty($entry) || empty($smaller) || empty($larger)) {
            return false;
        }
        if (strlen($entry) > $smaller && strlen($entry) < $larger) {
            return true;
        }
        return false;
    }

    public function lengthBetweenOrEqual($entry = null, $smaller = false, $larger = false)
    {
        if (empty($entry) || empty($smaller) || empty($larger)) {
            return false;
        }
        if (strlen($entry) >= $smaller && strlen($entry) <= $larger) {
            return true;
        }
        return false;
    }

    public function larger($entry = null, $comp = false)
    {
        if (empty($entry) || empty($comp)) {
            return false;
        }
        if ($entry > $comp) {
            return true;
        }
        return false;
    }

    public function largerOrEqual($entry = null, $comp = false)
    {
        if (empty($entry) || empty($comp)) {
            return false;
        }
        if ($entry >= $comp) {
            return true;
        }
        return false;
    }

    public function smaller($entry = null, $comp = false)
    {
        if (empty($entry) || empty($comp)) {
            return false;
        }
        if ($entry < $comp) {
            return true;
        }
        return false;
    }

    public function smallerOrEqual($entry = null, $comp = false)
    {
        if (empty($entry) || empty($comp)) {
            return false;
        }
        if ($entry <= $comp) {
            return true;
        }
        return false;
    }

    public function equals($entry = null, $comp = false)
    {
        if (empty($entry) || empty($comp)) {
            return false;
        }
        if ($entry === $comp) {
            return true;
        }
        return false;
    }

    public function different($entry = null, $comp = false)
    {
        if (empty($entry) || empty($comp)) {
            return false;
        }
        if ($entry !== $comp) {
            return true;
        }
        return false;
    }

    public function between($entry = null, $smaller = false, $larger = false)
    {
        if (empty($entry) || empty($smaller) || empty($larger)) {
            return false;
        }
        if ($entry > $smaller && $entry < $larger) {
            return true;
        }
        return false;
    }


    public function betweenEqual($entry = null, $smaller = false, $larger = false)
    {
        if (empty($entry) || empty($smaller) || empty($larger)) {
            return false;
        }
        if ($entry >= $smaller && $entry <= $larger) {
            return true;
        }
        return false;
    }

    public function dateGreater($entry = null, $comp = false)
    {
        if (empty($entry) || empty($comp)) {
            return false;
        }
        if ((new CoreDate($entry))->greater($comp)) {
            return true;
        }
        return false;
    }

    public function dateSmaller($entry = null, $comp = false)
    {
        if (empty($entry) || empty($comp)) {
            return false;
        }
        if ((new CoreDate($entry))->smaller($comp)) {
            return true;
        }
        return false;
    }

    public function dateBetween($entry = null, $smaller = false, $larger = false)
    {
        if (empty($entry) || empty($comp)) {
            return false;
        }
        if ((new CoreDate($entry))->smaller($larger) && (new CoreDate($entry))->greater($smaller)) {
            return true;
        }
        return false;
    }

}