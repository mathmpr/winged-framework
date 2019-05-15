<?php

namespace Winged\Model;

/**
 * Interface ModelInterface
 * @package Winged\Model
 */
interface ModelInterface
{
    /**
     * @return string
     */
    public static function primaryKeyName();

    /**
     * @return string
     */
    public static function tableName();

    /**
     * @param bool $pk
     * @return mixed
     */
    public function primaryKey($pk = false);

    /**
     * @return array
     */
    public function behaviors();

    /**
     * @return array
     */
    public function reverseBehaviors();

    /**
     * @return array
     */
    public function rules();

    /**
     * @return array
     */
    public function labels();
}