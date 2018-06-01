<?php

class OrderB extends Model
{
    public function __construct()
    {
        parent::__construct();
        return $this;
    }

    /** @var $id integer */
    public $id;
    /** @var $str string */
    public $str;
    /** @var $i1 integer */
    public $i1;
    /** @var $i2 integer */
    public $i2;
    /** @var $o1 integer */
    public $o1;
    /** @var $o2 integer */
    public $o2;
    /** @var $test_time integer */
    public $test_time;

    public static function tableName()
    {
        return "order_b";
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

    public function behaviors()
    {
        return [
            'str' => function () {
                return $this->str . ' --- Loaded';
            },
            'test_time' => function () {
                if (!$this->loaded('test_time')) {
                    return '1994';
                }
            }
        ];
    }

    public function reverseBehaviors()
    {
        return [
            'str' => function () {
                return explode(' --- ', $this->str)[0];
            },
        ];
    }

    public function rules()
    {
        return [];
    }

    public function messages()
    {
        return [];
    }

    public function labels()
    {
        return [];
    }
}