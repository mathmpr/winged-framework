<?php

namespace Winged\Utils;

use Winged\Formater\Formater;

class Chord implements \ArrayAccess, \Iterator
{
    private $chord = '';
    private $key = 0;

    public function next()
    {
        $this->key++;
    }

    public function prev()
    {
        $this->key--;
    }

    public function current()
    {
        return $this->chord[$this->key];
    }

    public function valid()
    {
        return isset($this->chord[$this->key]);
    }

    public function key()
    {
        return $this->key;
    }

    public function rewind()
    {
        $this->key = 0;
    }

    public function offsetSet($offset, $value)
    {
        if ($this->offsetExists($offset)) {
            $begin = substr($this->chord, 0, $offset);
            if ($offset === $this->length() - 1) {
                $begin = substr($this->chord, 0, $offset);
                $this->chord = $begin . $value;
            } else {
                $end = substr($this->chord, $offset + 1, $this->length());
                $this->chord = $begin . $value . $end;
            }
        }
    }

    public function offsetUnset($offset)
    {
        if ($this->offsetExists($offset)) {
            $begin = substr($this->chord, 0, $offset);
            if ($offset === $this->length() - 1) {
                $begin = substr($this->chord, 0, $offset);
                $this->chord = $begin;
            } else {
                $end = substr($this->chord, $offset + 1, $this->length());
                $this->chord = $begin . $end;
            }
        }
    }

    public function offsetGet($offset)
    {
        if ($this->offsetExists($offset)) {
            return $this->chord[$offset];
        }
        return false;
    }

    public function offsetExists($offset)
    {
        return isset($this->chord[$offset]);
    }

    public function __toString()
    {
        return $this->chord;
    }

    public static function factory($string = '')
    {
        return new Chord($string);
    }

    public function __construct($string = '')
    {
        return $this->set($string);
    }

    /**
     * @param string $string
     * @return $this
     */
    public function set($string = '')
    {
        if (is_string($string)) {
            $this->chord = $string;
        } else if (is_scalar($string)) {
            $this->chord = (string)$string;
        } else {
            $this->chord = '';
        }
        return $this;
    }

    /**
     * @return string
     */
    public function get()
    {
        return $this->chord;
    }

    /**
     * @return int
     */
    public function length()
    {
        return strlen($this->chord);
    }

    /**
     * @param string $string
     * @return bool
     */
    public function endsWith($string = '')
    {
        $len = strlen($string);
        if ($len == 0) {
            return true;
        }
        return (substr($this->chord, -$len) === $string);
    }

    /**
     * @param string $string
     * @return bool
     */
    public function startsWith($string = '')
    {
        $len = strlen($string);
        return (substr($this->chord, 0, $len) === $string);
    }

    /**
     * @param string $string
     * @param string $replace
     * @return string
     */
    public function startReplace($string = '', $replace = '')
    {
        if ($this->startsWith($string)) {
            $len = strlen($string);
            $this->chord = $replace . substr($this->chord, $len, ($this->length() - 1));
        }
        return $this->get();
    }

    /**
     * @param string $string
     * @param string $replace
     * @return string
     */
    public function endReplace($string = '', $replace = '')
    {
        if ($this->endsWith($string)) {
            $len = strlen($string);
            $this->chord = substr($this->chord, 0, ($this->length() - $len)) . $replace;
        }
        return $this->get();
    }

    /**
     * @return $this
     */
    public function rtrim()
    {
        rtrim($this->chord);
        return $this;
    }

    /**
     * @return $this
     */
    public function ltrim()
    {
        ltrim($this->chord);
        return $this;
    }

    /**
     * @return $this
     */
    public function trim()
    {
        trim($this->chord);
        return $this;
    }

    /**
     * @param string $glue
     * @param array|Vector $vector
     * @return $this
     */
    public function join($glue = '', $vector = [])
    {
        if (is_object($vector) && get_class($vector) === 'Winged\Utils\Vector') {
            $this->chord = join($glue, $vector->getStringsInside()->getVector());
        } else if (is_array($vector)) {
            $this->chord = join($glue, $vector);
        }
        return $this;
    }

