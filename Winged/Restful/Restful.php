<?php

namespace Winged\Restful;

use Winged\Winged;

class Restful
{

    private $rests = array();
    public static $rest = false;
    private $actions = array();

    public function restful_page()
    {
        $page = Winged::$page;
        $page_key = Winged::$key;
        $parent = Winged::$parent;
        $routed_file = Winged::$routed_file;
        $route_dir = Winged::$route_dir;

        $vect = $this->find_key($page_key);

        if ($vect) {

            $active_method = $vect["method"];
            $path = $vect["class_path"];
            $class_name = $vect["class_name"];
            $function = $vect["function"];
            $construct = false;
            $extra = array();

            if (array_key_exists("construct", $vect)) {
                $construct = $vect["construct"];
            }

            if (array_key_exists("extra", $vect)) {
                $extra = $vect["extra"];
            }

            $x = 0;
            $array = array();

            if (count7(Winged::$params) > 0 && gettype(Winged::$params) == "array") {
                foreach ($vect as $key => $value) {
                    if (is_int($key)) {
                        $param = Winged::$params[$x];
                        if (gettype($value) == "object" && get_class($value) == "Rest") {
                            if ($value->rule) {
                                if (!preg_match("/" . $value->rule . "/", $param) && $value->rule != 'no-rule') {
                                    Winged::error("the parameter '" . $param . "' failed by the rule '" . $value->rule . "'");
                                }
                            }
                            if (array_key_exists($value->name, $array)) {
                                Winged::error("the nickname for the parameters was doubled. duplicate nickname '" . $value->name . "'");
                            } else {
                                $array[$value->name] = $param;
                            }
                            $x++;
                            if ($x >= count7(Winged::$params)) {
                                break;
                            }
                        } else {
                            Winged::error("the value found during the looping is not a type 'Rest' object.");
                        }
                    }
                }
            }

            Winged::$oparams = Winged::$params;
            Winged::$params = $array;

            if (count7($array) > 0) {
                if ($vect[count7($array) - 1]->method) {
                    $function = $vect[count7($array) - 1]->method;
                }
                if ($vect[count7($array) - 1]->class_path) {
                    $path = $vect[count7($array) - 1]->class_path;
                }
                if ($vect[count7($array) - 1]->class_name) {
                    $class_name = $vect[count7($array) - 1]->class_name;
                }
                if ($vect[count7($array) - 1]->construct) {
                    $construct = $vect[count7($array) - 1]->construct;
                }
                if ($vect[count7($array) - 1]->extra) {
                    $extra = $vect[count7($array) - 1]->extra;
                }
            }

            Winged::$geted_file = false;

            if (file_exists($path) && !is_directory($path)) {
                include_once($path);
                if (class_exists($class_name)) {
                    if (array_key_exists("loads", $extra)) {
                        foreach ($extra["loads"] as $key => $load) {
                            if (file_exists($load) && !is_directory($load)) {
                                include_once $load;
                            } else {
                                CoreError::_die(__CLASS__, "include_once fails for expression {$load}", __FILE__, __LINE__);
                            }
                        }
                    }
                    if (array_key_exists("vars", $extra)) {
                        foreach ($extra["vars"] as $key => $var) {
                            ${$key} = $var;
                        }
                    }
                    if ($construct && is_array($construct)) {
                        $reflection = new ReflectionClass($class_name);
                        $obj = $reflection->newInstanceArgs($construct);
                    } else {
                        $obj = new $class_name();
                    }
                    if (method_exists($obj, $function)) {
                        self::$rest = $vect;
                        $reflect = new ReflectionMethod($class_name, $function);
                        $apply = $reflect->getParameters();
                        $num = 0;
                        if (!empty($apply)) {
                            $apply = $apply[0];
                            foreach ($apply as $key => $value) {
                                $num++;
                            }
                        }
                        if (count7(Winged::$oparams) >= $num) {
                            $the_params = Winged::$oparams;
                            if (array_key_exists("extra_params", $extra) && is_array($extra["extra_params"])) {
                                array_push($the_params, $extra["extra_params"]);
                            }
                            $ret = $reflect->invokeArgs($obj, $the_params);
                            if (gettype($ret) != null && gettype($ret) == "array") {
                                echo json_encode($ret);
                            }
                        } else {
                            Winged::error("the number of arguments to the method (" . $function . ") '" . $num . "'  is greater than that found in the url '" . count7(Winged::$oparams) . "'");
                        }
                    } else {
                        Winged::error("method '" . $function . "' no exists on class '" . $class_name . "'");
                    }
                } else {
                    Winged::error("class '" . $class_name . "' no exists on path '" . $path . "'");
                }
            } else {
                Winged::error("path '" . $path . "' no exists");
            }
        } else {
            Winged::error("no rest was found in the file " . $routed_file);
        }

    }


