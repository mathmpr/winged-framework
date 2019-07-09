<?php

namespace Winged\Controller;

use Winged\External\MatthiasMullie\Minify\Minify\CSS;
use Winged\External\MatthiasMullie\Minify\Minify\JS;
use Winged\Date\Date;
use Winged\Directory\Directory;
use Winged\File\File;
use Winged\Frontend\Render;
use Winged\Http\HttpResponseHandler;
use Winged\Utils\Container;
use Winged\Utils\RandomName;
use Winged\Utils\WingedLib;
use Winged\Winged;
use WingedConfig;
use Winged\Error\Error;
use Winged\Buffer\Buffer;
use \Exception;

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
    private $body_class = null;
    private $html_class = null;
    private $error_level = 0;
    private $vitual_success = false;

    public function __construct()
    {
        $this->copy();
        parent::__construct();
    }

    public function dynamic($key, $value)
    {
        $this->{$key} = $value;
        return $this;
    }

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
                        trigger_error("Controller class '" . $controller_name . "' no exists in file '" . $controller->file_path . "'", E_USER_WARNING);
                    }
                }
            }
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
            Winged::start();
            return true;
        }
        return false;
    }

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
     *
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
     * @throws Exception
     */
    public function html($path, $vars = [])
    {
        $content = $this->_render($this->view($path), $vars);
        if ($content && $this->checkCalls()) {
            $this->activeMinify();
            if(is_string(WingedConfig::$config->HEAD_CONTENT_PATH)){
                $this->appendAbstractHead('__first_head_content___', WingedConfig::$config->HEAD_CONTENT_PATH);
            }
            $this->configureAssets($content);
            $this->compactHtml($content);
            return $this->channelingRender($content, 'html');
        }

        if ($content) {
            //$content = $this->pureHtml($content);
            $rep = [];
            $match = false;
            $matchs = null;
            if (is_int(stripos($content, '<textarea'))) {
                $match = preg_match_all('#<textarea(.*?)>(.*?)</textarea>#is', $content, $matchs);
                if ($match) {
                    foreach ($matchs[0] as $match) {
                        $rep[] = '#___' . RandomName::generate('sisisisi') . '___#';
                    }
                    $content = str_replace($matchs[0], $rep, $content);
                }
            }
            $match_code = false;
            $matchs_code = null;
            if (is_int(stripos($content, '<code'))) {
                $match_code = preg_match_all('#<code(.*?)>(.*?)</code>#is', $content, $matchs_code);
                if ($match_code) {
                    foreach ($matchs_code[0] as $match_code) {
                        $rep[] = '#___' . RandomName::generate('sisisisi') . '___#';
                    }
                    $content = str_replace($matchs_code[0], $rep, $content);
                }
            }
            $content = preg_replace('#> <#', '><', preg_replace('# {2,}#', ' ', preg_replace('/[\n\r]|/', '', $content)));
            if ($match) {
                $content = str_replace($rep, $matchs[0], $content);
            }
            if ($match_code) {
                $content = str_replace($rep, $matchs_code[0], $content);
            }

            global $__base__;
            $__base__ = false;

            preg_replace_callback('#<base.+?href="([^"]*)".*?/?>#i', function ($found) {
                global $__base__;
                $__base__ = $found[1];
                if ($__base__ == "") {
                    $__base__ = false;
                }
            }, $content);

            $perms_tags = [
                'script' => '#<script.+?src="([^"]*)".*?/?>#i',
                'img' => '#<img.+?src="([^"]*)".*?/?>#i',
                'source' => '#<source.+?src="([^"]*)".*?/?>#i',
                'link' => '#<link.+?href="([^"]*)".*?/?>#i',
            ];

            $unicid_assets = [];

            Container::$self->attach('__resolve_pattern_parser___', function ($matches) {
                $full_string = $matches[0];
                $only_match = $matches[1];
                $base = false;

                if (is_string(Container::$self->__base__)) {
                    $base = str_replace(
                        [
                            Winged::$protocol,
                            Winged::$http,
                            Winged::$https,
                        ],
                        '',
                        Container::$self->__base__
                    );
                }

                $copy_match = WingedLib::clearPath($only_match);
                $copy_match = str_replace(
                    [
                        Winged::$protocol,
                        Winged::$http,
                        Winged::$https,
                    ],
                    '',
                    $copy_match
                );
                if (is_string($base)) {
                    $file = new File(WingedLib::normalizePath($base) . $copy_match, false);
                    $mime = explode('/', $file->getMimeType());
                    $mime = $mime[0];
                    if (($file->exists() && in_array($file->getExtension(), [
                                'json',
                                'html',
                                'xml',
                                'css',
                                'htm',
                                'js',
                                'svg'
                            ])) || (($file->exists() && $mime == 'image'))) {
                        if (
                            (!is_int(stripos($copy_match, 'https://')) &&
                                !is_int(stripos($copy_match, 'http://')) &&
                                !is_int(stripos($copy_match, '//'))) ||
                            (is_int(stripos($copy_match, Winged::$http)) ||
                                is_int(stripos($copy_match, Winged::$https))
                            )
                        ) {
                            $copy_match = Winged::$protocol . '__winged_file_handle_core__/' . base64_encode(WingedLib::normalizePath($base) . $copy_match);
                            $full_string = str_replace($only_match, $copy_match, $full_string);
                            $only_match = $copy_match;
                        }
                    }
                }


                if (in_array(Container::$self->__tag__, Container::$self->unicid_assets)) {
                    if (is_int(stripos($only_match, '?'))) {
                        $full_string = str_replace($only_match, $only_match . '&get=' . RandomName::generate('sisisi'), $full_string);
                    } else {
                        $full_string = str_replace($only_match, $only_match . '?get=' . RandomName::generate('sisisi'), $full_string);
                    }
                }

                return $full_string;

            });

            if (is_array(WingedConfig::$config->USE_UNICID_ON_INCLUDE_ASSETS)) {
                $unicid_assets = WingedConfig::$config->USE_UNICID_ON_INCLUDE_ASSETS;
            }

            if (!empty($unicid_assets) || WingedConfig::$config->ADD_CACHE_CONTROL) {

                Container::$self->unicid_assets = $unicid_assets;
                Container::$self->__base__ = $__base__;

                foreach ($perms_tags as $tag => $pattern) {
                    Container::$self->__tag__ = $tag;
                    $content = preg_replace_callback($pattern, [Container::$self, '__resolve_pattern_parser___'], $content);
                }
            }

            /*
            if ($content) {
                $minified = array_shift($this->css);
                $file = new File($minified['string'], false);
                $file_content = $file->read();
                $originals = [];
                $replaces = [];
                $trades = [];
                if ($file->exists()) {
                    if (Container::$self->__css_paths__) {
                        foreach (Container::$self->__css_paths__ as $path => $paths) {
                            $path = new File($path, false);
                            if ($path->exists()) {
                                foreach ($paths as $files) {
                                    if (!empty($files)) {
                                        $target_original = str_replace(['"', "'"], '', $files['file']);
                                        $target = new File($path->folder->folder . $target_original, false);
                                        if ($target->exists()) {
                                            $originals[] = $files['file'];
                                            $replaces[] = RandomName::generate('sisis', false, false);
                                            $trades[] = Winged::$protocol . '__winged_file_handle_core__/' . base64_encode($target->file_path);

                                            if ($target_original != $files['file']) {
                                                $originals[] = $target_original;
                                                $replaces[] = RandomName::generate('sisis', false, false);
                                                $trades[] = end($trades);
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
                $file_content = str_replace($originals, $replaces, $file_content);
                $file_content = str_replace($replaces, $trades, $file_content);
                $file->write($file_content);
            }*/
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

    private function createJsMinifyNew($path, $read)
    {
        $read[$path] = [
            'create_at' => Date::now()->timestamp(),
            'formed_with' => [],
            'cache_file' => './cache/js/' . RandomName::generate('sisisisi', true, false) . '.js'
        ];
        $cache_file = new File($read[$path]['cache_file']);
        $minify = new JS();
        foreach ($this->js as $identifier => $content) {
            if (!in_array($identifier, $this->remove_js)) {
                if ($content['type'] === 'file') {
                    $file = new File($content['string'], false);
                    if ($file->exists()) {
                        $minify->add($file->read());
                        $this->removeJs($identifier);
                        $read[$path]['formed_with'][$content['string']] = [
                            'time' => $file->modifyTime(),
                            'path' => $file->file_path,
                            'name' => $file->file,
                            'identifier' => $identifier,
                        ];
                    }
                }
            }
        }

        $cache_file->write($minify->minify());

        $this->js = array_merge([$path => ['string' => $cache_file->file_path, 'type' => 'file']], $this->js);
        return $this->persistsMinifiedCacheFileInformation($read);
    }

    private function createJsMinify()
    {
        $this->createMinifiedCacheFileInformation();
        $read = $this->readMinifiedCacheFileInformation();
        $path = '';
        foreach ($this->js as $identifier => $content) {
            if (!in_array($identifier, $this->remove_js)) {
                if ($content['type'] === 'file') {
                    $path .= $content['string'];
                }
            }
            $path = md5($path);
        }
        if (WingedConfig::$config->AUTO_MINIFY) {
            if (!array_key_exists($path, $read) && $path !== '') {
                return $this->createJsMinifyNew($path, $read);
            } else {
                if (array_key_exists($path, $read)) {
                    $check = $read[$path];
                    $renew = false;
                    foreach ($check['formed_with'] as $key => $former) {
                        $file = new File($key, false);
                        if (!$file->exists()) {
                            $renew = true;
                        } else {
                            if ($former['time'] != $file->modifyTime()) {
                                $renew = true;
                            }
                        }
                        if (!array_key_exists($former['identifier'], $this->js)) {
                            $renew = true;
                        }
                    }
                    if ((int)Date::now()->diff((new Date($check['create_at'])), ['i'])->minutes > (int)WingedConfig::$config->AUTO_MINIFY) {
                        $renew = true;
                    }
                    if ($renew) {
                        $old_cache_file = new File($read[$path]['cache_file']);
                        if ($old_cache_file->exists()) {
                            $old_cache_file->delete();
                        }
                        return $this->createJsMinifyNew($path, $read);
                    } else {
                        foreach ($check['formed_with'] as $key => $former) {
                            $this->removeJs($former['identifier']);
                        }
                        $this->js = array_merge([$path => ['string' => $check['cache_file'], 'type' => 'file']], $this->js);
                    }
                }
            }
        } else {
            if (array_key_exists($path, $read)) {
                $check = $read[$path];
                foreach ($check['formed_with'] as $key => $former) {
                    $this->removeJs($former['identifier']);
                }
                $this->js = array_merge([$path => ['string' => $check['cache_file'], 'type' => 'file']], $this->js);
            }
        }
        return false;
    }

    private function createCssMinifyNew($path, $read)
    {
        //$pattern = '/url\(([\'"]?.[^\'"]*\.(png|jpg|jpeg|gif|svg|woff|woff2|ttf|otf|eot|css)[\'"]?)\)/i';
        $pattern = '/url\((?![\'"]?(?:data|http):)[\'"]?([^\'"\)]*)[\'"]?\)/i';
        $read[$path] = [
            'create_at' => Date::now()->timestamp(),
            'formed_with' => [],
            'cache_file' => './cache/css/' . RandomName::generate('sisisisi', true, false) . '.css'
        ];
        $cache_file = new File($read[$path]['cache_file']);
        $minify = new CSS();
        Container::$self->__css_paths__ = [];

        function __recursiveCheck__($matches, $_file, $pattern, $minify)
        {
            /**
             * @var $_file  File
             * @var $minify CSS
             */
            $full_string = $matches[0];
            $file = str_replace(['"', "'"], '', $matches[1]);
            //$extension = trim($matches[2]);
            $now_in = Container::$self->__css_path_now__;

            $file = explode(')', $file);
            $file = array_shift($file);
            $exp = explode('.', $file);
            $extension = end($exp);

            Container::$self->vars['__css_paths__'][Container::$self->__css_path_now__][] =
                [
                    'full_string' => $full_string,
                    'file' => $file,
                    'extension' => $extension
                ];
            if ($extension == 'css') {
                $_file = new File($_file->folder->folder . $file, false);
                if ($_file->exists()) {
                    Container::$self->vars['__css_path_now__'] = $_file->file_path;
                    Container::$self->vars['__css_paths__'][Container::$self->__css_path_now__] = [];
                    preg_replace_callback($pattern, function ($matches) use ($file, $pattern, $minify) {
                        __recursiveCheck__($matches, $file, $pattern, $minify);
                    }, $_file->read());
                    $minify->add($_file->read());
                }
                Container::$self->vars['__css_path_now__'] = $now_in;
            }
        }

        foreach ($this->css as $identifier => $content) {
            if (!in_array($identifier, $this->remove_css)) {
                if ($content['type'] === 'file') {
                    $file = new File($content['string'], false);
                    if ($file->exists()) {
                        Container::$self->vars['__css_path_now__'] = $file->file_path;
                        Container::$self->vars['__css_paths__'][Container::$self->__css_path_now__] = [];
                        preg_replace_callback($pattern, function ($matches) use ($file, $pattern, $minify) {
                            __recursiveCheck__($matches, $file, $pattern, $minify);
                        }, $file->read());

                        $minify->add($file->read());
                        $this->removeCss($identifier);
                        $read[$path]['formed_with'][$content['string']] = [
                            'time' => $file->modifyTime(),
                            'path' => $file->file_path,
                            'name' => $file->file,
                            'identifier' => $identifier,
                        ];
                    }
                }
            }
        }

        $cache_file->write($minify->minify());
        $this->css = array_merge([$path => ['string' => $cache_file->file_path, 'type' => 'file']], $this->css);
        return $this->persistsMinifiedCacheFileInformation($read);
    }

    private function createCssMinify()
    {
        $this->createMinifiedCacheFileInformation();
        $read = $this->readMinifiedCacheFileInformation();
        $path = '';

        if (WingedConfig::$config->AUTO_MINIFY) {
            foreach ($this->css as $identifier => $content) {
                if (!in_array($identifier, $this->remove_css)) {
                    if ($content['type'] === 'file') {
                        $path .= $content['string'];
                    }
                }
                $path = md5($path);
            }
            if (!array_key_exists($path, $read) && $path !== '') {
                return $this->createCssMinifyNew($path, $read);
            } else {
                //check and update if need
                if (array_key_exists($path, $read)) {
                    $check = $read[$path];
                    $renew = false;
                    foreach ($check['formed_with'] as $key => $former) {
                        $file = new File($key, false);
                        if (!$file->exists()) {
                            $renew = true;
                        } else {
                            if ($former['time'] != $file->modifyTime()) {
                                $renew = true;
                            }
                        }
                        if (!array_key_exists($former['identifier'], $this->css)) {
                            $renew = true;
                        }
                    }
                    if ((int)Date::now()->diff((new Date($check['create_at'])), ['i'])->minutes > (int)WingedConfig::$config->AUTO_MINIFY) {
                        $renew = true;
                    }
                    if ($renew) {
                        $old_cache_file = new File($read[$path]['cache_file']);
                        if ($old_cache_file->exists()) {
                            $old_cache_file->delete();
                        }
                        return $this->createCssMinifyNew($path, $read);
                    } else {
                        foreach ($check['formed_with'] as $key => $former) {
                            $this->removeCss($former['identifier']);
                        }
                        $this->css = array_merge([$path => ['string' => $check['cache_file'], 'type' => 'file']], $this->css);
                    }
                }
            }
        } else {
            if (array_key_exists($path, $read)) {
                $check = $read[$path];
                foreach ($check['formed_with'] as $key => $former) {
                    $this->removeCss($former['identifier']);
                }
                $this->css = array_merge([$path => ['string' => $check['cache_file'], 'type' => 'file']], $this->css);
            }
        }
        return false;
    }

    private function createMinifiedCacheFileInformation()
    {
        $file = new File('./minify.cache.json', false);
        if (!$file->exists()) {
            $file = new File('./minify.cache.json');
            $file->write(json_encode([]));
        } else {
            $file = new File('./minify.cache.json');
        }
        $this->minify_cache = $file;
    }

    private function persistsMinifiedCacheFileInformation($content)
    {
        return ($this->minify_cache->write(json_encode($content)));
    }

    private function readMinifiedCacheFileInformation()
    {
        return json_decode($this->minify_cache->read(), true);
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