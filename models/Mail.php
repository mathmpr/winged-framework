<?php

class Mail extends Model
{
    public function __construct()
    {
        parent::__construct();
        return $this;
    }

    /** @var $id integer */
    public $id;

    /** @var $str integer */
    public $str;

    public static function tableName()
    {
        return "mail";
    }

    public static function primaryKeyName()
    {
        return "id";
    }

    public function primaryKey($pk = false)
    {
        if ($pk && (is_int($pk) || intval($pk) != 0)) {
            $this->id = $pk;
            return $this;
        }
        return $this->id;
    }
}