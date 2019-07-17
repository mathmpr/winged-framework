<?php

namespace Winged\Controller;

use Winged\Database\Connections;
use Winged\Directory\Directory;
use Winged\File\File;
use Winged\Frontend\Render;
use Winged\Http\HttpResponseHandler;
use Winged\Utils\WingedLib;
use Winged\Winged;
use \WingedConfig;
use Winged\Error\Error;

/**
 * Class Controller
 *
 * @package Winged\Controller
 */
class Controller extends Render
{
    public static $CONTROLLERS_PATH = './controllers/';
    public static $MODELS_PATH = './models/';
    public static $VIEWS_PATH = './views/';

    private $controller_path = false;
    private $controller_name = false;
    private $action_name = false;
    private $query_params = [];
    private $method_args = [];
    private $controller_reset = false;
    private $error_level = 0;
    private $vitual_success = false;

    /**
     * @var bool | callable $beforeSearch
     */
    public static $beforeSearch = false;

    /**
     * @var bool | callable $whenNotFound
     */
    public static $whenNotFound = false;

    /**
     * called in all requests before search any controller
     *
     * @param null $calback
     */
    public static function beforeSearch($calback = null)
    {
        if (is_callable($calback)) {
            self::$beforeSearch = $calback;
        }
    }

    /**
     * called in all requests when any controller not founded
     *
     * @param null $calback
     */
    public static function whenNotFound($calback = null)
    {
        if (is_callable($calback)) {
            self::$whenNotFound = $calback;
        }
    }

    /**
     * Controller constructor.
     */
    public function __construct()
    {
        $this->copy();
        parent::__construct();
    }

    /**
     * create property dynamic inside current controller
     *
     * @param $key
     * @param $value
     *
     * @return $this
     */
    public function dynamic($key, $value)
    {
        $this->{$key} = $value;
        return $this;
    }

    /**
     * try to find a controller based into string like an URI
     *
     * @param string $path
     *
     * @return array|bool
     * @throws \ReflectionException
     */
    public function virtualString($path = '')
    {
        $exp = explode('/', $path);
        if (count7($exp) >= 1 && $exp[0] != '') {
            Winged::$controller_page = $exp[0];
            if (isset($exp[1])) {
                Winged::$controller_action = $exp[1];
                array_splice($exp, 1, 1);
            } else {
                Winged::$controller_action = 'index';
            }
            array_splice($exp, 0, 1);
        }
        if (count7($exp) > 0) {
            Winged::$params = $exp;
        }
        $find = $this->find();
        if ($find) {
            $this->vitual_success = true;
            Winged::$controller = $this;
        }
        return $find;
    }

    /**
     * simulates a call like a request to an controller with explicit call
     *
     * @param       $controller
     * @param bool  $action
     * @param array $uri
     *
     * @return array|bool
     * @throws \ReflectionException
     */
    public function virtual($controller, $action = false, $uri = [])
    {
        Winged::$controller_page = $controller;
        if ($action === false) {
            Winged::$controller_action = 'index';
        } else {
            Winged::$controller_action = $action;
        }
        if (is_array($uri)) {
            Winged::$params = $uri;
        }
        $find = $this->find();
        if ($find) {
            $this->vitual_success = true;
            Winged::$controller = $this;
        }
        return $find;
    }

    /**
     * look for all aspects in controller
     * check if directory <path>/controller exists
     * check if file controller exists
     * check if class inside controller exists and match with name founded in URI
     *
     * @param string $controller_name
     * @param string $action_name
     *
     * @return array|bool
     */
    public static function controllerObserver($controller_name = '', $action_name = '')
    {
        if (WingedConfig::$config->PARENT_FOLDER_MVC) {
            self::$CONTROLLERS_PATH = Winged::$parent . WingedLib::clearPath(self::$CONTROLLERS_PATH) . '/';
            self::$MODELS_PATH = Winged::$parent . WingedLib::clearPath(self::$MODELS_PATH) . '/';
            self::$VIEWS_PATH = Winged::$parent . WingedLib::clearPath(self::$VIEWS_PATH) . '/';
        } else {
            self::$CONTROLLERS_PATH = WingedLib::clearPath(self::$CONTROLLERS_PATH) . '/';
            self::$MODELS_PATH = WingedLib::clearPath(self::$MODELS_PATH) . '/';
            self::$VIEWS_PATH = WingedLib::clearPath(self::$VIEWS_PATH) . '/';
        }
        $controller_name = self::getControllerName($controller_name);
        $directory = new Directory(self::$CONTROLLERS_PATH, false);
        $controller = new File(self::$CONTROLLERS_PATH . $controller_name . '.php', false);
        if ($directory->exists()) {
            if ($controller->exists()) {
                $action_name = self::getActionName($action_name);
                include_once $controller->file_path;
                if (class_exists($controller_name)) {
                    return [
                        'action' => $action_name,
                        'controller' => $controller_name,
                        'path' => $controller->file_path
                    ];
                } else {
                    if (WingedConfig::$config->CONTROLLER_DEBUG) {
                        trigger_error("Controller class '" . $controller_name . "' no exists in file '" . $controller->file_path . "'", E_USER_ERROR);
                    }
                }
            } else {
                trigger_error("File '" . $controller->file_path . "' no exists", E_USER_ERROR);
            }
        } else {
            trigger_error("Directory '" . $directory->folder . "' no exists", E_USER_ERROR);
        }
        return false;
    }

