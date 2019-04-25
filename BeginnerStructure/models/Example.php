<?php

use Winged\Model\Model;

/**
 * Class Example
 */
class Example extends Model{

    /**
     * @var $id_example int
     */
    public $id_example;

    /**
     * Example constructor.
     */
    public function __construct()
    {
        parent::__construct();
        return $this;
    }

    /**
     * @return string
     */
    public static function tableName()
    {
        return "example";
    }

    /**
     * @return string
     */
    public static function primaryKeyName()
    {
        return "id_example";
    }

    /**
     * @param bool $pk
     * @return int | $this
     */
    public function primaryKey($pk = false)
    {
        if ($pk && (is_int($pk) || intval($pk) != 0)) {
            $this->id_example = $pk;
            return $this;
        }
        return $this->id_example;
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        return [];
    }

    /**
     * @return array
     */
    public function reverseBehaviors()
    {
        return [];
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [];
    }

    /**
     * @return array
     */
    public function messages()
    {
        return [];
    }

    /**
     * @return array
     */
    public function labels()
    {
        return [];
    }

}