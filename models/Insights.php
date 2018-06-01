<?php

class Insights
{

    /**
     * @var $all News[]
     */
    public static $all = [];
    /**
     * @var $geted int[]
     */
    public static $geted = [];

    /**
     * @return bool|News
     */
    public static function image()
    {
        foreach (self::$all as $insight) {
            if ($insight->post_type == 1 && !in_array($insight->primaryKey(), self::$geted)) {
                self::$geted[] = $insight->primaryKey();
                return $insight;
            }
        }
        return false;
    }

    /**
     * @return bool|News
     */
    public static function text()
    {
        foreach (self::$all as $insight) {
            if ($insight->post_type == 0 && !in_array($insight->primaryKey(), self::$geted)) {
                self::$geted[] = $insight->primaryKey();
                return $insight;
            }
        }
        return false;
    }

}