    /**
     * @return array|bool
     * @throws \ReflectionException
     */
    public function find()
    {
        if (!$this->reset()) {
            $observer = self::controllerObserver();
            if ($observer) {
                $this->action_name = $observer['action'];
                $this->controller_path = $observer['path'];
                $this->controller_name = $observer['controller'];
                if (is_array(Winged::$controller_params)) {
                    if (in_array(Winged::$controller_action, Winged::$controller_params)) {
                        $key = array_search(Winged::$controller_action, Winged::$controller_params);
                        unset(Winged::$controller_params[$key]);
                    }
                    if (in_array(Winged::$controller_page, Winged::$controller_params)) {
                        $key = array_search(Winged::$controller_page, Winged::$controller_params);
                        unset(Winged::$controller_params[$key]);
                    }
                    Winged::$controller_params = array_values(Winged::$controller_params);
                }
                $obj = new $this->controller_name();
                if (method_exists($obj, 'beforeAction')) {
                    $to_call = [];
                    $this->getGetArgs();
                    $reflect = new \ReflectionMethod($this->controller_name, 'beforeAction');
                    $apply = $reflect->getParameters();

                    if (!empty($apply)) {
                        foreach ($apply as $key => $value) {
                            if (isset($this->method_args[$value->name])) {
                                $to_call[] = $this->method_args[$value->name];
                            }
                        }
                    }

                    $return = $reflect->invokeArgs($obj, [Winged::$controller_action]);
                    if ($return !== null) {
                        if (is_array($return)) {
                            try {
                                $json = json_encode($return);
                                echo $json;
                            } catch (\Exception $error) {
                                return true;
                            }
                        }
                        return true;
                    }
                }
                $to_call = [];
                if (method_exists($obj, $this->action_name)) {
                    $this->getGetArgs();
                    $reflect = new \ReflectionMethod($this->controller_name, $this->action_name);
                    $apply = $reflect->getParameters();
                    $controller_warn = false;
                    if (!empty($apply)) {
                        foreach ($apply as $key => $value) {
                            if (!isset($this->method_args[$value->name])) {
                                $controller_warn = true;
                                Error::push(__CLASS__, "Action '" . $this->action_name . "' requires parameter'" . $value->name . "'", __FILE__, __LINE__);
                            } else {
                                $to_call[] = $this->method_args[$value->name];
                            }
                        }
                    }
                    if (!$controller_warn) {
                        if ($this->error_level == 1) {
                            Error::clear();
                        }
                        $return = $reflect->invokeArgs($obj, $to_call);
                        if (is_array($return)) {
                            try {
                                $json = json_encode($return);
                                echo $json;
                            } catch (\Exception $error) {
                                return true;
                            }
                        }
                    }
                } else {
                    if (method_exists($obj, 'whenActionNotFound')) {
                        $reflect = new \ReflectionMethod($this->controller_name, 'whenActionNotFound');
                        $apply = $reflect->getParameters();
                        if (!empty($apply)) {
                            foreach ($apply as $key => $value) {
                                if (isset($this->method_args[$value->name])) {
                                    $to_call[] = $this->method_args[$value->name];
                                }
                            }
                        }
                        $return = $reflect->invokeArgs($obj, [Winged::$controller_action]);
                        if (is_array($return)) {
                            try {
                                $json = json_encode($return);
                                echo $json;
                            } catch (\Exception $error) {
                                return true;
                            }
                        }
                    }
                }
                return $observer;
            }
            return $observer;
        }
        return false;
    }

    /**
     * get query string from $_GET + opction $push param
     *
     * @param array $push
     *
     * @return string
     */
    public function getQueryStringConcat($push = [])
    {
        $param = '';
        $fisrt = true;
        foreach ($this->method_args as $key => $value) {
            if ($fisrt) {
                $fisrt = false;
                if (array_key_exists($key, $push)) {
                    $param .= $key . '=' . $push[$key];
                    unset($push[$key]);
                } else {
                    $param .= $key . '=' . $value;
                }
            } else {
                if (array_key_exists($key, $push)) {
                    $param .= '&' . $key . '=' . $push[$key];
                    unset($push[$key]);
                } else {
                    $param .= '&' . $key . '=' . $value;
                }
            }
        }
        $fisrt = false;
        foreach ($push as $key => $value) {
            if ($fisrt) {
                $fisrt = false;
                $param .= $key . '=' . $value;
            } else {
                $param .= '&' . $key . '=' . $value;
            }
        }
        return $param;
    }