    /**
     * @param string $glue
     * @param array $vector
     * @return Chord
     */
    public function implode($glue = '', $vector = [])
    {
        return $this->join($glue, $vector);
    }

    /**
     * @param $delimiter
     * @param $limit
     * @return Vector
     */
    public function explode($delimiter, $limit = null)
    {
        if ($limit) {
            return Vector::factory(explode($delimiter, $this->chord, $limit));
        }
        return Vector::factory(explode($delimiter, $this->chord));
    }

    /**
     * @param int $length
     * @return array[]|false|string[]
     */
    public function split($length = -1)
    {
        return preg_split("//u", $this->chord, $length, PREG_SPLIT_NO_EMPTY);
    }

    /**
     * @param int $length
     * @param string $end
     * @return string
     */
    public function chunk($length = 76, $end = '')
    {
        $tmp = array_chunk(
            preg_split("//u", $this->chord, -1, PREG_SPLIT_NO_EMPTY), $length);
        $string = "";
        foreach ($tmp as $chunked) {
            $string .= join("", $chunked) . $end;
        }
        return $string;
    }

    /**
     * @param string|array $search
     * @param string|array $replace
     */
    public function replace($search = '', $replace = '')
    {
        $this->chord = str_replace($search, $replace, $this->chord);
    }

    /**
     * @return Vector
     */
    public function countChars()
    {
        return Vector::factory($this->split())->countValues();
    }

    /**
     * @return Vector
     * @throws \ReflectionException
     */
    public function countWords()
    {
        return $this->explode(' ')->walk(function ($value) {
            return Formater::removeSymbols($value);
        })->countValues();
    }

    public function countNewLines()
    {
        $copy = DeepClone::factory($this)->copy();
        $copy->replace(["\n\r", "\r", "\n"], "\n");
        return substr_count($copy->chord, "\n");
    }

    public function insensitiveExists($search = '', $offset = 0)
    {
        return is_int(stripos($this->chord, $search, $offset));
    }

    public function sensitiveExists($search = '', $offset = 0)
    {
        return is_int(strpos($this->chord, $search, $offset));
    }

    public function insensitiveSearch($search = '', $offset = 0)
    {
        return stripos($this->chord, $search, $offset);
    }

    public function sensitiveSearch($search = '', $offset = 0)
    {
        return strpos($this->chord, $search, $offset);
    }

    /**
     * @param int $start
     * @param bool $length
     * @return bool|string
     */
    public function substr($start = 0, $length = false)
    {
        if ($length) {
            return substr($this->chord, $start, $length);
        }
        return substr($this->chord, $start);
    }

    /**
     * return crop string if string length is larger or equal length
     * if nocut is true, this method does not return the cut string in the exact size, the cut string will contain the last whole word even if it exceeds the length passed as an argument.
     * if add is a string, the end of the returned string will contain the same
     * @param int $start
     * @param int $length
     * @param bool $nocut
     * @param bool $ads
     * @return Chord
     */
    public function substrIfNeed($start = 0, $length = 50, $nocut = false, $ads = false)
    {
        if (is_int($length) && is_int($start)) {
            if ($length < $this->length() && $start < $this->length() && $this->length() >= $length) {
                if ($nocut) {
                    $perms = [' ', ',', '.', '-', '_', '(', ')', '[', ']', '{', '}'];
                    if (!in_array($this->chord[$length], $perms)) {
                        $copy = DeepClone::factory($this)->copy();
                        $copy->replace($perms, ' ');
                        if ($copy->insensitiveExists(' ', $length)) {
                            $length = $copy->sensitiveSearch(' ', $length);
                        }
                    }
                }
                return Chord::factory(substr($this->chord, $start, $length) . $ads);
            }
        }
        return Chord::factory($this->chord);
    }
}