    private function find_key($okey)
    {
        $okey = trim($okey);
        $found = false;
        $f_num = 0;
        $stack = array();
        foreach ($this->rests as $key => $value) {
            $key = trim($key);
            $exp = explode("/", $key);
            $index = wl::dotslash(wl::dotslash($exp[1]), true);
            $pos = stripos($key, $okey);
            if (is_int($pos) && !$found) {
                $replace = str_replace($okey, "", $key);
                $found = array("key" => $key, "value" => $value, "uri_comp" => wl::dotslash($replace));
                $value["uri_comp"] = wl::dotslash($replace);
                $stack[$key] = $value;
                $f_num++;
            } else if (is_int($pos)) {
                $replace = str_replace($okey, "", $key);
                $value["uri_comp"] = wl::dotslash($replace);
                $stack[$key] = $value;
                $f_num++;
            }
        }
        if ($f_num > 1) {
            return $this->corrert_route($stack);
        } else if ($f_num == 0) {
            return false;
        } else {
            $this->actions = array();
            $newparams = array();
            $exp = array();
            if ($found["uri_comp"] != "") {
                $exp = explode("/", $found["uri_comp"]);
            }
            $params = Winged::$params;
            if (count7($params) == count7($exp) || Winged::$is_standard) {

                $init = true;
                for ($x = 0; $x < count7($exp); $x++) {
                    $math = explode(":", $exp[$x]);
                    $is_action = false;
                    if (@count7($math) == 2 && trim($math[0]) == "action") {
                        $preg = trim($math[1]);
                        $is_action = true;
                    } else {
                        $preg = $exp[$x];
                    }
                    if (!preg_match("/" . $preg . "/", $params[$x]) && $preg != 'no-rule') {
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
            Winged::error("the URL passed as reference for restful call was recognized by a route, but the comparison of the parameters count found in the URL and the parameters count configured for the route are different. Make a new call using the correct number of parameters in the URL.");
            return false;
        }

    }

    private function corrert_route($probable_routes)
    {
        foreach ($probable_routes as $key => $value) {
            $this->actions = array();
            $exp = array();
            if ($value["uri_comp"] != "") {
                $exp = explode("/", $value["uri_comp"]);
            }

            $params = Winged::$params;

            if (!$params) {
                $params = array();
            }

            $init = true;
            if (count7($params) == count7($exp)) {
                for ($x = 0; $x < count7($exp); $x++) {
                    $math = explode(":", $exp[$x]);
                    $is_action = false;
                    if (count7($math) == 2 && $math[0] == "action") {
                        $preg = trim($math[1]);
                        $is_action = true;
                    } else {
                        $preg = $exp[$x];
                    }

                    if (!preg_match("/^" . $preg . "$/", $params[$x]) && $preg != 'no-rule') {
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
                $newparams = array();
                foreach ($params as $key => $now) {
                    $newparams[] = $now;
                }
                Winged::$params = $newparams;
                return $value;
            }

        }
        return false;
    }

    public function addrest($index, $rest)
    {
        $index = wl::dotslash(wl::dotslash($index), true);
        if (!array_key_exists($index, $this->rests)) {
            $this->rests[$index] = $rest;
        } else {
            Winged::error("Rest '" . $index . "' in file path '" . Winged::$routed_file . "' was doubled.");
        }
    }

}