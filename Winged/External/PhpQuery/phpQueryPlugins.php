<?php
/**
 * Created by PhpStorm.
 * User: Guerra Propaganda
 * Date: 29/05/2018
 * Time: 18:10
 */

namespace Winged\External\PhpQuery;


/**
 * Plugins static namespace class.
 *
 * @author Tobiasz Cudnik <tobiasz.cudnik/gmail.com>
 * @package phpQuery
 */
class phpQueryPlugins
{
    public function __call($method, $args)
    {
        if (isset(phpQuery::$extendStaticMethods[$method])) {
            $return = call_user_func_array(
                phpQuery::$extendStaticMethods[$method],
                $args
            );
        } else if (isset(phpQuery::$pluginsStaticMethods[$method])) {
            $class = phpQuery::$pluginsStaticMethods[$method];
            $realClass = "phpQueryPlugin_$class";
            $return = call_user_func_array(
                array($realClass, $method),
                $args
            );
            return isset($return)
                ? $return
                : $this;
        } else
            throw new Exception("Method '{$method}' doesnt exist");
    }
}