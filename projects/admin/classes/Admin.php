<?php

use Winged\Winged;
use Winged\Database\DbDict;
use Winged\Http\Session;


class Admin
{
    public static function buildPageNameUrl($page_name = false)
    {
        if($page_name) return str_replace('./', '', Winged::$parent) . $page_name . '/';
        return str_replace('./', '', Winged::$parent) . Winged::$page_surname . '/';
    }
    public static function buildUrlNoPage($custom = '')
    {
        if ($custom != '') {
            return str_replace('./', '', Winged::$parent) . $custom . '/';
        }
        return str_replace('./', '', Winged::$parent);
    }
    public static function buildGetUrl($mkey = '', $mvalue = '')
    {
        $url = Winged::$protocol . str_replace('./', '', Winged::$parent) . Winged::$page_surname . '/' . Winged::$controller_action . '/' . implode('/', Winged::$controller_params) . '?';
        $first = true;
        foreach ($_GET as $key => $value) {
            if ($first) {
                $first = false;
                if ($mkey == $key) {
                    $url .= $mkey . '=' . $mvalue;
                } else {
                    $url .= $key . '=' . $value;
                }
            } else {
                if ($mkey == $key) {
                    $url .= '&' . $mkey . '=' . $mvalue;
                } else {
                    $url .= '&' . $key . '=' . $value;
                }
            }
        }
        if ($mkey != '' && !array_key_exists($mkey, $_GET) && !empty($_GET)) {
            $url .= '&' . $mkey . '=' . $mvalue;
        } else if ($mkey != '' && !array_key_exists($mkey, $_GET) && empty($_GET)) {
            $url = $url . '?' . $mkey . '=' . $mvalue;
        }
        return $url;
    }
    public static function buildGetUrlSorting($sorting = '')
    {
        $url = self::buildGetUrl();
        if (is_int(stripos($url, $sorting))) {
            $ord = 'desc';
            if (is_int(stripos($url, $sorting . '=desc'))) {
                $ord = 'asc';
                $url = explode($sorting . '=desc', $url);
                $url = implode('', $url);
            } else if (is_int(stripos($url, $sorting . '=asc'))) {
                $url = explode($sorting . '=asc', $url);
                $url = implode('', $url);
            }
            if (endstr($url) == '?') {
                $url .= $sorting . '=' . $ord;
            } else {
                $url .= '&' . $sorting . '=' . $ord;
            }
        } else {
            if (endstr($url) == '?') {
                $url .= $sorting . '=desc';
            } else {
                $url .= '&' . $sorting . '=desc';
            }
        }
        return $url;
    }
    public static function buildSearchModel($model = null, $search = [], $and = false)
    {
        if ($model !== null) {
            if (!empty($search) && get('search')) {
                $f = true;
                foreach ($search as $val) {
                    if ($f) {
                        $f = false;
                        if($and){
                            $model->andWhere(ELOQUENT_LIKE, ['LCASE(' . $val . ')' => '%' . mb_strtolower(get('search'), 'UTF-8') . '%']);
                        }else{
                            $model->where(ELOQUENT_LIKE, ['LCASE(' . $val . ')' => '%' . mb_strtolower(get('search'), 'UTF-8') . '%']);
                        }
                    } else {
                        $model->orWhere(ELOQUENT_LIKE, ['LCASE(' . $val . ')' => '%' . mb_strtolower(get('search'), 'UTF-8') . '%']);
                    }
                }
            }
        }
    }
    public static function buildOrderModel($model = null, $orders = [], $and = false)
    {
        if ($model !== null) {
            $f = true;
            foreach ($orders as $key => $val) {
                if (($ord = array_key_exists_check($key, $_GET))) {
                    if ($ord == 'desc') {
                        $ord = ELOQUENT_DESC;
                    } else {
                        $ord = ELOQUENT_ASC;
                    }
                    if ($f) {
                        $f = false;
                        if($and){
                            $model->addOrderBy($ord, $val);
                        }else{
                            $model->orderBy($ord, $val);
                        }
                    } else {
                        $model->addOrderBy($ord, $val);
                    }
                }
            }
        }
    }
    private static function normalizeAction()
    {
        $action = Session::get('action');
        if ($action) {
            $action = explode('/', $action);
        }
        return $action;
    }
    public static function isUpdate()
    {
        if (($action = self::normalizeAction())) {
            if (in_array('update', $action)) {
                return true;
            }
        }
        return false;
    }
    public static function isInsert()
    {
        if (($action = self::normalizeAction())) {
            if (in_array('insert', $action)) {
                return true;
            }
        }
        return false;
    }
    public static function isList()
    {
        if (($action = self::normalizeAction())) {
            if (count($action) == 1) {
                $action = $action[0];
                if (is_int((int)$action) && (int)$action > 0) {
                    return true;
                }
            }
        }
        return false;
    }
}