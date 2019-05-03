<?php

namespace Winged\Database;

/**
 * have the main keywords to help in queries
 * Class Dict
 * @package Winged\Database
 */

class Dict{
    const DIFFERENT = '<>'; //of the rest, men i know it's hard to digest
    const SMALLER = '<';
    const LARGER = '>'; //remove 'r' from this word after work
    const SMALLER_OR_EQUAL = '<=';
    const LARGER_OR_EQUAL = '>=';
    const EQUAL = '=';
    const BETWEEN = 'BETWEEN'; //me and you, only love
    const DESC = 'DESC';
    const ASC = 'ASC';
    const IN = 'IN';
    const NOTIN = 'NOT IN';
    const LIKE = 'LIKE'; //you
    const NOTLIKE = 'NOT LIKE';
    const SUB_SELECT = 'SUB_SELECT';
    const ARGUMENT = 'ARGUMENT';
    const IS_NULL = 'IS_NULL';
    const IS_NOT_NULL = 'IS_NOT_NULL';
}