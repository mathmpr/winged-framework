<?php

namespace Winged\Model;

/**
 * Interface ModelInterface
 * @package Winged\Model
 */
interface ModelInterface
{
    public static function primaryKeyName();

    public static function tableName();

    public function primaryKey();

    public function behaviors();

    public function reverseBehaviors();

    public function rules();

    public function labels();
}