    /**
     * reset main controller informations
     * check if other config file exists inside parent dir
     * rebuild configs and try to find controller again
     *
     * @return bool
     */
    private function reset()
    {
        $parent = Winged::$parent;
        $outher_conf = $parent . "config/config.php";
        if (file_exists($outher_conf) && !is_directory($outher_conf) && !$this->controller_reset) {
            include_once $outher_conf;
            mb_internal_encoding(WingedConfig::$config->INTERNAL_ENCODING);
            mb_http_output(WingedConfig::$config->OUTPUT_ENCODING);
            Error::clear();
            $this->controller_reset = true;
            Connections::closeAll();
            Connections::init();
            Winged::start();
            return true;
        }
        return false;
    }

    /**
     * get controller name founded in URI if $name param not passed
     * if passed convert name into Camel Case format
     *
     * @param bool $name
     *
     * @return string
     */
    public static function getControllerName($name = false)
    {
        if (!$name) {
            $name = Winged::$controller_page;
        }
        $exp = explode("-", str_replace(['.', '_'], '-', $name));
        foreach ($exp as $index => $names) {
            $exp[$index] = ucfirst($exp[$index]);
        }
        return trim(implode('', $exp) . 'Controller');
    }

    /**
     * get action name founded in URI if $name param not passed
     * if passed convert name into Camel Case format
     *
     * @param bool $name
     *
     * @return string
     */
    public static function getActionName($name = false)
    {
        if (!$name) {
            $name = Winged::$controller_action;
        }
        $exp = explode("-", str_replace(['.', '_'], '-', $name));
        foreach ($exp as $index => $names) {
            $exp[$index] = ucfirst($exp[$index]);
        }
        return trim('action' . implode('', $exp));
    }

    /**
     * put $_GET inside current controller
     */
    public function getGetArgs()
    {
        foreach ($_GET as $index => $value) {
            if (is_array($value)) {
                $this->method_args[$index] = $value;
            } else if ((intval($value) == 0 && $value == '0') || intval($value) > 0) {
                $this->method_args[$index] = (int)$value;
            } else {
                $this->method_args[$index] = $value;
            }
        }
        return;
    }

    /**
     * redirect to any location, full URL string is required
     *
     * @param string $path
     */
    public function redirectOnly($path = '')
    {
        header('Location: ' . $path);
    }

    /**
     * redirect to an path
     *
     * @param string $path
     * @param bool   $keep_args
     */
    public function redirectTo($path = '', $keep_args = true)
    {
        $args_path = explode('?', $path);
        $path = $args_path[0];
        $args = explode('?', server('request_uri'));
        $join = [];
        if (count7($args) >= 2 && $keep_args) {
            if (count7($args_path) >= 2) {
                $args = explode('&', end($args));
                $args_path = explode('&', end($args_path));
                foreach ($args_path as $arg) {
                    $from_redi = explode('=', $arg);
                    $key_redi = array_shift($from_redi);
                    $from_redi = [$key_redi => (count7($from_redi) > 1 ? implode('=', $from_redi) : end($from_redi))];
                    foreach ($args as $varg) {
                        $from_url = explode('=', $varg);
                        $key_url = array_shift($from_url);
                        $from_url = [$key_url => (count7($from_url) > 1 ? implode('=', $from_url) : end($from_url))];
                        if ($key_redi == $key_url) {
                            $join[$key_redi] = $from_redi[$key_redi];
                        } else {
                            $join[$key_url] = $from_url[$key_url];
                        }
                    }
                }
                $args = '';
                foreach ($join as $key => $value) {
                    $args .= $key . '=' . $value;
                }
            } else {
                $args = '?' . array_pop($args);
            }
        } else {
            if (count7($args_path) >= 2) {
                $args = end($args_path);
            } else {
                $args = '';
            }
        }
        if ($path != '') {
            if (endstr($path) != '/') {
                $path .= '/';
            }
        }
        if (trim($args) != '') {
            $args = '?' . $args;
        }
        if (WingedConfig::$config->PARENT_FOLDER_MVC) {
            $parent = WingedLib::clearPath(Winged::$parent);
            if ($parent == '') {
                header('Location: ' . Winged::$protocol . $path . $args);
            } else {
                header('Location: ' . Winged::$protocol . $parent . '/' . $path . $args);
            }
        } else {
            header('Location: ' . Winged::$protocol . $path . $args);
        }
        exit;
    }

