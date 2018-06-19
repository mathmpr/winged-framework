<?php

namespace Winged\Rewrite;

use Winged\Winged;
use Winged\Error\Error;
use Winged\WingedConfig;
use Winged\Buffer\Buffer;
use Winged\Utils\WingedLib;

/**
 * Class Rewrite
 * @package Winged\Rewrite
 */
class Rewrite
{

    private $routes = [];
    private $actions = [];
    public $find_vect = false;
    public $rewrite_reset = false;

    public function rewrite_page()
    {
        if (!$this->reset()) {

            $page = Winged::$page;
            $page_key = Winged::$key;
            $parent = Winged::$parent;
            $routed_file = Winged::$routed_file;
            $route_dir = Winged::$route_dir;

            if (file_exists($route_dir) && is_directory($route_dir)) {

                if (file_exists($routed_file)) {

                    include_once($routed_file);

                    if (!Winged::$restful) {
                        $vect = $this->find_key($page_key);


                        $find = '';
                        $x = 0;
                        $array = [];
                        if ($vect) {
                            if (array_key_exists('index', $vect)) {
                                $find = $vect["index"];
                            }
                            if (count7(Winged::$params) > 0 && gettype(Winged::$params) == "array") {
                                foreach ($vect as $key => $value) {
                                    if (is_int($key)) {
                                        $param = Winged::$params[$x];
                                        if (gettype($value) == "object" && get_class($value) == "Parameter") {
                                            if (array_key_exists($value->name, $array)) {
                                                Error::push(128, "the nickname for the parameters was doubled. duplicate nickname '" . $value->name . "'", __FILE__, __LINE__);
                                            } else {
                                                $array[$value->name] = $param;
                                            }
                                            $x++;
                                            if ($x >= count7(Winged::$params)) {
                                                break;
                                            }
                                        } else {
                                            Error::push(128, "the value found during the looping is not a type 'Parameter' object.", __FILE__, __LINE__);
                                        }
                                    }
                                }
                            }

                            Winged::$oparams = Winged::$params;
                            if (count7($array) > 0) {
                                Winged::$params = $array;
                            }


                            if (count7($array) > 0) {
                                if ($vect[count7($array) - 1]->index) {
                                    $find = $vect[count7($array) - 1]->index;
                                }
                            }

                            Winged::$geted_file = $find;

                            if (file_exists($find) && !is_directory($find) && !array_key_exists('controller', $vect)) {
                                if (array_key_exists("extra", $vect)) {
                                    $extra = $vect["extra"];
                                    if (array_key_exists("loads", $extra)) {
                                        foreach ($extra["loads"] as $key => $load) {
                                            if (file_exists($load) && !is_directory($load)) {
                                                include_once $load;
                                            } else {
                                                Error::_die(__CLASS__, "include_once fails for expression {$load}", __FILE__, __LINE__);
                                            }
                                        }
                                    }
                                    if (array_key_exists("vars", $extra)) {
                                        foreach ($extra["vars"] as $key => $var) {
                                            ${$key} = $var;
                                        }
                                    }
                                }
                                include_once $find;
                                if (Error::exists()) {
                                    Error::display(__LINE__, __FILE__);
                                }
                            } else {
                                if (array_key_exists('controller', $vect)) {
                                    if (check_all_keys(['name', 'action'], $vect['controller'])) {
                                        $this->find_vect = $vect['controller'];
                                    }
                                }

                                $have_action = false;

                                if ($this->find_vect) {
                                    $have_action = Winged::$controller->allExists($this->find_vect);
                                }

                                if (WingedConfig::$FORCE_NOTFOUND && !$have_action) {
                                    $this->setDefault404();
                                } else {
                                    Winged::$controller_action = $this->find_vect['action'];
                                    Winged::$controller_page = $this->find_vect['name'];
                                    Winged::$controller->find();
                                }
                            }
                        } else {
                            if (WingedConfig::$FORCE_NOTFOUND) {
                                $this->setDefault404();
                            } else {
                                Error::_die("no route was found in the file " . $routed_file, __CLASS__ . ' : ' . __LINE__, __FILE__, __LINE__);
                            }
                        }
                    } else {
                        Winged::$restful_obj->restful_page();
                    }
                } else {
                    Error::_die("file '" . $page . ".php' not fount in '" . $route_dir . "'", __CLASS__ . ' : ' . __LINE__, __FILE__, __LINE__);
                }
            } else {
                Error::_die("folder 'routes' not fount in '" . $parent . "'", __CLASS__ . ' : ' . __LINE__, __FILE__, __LINE__);
            }
        }
    }

    public function setDefault404()
    {
        if (file_exists(Winged::$notfound) && !is_directory(Winged::$notfound)) {
            Buffer::kill();
            include_once Winged::$notfound;
            Buffer::flush();
            if (Error::exists()) {
                Error::display(__LINE__, __FILE__);
            }
        } else {
            Error::_die("the error file 404 was not found in '" . Winged::$notfound . "'. this is very very bad", __CLASS__ . ' : ' . __LINE__, __FILE__, __LINE__);
        }
    }


    private function reset()
    {
        $parent = Winged::$parent;
        $outher_conf = $parent . "config/config.php";
        if (file_exists($outher_conf) && !is_directory($outher_conf) && !$this->rewrite_reset) {
            include_once $outher_conf;
            mb_internal_encoding(WingedConfig::$INTERNAL_ENCODING);
            mb_http_output(WingedConfig::$OUTPUT_ENCODING);
            Error::clear();
            $this->rewrite_reset = true;
            Winged::start();
            return true;
        }
        return false;
    }

