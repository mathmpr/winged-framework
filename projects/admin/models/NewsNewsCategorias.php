<?php

use Winged\Model\Model;
use Winged\Database\DbDict;

/**
 * Class NewsCategorias
 */
class NewsNewsCategorias extends Model
{
    public function __construct()
    {
        parent::__construct();
        return $this;
    }

    /** @var $id int */
    public $id;

    /** @var $id_categoria int */
    public $id_categoria;

    /** @var $id_new int */
    public $id_new;

    /**
     * @return string
     */
    public static function tableName()
    {
        return "news_news_categorias";
    }

    /**
     * @return string
     */
    public static function primaryKeyName()
    {
        return "id";
    }

    /**
     * @param bool $pk
     *
     * @return $this|int|Model
     */
    public function primaryKey($pk = false)
    {
        if ($pk && (is_int($pk) || intval($pk) != 0)) {
            $this->id = $pk;
            return $this;
        }
        return $this->id;
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
    public function labels()
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

}