    /**
     * set nicknames in uri
     * Ex: /users/edit/1
     * {controller}/{action}/1
     * $nicks = ['id']
     * {controller}/{action}/{id}
     *
     * @param array $nicks
     */
    public function setNicknamesToUri($nicks = [])
    {
        $narr = [];
        if (Winged::$controller_params == false) {
            Winged::$controller_params = [];
        }
        if (count7($nicks) > count7(Winged::$controller_params)) {
            for ($x = 0; $x < count7($nicks); $x++) {
                if (array_key_exists($x, Winged::$controller_params)) {
                    $narr[$nicks[$x]] = Winged::$controller_params[$x];
                } else {
                    $narr[$nicks[$x]] = null;
                }
            }
        } else {
            for ($x = 0; $x < count7($nicks); $x++) {
                $narr[$nicks[$x]] = Winged::$controller_params[$x];
            }
        }
        Winged::$controller_params = $narr;
    }

    /**
     * copy informations from main controler locate in Winged to new controller
     */
    public function copy()
    {
        if (Winged::$controller !== null) {
            Winged::$controller->getGetArgs();
            $this->controller_path = Winged::$controller->controller_path;
            $this->controller_name = Winged::$controller->controller_name;
            $this->query_params = Winged::$controller->query_params;
            $this->method_args = Winged::$controller->method_args;
            $this->action_name = Winged::$controller->action_name;
        }
    }

    /**
     * get specific
     *
     * @param $key
     *
     * @return bool|mixed
     */
    public function get($key)
    {
        if (array_key_exists($key, $this->method_args)) {
            return $this->method_args[$key];
        }
        return false;
    }

    /**
     * @param $key
     *
     * @return bool|mixed
     */
    public function params($key)
    {
        if (array_key_exists($key, $this->query_params)) {
            return $this->query_params[$key];
        }
        return false;
    }

    /**
     * render view/controller response as html, add head tag with js and css files, and raw headers
     *
     * @param string $path
     * @param array  $vars
     *
     * @return bool
     */
    public function html($path, $vars = [])
    {
        $content = $this->_render($this->view($path), $vars);
        if ($content && $this->checkCalls()) {
            try {
                $this->activeMinify();
            } catch (\Exception $exception) {
                trigger_error($exception->getMessage(), E_USER_ERROR);
            }
            if (is_string(WingedConfig::$config->HEAD_CONTENT_PATH)) {
                $this->appendAbstractHead('__first_head_content___', WingedConfig::$config->HEAD_CONTENT_PATH);
            }
            $this->configureAssets($content);
            $this->compactHtml($content);
            $this->reconfigurePaths($content);
            return $this->channelingRender($content, 'html');
        }
        return false;
    }

    /**
     * return view path has file path
     *
     * @param $path
     *
     * @return string
     */
    private function view($path)
    {
        $path .= '.php';
        return self::$VIEWS_PATH . $path;
    }

    /**
     * render any file with extensions *.html, *.json, *.php, *.yaml and *.json
     *
     * @param string $path
     * @param array  $vars
     *
     * @return bool
     */
    public function file($path, $vars = [])
    {
        $content = $this->_render($path, $vars);
        $file = new File($path, false);
        if ($content && $this->checkCalls()) {
            return $this->channelingRender($content, ($file->getExtension() === 'php' ? 'html' : $file->getExtension()));
        }
        return false;
    }

    /**
     * render view/controller response as html without any included html
     *
     * @param string $path
     * @param array  $vars
     * @param bool   $return
     *
     * @return bool|string
     */
    public function partial($path, $vars = [], $return = false)
    {
        $content = $this->_render($this->view($path), $vars);
        if ($content && $this->checkCalls()) {
            if ($return) {
                return $content;
            }
            return $this->channelingRender($content, 'html');
        }
        return false;
    }

    /**
     * render view/controller response as json
     *
     * @param string $path
     * @param array  $vars
     * @param bool   $return
     *
     * @return bool|string
     */
    public function json($path, $vars = [], $return = false)
    {
        $content = $this->_render($this->view($path), $vars);
        if ($content && $this->checkCalls()) {
            if ($return) {
                return $content;
            }
            return $this->channelingRender($content, 'json');
        }
        return false;
    }

    /**
     * channel all final render calls to this function, check if an error exists and if not, dispatch http response with content
     *
     * @param string $content
     * @param string $type
     *
     * @return bool
     */
    private function channelingRender($content, $type = 'html')
    {
        if (Error::exists()) {
            Error::display();
        }
        $response = new HttpResponseHandler();
        switch ($type) {
            case 'html':
                $response->dispatchHtml($content, false);
                return true;
                break;
            case 'json':
                $response->dispatchJson($content, false);
                return true;
                break;
            case 'xml':
                $response->dispatchXml($content, false);
                return true;
                break;
        }
        return false;
    }

}