    public function find_key($okey)
    {
        $okey = trim($okey);
        $found = false;
        $f_num = 0;
        $stack = [];
        foreach ($this->routes as $key => $value) {
            $key = trim($key);
            $exp = explode("/", $key);
            $pos = stripos($key, $okey);
            $countExp = count7($exp);
            if ($countExp >= 3) {
                if (is_int($pos) && !$found) {
                    $replace = str_replace($okey, "", $key);
                    $found = ["key" => $key, "value" => $value, "uri_comp" => WingedLib::dotslash($replace)];
                    $point = WingedLib::dotslash($replace);
                    if ($point == '.') {
                        $value["uri_comp"] = false;
                    } else {
                        $value["uri_comp"] = WingedLib::dotslash(WingedLib::dotslash($replace));
                    }
                    $stack[$key] = $value;
                    $f_num++;
                } else if (is_int($pos)) {
                    $replace = str_replace($okey, "", $key);
                    $value["uri_comp"] = WingedLib::dotslash($replace);
                    $stack[$key] = $value;
                    $f_num++;
                }
            }
        }

        if ($f_num > 1) {
            return $this->corrert_route($stack);
        } else if ($f_num == 0) {
            return false;
        } else {
            $this->actions = [];
            $newparams = [];
            $exp = false;
            if ($found["uri_comp"] != "" && $found["uri_comp"] != ".") {
                $exp = explode("/", $found["uri_comp"]);
            }
            $params = Winged::$params;
            if (is_bool($params)) {
                $params = [];
            }
            $countParams = is_array($params) ? count7($params) : 0;
            $countExp = is_array($exp) ? count7($exp) : 1;
            if ($countParams == $countExp || Winged::$is_standard || $exp == false) {
                $init = true;
                $countExp = is_array($exp) ? count7($exp) : 0;
                for ($x = 0; $x < $countExp; $x++) {
                    $math = explode(":", $exp[$x]);
                    $is_action = false;
                    if ($math && (count7($math) == 2 && trim($math[0]) == "action")) {
                        $preg = trim($math[1]);
                        $is_action = true;
                    } else {
                        $preg = $exp[$x];
                    }
                    if (array_key_exists($x, $params)) {
                        if (!preg_match("/^" . $preg . "$/", $params[$x]) && $preg != 'no-rule') {
                            Error::_die(__CLASS__, "{$params[$x]} was reproved by rule {$preg}", __FILE__, __LINE__);
                            $init = false;
                            break;
                        }
                    }
                    if ($is_action) {
                        $this->actions[] = $preg;
                        $search = array_search($preg, $params);
                        if (is_int($search)) {
                            unset($params[$search]);
                        }
                    }
                }

                if (is_array($params)) {
                    foreach ($params as $key => $now) {
                        $newparams[] = $now;
                    }
                }

                if ($init) {
                    Winged::$params = $newparams;
                    return $found["value"];
                }
            }
            Error::push(128, "the URL passed as reference for call was recognized by a route file, but the comparison of the parameters count found in the URL and the parameters count configured for the route are different. Make a new call using the correct number of parameters in the URL.", __FILE__, __LINE__);
            return false;
        }

    }

    private function corrert_route($probable_routes)
    {
        foreach ($probable_routes as $key => $value) {
            $this->actions = [];
            $exp = [];
            if ($value["uri_comp"] != "") {
                $exp = explode("/", $value["uri_comp"]);
            }
            $params = Winged::$params;

            if (!$params) {
                $params = [];
            }

            $init = true;

            $countParams = is_array($params) ? count7($params) : 0;
            $countExp = is_array($exp) ? count7($exp) : 0;

            if ($countParams == $countExp) {
                for ($x = 0; $x < $countExp; $x++) {
                    $math = explode(":", $exp[$x]);
                    $is_action = false;
                    if (count7($math) == 2 && $math[0] == "action") {
                        $preg = trim($math[1]);
                        $is_action = true;
                    } else {
                        $preg = $exp[$x];
                    }

                    if (!preg_match("/^" . $preg . "$/", $params[$x]) && $preg != 'no-rule') {
                        Error::_die(__CLASS__, "{$params[$x]} was reproved by rule {$preg}", __FILE__, __LINE__);
                        $init = false;
                        break;
                    }

                    if ($is_action) {
                        $this->actions[] = $preg;
                        $search = array_search($preg, $params);
                        if (is_int($search)) {
                            unset($params[$search]);
                        }
                    }
                }
            } else {
                $init = false;
            }

            if ($init) {
                $newparams = [];
                foreach ($params as $key => $now) {
                    $newparams[] = $now;
                }
                Winged::$params = $newparams;
                return $value;
            }

        }

        return false;

    }

    public function get_actions_count()
    {
        $count = is_array($this->actions) ? count7($this->actions) : 0;
        return $count;
    }

    public function get_actions()
    {
        return $this->actions;
    }

    public function addroute($index, $route)
    {
        $index = WingedLib::dotslash(WingedLib::dotslash($index), true);
        if (!array_key_exists($index, $this->routes)) {
            $this->routes[$index] = $route;
        } else {
            Error::push(128, "Route '" . $index . "' in file path '" . Winged::$routed_file . "' was doubled.", __FILE__, __LINE__);
        }
    }

    public function render($path, $vars = [], $loads = [])
    {
        if (file_exists($path) && !is_directory($path)) {
            foreach ($vars as $key => $value) {
                if (!is_int($key) && is_string($key)) {
                    ${$key} = $value;
                }
            }
            foreach ($vars as $key => $value) {
                if (!is_int($key) && is_string($key)) {
                    ${$key} = $value;
                }
            }
            foreach ($loads as $key => $value) {
                if (file_exists($value) && !is_directory($value)) {
                    include_once $value;
                }
            }
            require $path;
        } else {
            Error::push(128, "file {$path} can't rendred because file not found.", __FILE__, __LINE__);
        }
    }

}