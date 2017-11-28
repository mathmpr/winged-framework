<?php

ini_set('memory_limit', '2048M');
ini_set('max_execution_time', '120');
ini_set('upload_max_filesize', '64MB');
ini_set('max_file_uploads', '100');
ini_set('post_max_size', '64MB');

define("PARENT_DIR_PAGE_NAME", 1);
define("ROOT_ROUTES_PAGE_NAME", 2);
define("PARENT_ROUTES_ROUTE_PHP", 3);
define("ROOT_ROUTES_ROUTE_PHP", 4);

define("USE_PREPARED_STMT", true);
define("NO_USE_PREPARED_STMT", false);
define("IS_PDO", "PDO");
define("IS_MYSQLI", "MYSQLI");

define("DB_DRIVER_CUBRID", "cubrid:host=%s;port=%s;dbname=%s"); //host, port, dbname, user pass
define("DB_DRIVER_FIREBIRD", "firebird:dbname=%s/%s:%s"); //host(dmname), port(dbname), file_path, user, pass
define("DB_DRIVER_MYSQL", "mysql:host=%s;port=%s;dbname=%s"); //host || unix_socket, port, dbname, user, pass
define("DB_DRIVER_MYSQL_UNIX", "mysql_unix:unix_socket=%s;port=%s;dbname=%s"); //host || unix_socket, port, dbname, user, pass
define("DB_DRIVER_SQLSRV", "sqlsrv:Server=%s,%s;Database=%s"); //host, port, dbname, user, pass
define("DB_DRIVER_PGSQL", "pgsql:dbname=%s;host=%s"); //dbname, host, user, pass
define("DB_DRIVER_SQLITE", "sqlite:%s"); //dbname

define("PATH_CONFIG", "./config.php");
define("PATH_EXTRA_CONFIG", "./extra.config.php");
define("CLASS_PATH", "./winged/");
define("STD_CONFIG", "./winged/config/config.php");
define("STD_ROUTES", "./winged/routes/");

include_once CLASS_PATH . 'utils/functions.php';

ini_set("display_errors", true);

ini_set("display_startup_errors", true);

umask(0);

clearstatcache();

global $_OPOST, $_OGET, $beggin_time;

$_OPOST = $_POST;
$_OGET = $_GET;

include_once(CLASS_PATH . "external/phpQuery.php");
include_once(CLASS_PATH . "autoload/winged.autoload.php");
include_once(CLASS_PATH . "utils/winged.lib.php");
include_once(CLASS_PATH . "rewrite/winged.rewrite.php");
include_once(CLASS_PATH . "rewrite/winged.parameter.php");
include_once(CLASS_PATH . "controller/winged.assets.php");
include_once(CLASS_PATH . "controller/winged.controller.php");
include_once(CLASS_PATH . "restful/winged.restful.php");
include_once(CLASS_PATH . "session/winged.session.php");
include_once(CLASS_PATH . "date/winged.date.php");
include_once(CLASS_PATH . "date/winged.microtime.php");
include_once(CLASS_PATH . "file/winged.download.php");
include_once(CLASS_PATH . "file/winged.upload.php");
include_once(CLASS_PATH . "string/winged.string.php");
include_once(CLASS_PATH . "token/winged.tokanizer.php");
include_once(CLASS_PATH . "file/winged.fileutils.php");
include_once(CLASS_PATH . "file/winged.img.file.php");
include_once(CLASS_PATH . "http/winged.response.php");
include_once(CLASS_PATH . "http/winged.request.php");
include_once(CLASS_PATH . "form/winged.form.html.store.php");
include_once(CLASS_PATH . "form/winged.form.php");
include_once(CLASS_PATH . "validator/winged.validator.php");
include_once(CLASS_PATH . "formater/winged.formater.php");
include_once(CLASS_PATH . "error/winged.error.php");
include_once(CLASS_PATH . "buffer/winged.buffer.php");

Microtime::init();

CoreBuffer::start();

function __autoload($class)
{
    WingedAutoLoad::verify($class);
}

class Container
{
    protected $target;
    protected $className;
    protected $methods = [];

    /**
     * @var $self Container
     */
    public static $self = null;

    public function __construct($target)
    {
        $this->target = $target;
    }

    public function attach($name, $method)
    {
        if (!$this->className) {
            $this->className = get_class($this->target);
        }
        $binded = Closure::bind($method, $this->target, $this->className);
        $this->methods[$name] = $binded;
    }

    public function __call($name, $arguments)
    {
        if (array_key_exists($name, $this->methods)) {
            return call_user_func_array($this->methods[$name], $arguments);
        }

        if (method_exists($this->target, $name)) {
            return call_user_func_array(
                array($this->target, $name),
                $arguments
            );
        }
    }

    public function methodExists($name)
    {
        if (array_key_exists($name, $this->methods)) {
            return true;
        }
    }

}

Container::$self = new Container(Container::$self);

if (!file_exists(PATH_CONFIG)) {
    CoreError::_die('file config.php do not exists.', 88, 'winged.class.php', 88);
} else {
    include_once PATH_CONFIG;
    if (!class_exists('WingedConfig')) {
        CoreError::_die('class WingedConfig do not exists in config.php', 92, 'winged.class.php', 92);
    }
    if (file_exists(PATH_EXTRA_CONFIG)) {
        include_once PATH_EXTRA_CONFIG;
    }
    if (WingedConfig::$DBEXT) {
        include_once(CLASS_PATH . "database/winged.db.php");
        include_once(CLASS_PATH . "database/winged.querybuilder.php");
        include_once(CLASS_PATH . "database/winged.delegate.php");
        include_once(CLASS_PATH . "database/winged.migrate.php");
        include_once(CLASS_PATH . "database/winged.db.dict.php");
        include_once(CLASS_PATH . "model/winged.model.php");

        Connections::init();

        $_GET = no_injection_array($_OGET);
        $_POST = no_injection_array($_OPOST);
    }

    if (!is_null(WingedConfig::$TIMEZONE)) {
        date_default_timezone_set(WingedConfig::$TIMEZONE);
    } else {
        date_default_timezone_set("Brazil/West");
    }

    if (!is_null(WingedConfig::$INCLUDES)) {
        if (gettype(WingedConfig::$INCLUDES) == "array") {
            for ($x = 0; $x < count(WingedConfig::$INCLUDES); $x++) {
                include_once WingedConfig::$INCLUDES[$x];
            }
        } else {
            include_once WingedConfig::$INCLUDES;
        }
    }
}

if (WingedConfig::$DEBUG) {
    error_reporting(E_ALL);
} else {
    error_reporting(E_WARNING | E_NOTICE | E_ERROR);
}

mb_internal_encoding(WingedConfig::$INTERNAL_ENCODING);
mb_http_output(WingedConfig::$OUTPUT_ENCODING);
header('Content-type: ' . WingedConfig::$MAIN_CONTENT_TYPE . '; charset=' . WingedConfig::$HTML_CHARSET . '');

/**
 * This class its a main class of Winged
 * @version 1.8.3.5
 * @access public static object
 * @author Matheus Prado Rodrigues
 * @copyright (c) 2017, Winged Framework
 */
class Winged
{

    public static $standard;
    public static $standard_controller;
    public static $controller_page;
    public static $controller_action;
    public static $controller_debug = true;
    public static $http;
    public static $https;
    public static $protocol;
    public static $uri = false;
    public static $pure_uri = false;
    public static $page;
    public static $parent = false;
    public static $params = array();
    public static $oparams = array();
    public static $controller_params = array();
    public static $key;
    public static $page_surname;
    public static $routed_file;
    public static $router = 1;
    public static $routes = array();
    public static $restful = false;
    public static $route_dir;
    /**
     * @var $rewrite_obj Rewrite
     */
    public static $rewrite_obj = false;
    /**
     * @var $restful_obj Restful
     */
    public static $restful_obj;
    /**
     * @var $controller Controller
     */
    public static $controller;
    public static $geted_file;
    public static $reset;
    public static $notfound;
    public static $is_standard = false;
    public static $ob_buffer = false;

    /**
     * @access public
     * @example Winged::start() this method starts all of winged, don't call this method again.
     * @return void
     */
    public static function start()
    {
        if (!self::$rewrite_obj) {
            self::$rewrite_obj = new Rewrite();
            self::$restful_obj = new Restful();
            self::$controller = new Controller();
        }
        if (is_null(WingedConfig::$NOTFOUND) || !WingedConfig::$NOTFOUND) {
            WingedConfig::$NOTFOUND = "./winged/class/rewrite/error/404.php";
        }
        self::$controller_debug = (WingedConfig::$CONTROLLER_DEBUG !== null) ? WingedConfig::$CONTROLLER_DEBUG : true;
        if (is_null(WingedConfig::$STANDARD)) {
            self::$standard = WingedConfig::$STANDARD;
            self::$notfound = WingedConfig::$NOTFOUND;
        } else {
            self::$notfound = WingedConfig::$NOTFOUND;
            self::$standard = WingedConfig::$STANDARD;
            self::$standard_controller = WingedConfig::$STANDARD_CONTROLLER;
            self::$router = WingedConfig::$ROUTER;
        }
        self::nosplit();
    }

    public static function nosplit()
    {
        self::normalize();
        self::restful();

        $arr_ext = ['.php', '.html', '.htm', '.xml', '.json'];

        if (WingedConfig::$NOT_WINGED) {
            $dirs = self::getdir(wl::dotslash(self::$uri), 'pure-html');
            self::$parent = $dirs['parent'];
            self::$page_surname = $dirs['page'];
            foreach ($arr_ext as $ext) {
                if (file_exists(self::$parent . self::$page_surname . $ext)) {
                    include_once self::$parent . self::$page_surname . $ext;
                    if (WingedConfig::$DEBUG && CoreError::warnings()) {
                        CoreBuffer::flush();
                    }
                    exit;
                }
            }
        }

        $dirs = self::getdir(wl::dotslash(self::$uri));

        $page = trim($dirs["page"]);

        $parent = wl::dotslash(wl::dotslash(trim($dirs["parent"])), true);
        $params = $dirs["params"];

        self::$key = $parent . $page . "/";

        self::$page_surname = $page;
        self::$page = $page;
        self::$parent = $parent;
        self::$params = $params;
        self::$controller_params = $params;

        $vect = self::return_path_route();

        self::$page = $vect["page"];
        self::$routed_file = DOCUMENT_ROOT . str_replace('./', '', $vect["file"]);
        self::$route_dir = DOCUMENT_ROOT . str_replace('./', '', $vect["dir"]);

        $controller_info = self::controller_info();

        self::$controller_page = $controller_info['controller'];
        self::$controller_action = $controller_info['action'];

        if (!self::$restful) {
            $before = false;
            if (Container::$self->methodExists('beforeSearchController')) {
                $before = Container::$self->beforeSearchController();
            }
            if ($before === false || $before === null) {
                $before = false;
                $found = self::$controller->find();
                if (!$found) {
                    if (Container::$self->methodExists('whenControllerNotFound')) {
                        $before = Container::$self->whenControllerNotFound();
                    }
                    if ($before === false || $before === null) {
                        self::$rewrite_obj->rewrite_page();
                    }
                }
            }
        }

    }

    private static function controller_info()
    {
        $exp = wl::slashexplode(Winged::$parent);
        $uri = wl::slashexplode(self::$uri);
        $nar = [];

        if (isset($exp[0]) && $exp[0] == '') {
            return ['controller' => self::$standard_controller, 'action' => 'index'];
        } else {
            for ($i = 0; $i < count($uri); $i++) {
                if (!in_array($uri[$i], $exp)) {
                    $nar[] = $uri[$i];
                }
            }
            if (count($nar) == 0) {
                return ['controller' => self::$standard_controller, 'action' => 'index'];
            } else if (count($nar) == 1) {
                return ['controller' => $nar[0], 'action' => 'index'];
            } else {
                return ['controller' => $nar[0], 'action' => $nar[1]];
            }
        }
    }

    public static function normalize()
    {

        $b_uri = server("request_uri");
        $free_get = explode("?", $b_uri);
        $uri = wl::convertslash($free_get[0]);
        $self = wl::convertslash(server("php_self"));
        $host = wl::convertslash(server("server_name"));

        $uris = wl::slashexplode($uri);
        $selfs = wl::slashexplode($self);

        $self = "";
        $lastself = "";
        for ($x = 0; $x < count($selfs); $x++) {
            if ($selfs[$x] != "index.php") {
                if ($x == 0) {
                    $self = $selfs[$x];
                } else {
                    $self .= "/" . $selfs[$x];
                }
                $lastself = $selfs[$x];
            }
        }

        $fix = false;
        $cont = 0;
        $find = 0;
        $inarray = array();

        for ($x = 0; $x < count($uris); $x++) {
            if ($uris[$x] == $lastself || $lastself == "") {
                $fix = true;
                array_push($inarray, $lastself);
            }

            $str_count = count($inarray);

            if (($fix && $uris[$x] != $lastself) || ($fix && $str_count >= 2)) {
                if ($cont == 0) {
                    $uri = "./" . $uris[$x];
                    $cont++;
                } else {
                    $uri .= "/" . $uris[$x];
                }
                $find++;
            }
        }

        if ($find == 0) {
            $uri = ".";
        }

        $uri .= "/";

        if ($self == "") {
            $https = "https://" . $host . "/";
            $http = "http://" . $host . "/";
        } else {
            $https = "https://" . $host . "/" . $self . "/";
            $http = "http://" . $host . "/" . $self . "/";
        }

        self::$uri = $uri;
        if (count($free_get) > 1) {
            self::$pure_uri = $uri . $free_get[1];
        } else {
            self::$pure_uri = $uri;
        }

        self::$https = $https;
        self::$http = $http;

        if (server('https')) {
            if (server('https') != 'off') {
                self::$protocol = $https;
            }
        } else {
            self::$protocol = $http;
        }
    }

    public static function restful()
    {
        $uri = wl::dotslash(self::$uri);
        $exp = wl::slashexplode($uri);
        if (count($exp) > 0 && $exp[0] == "restful") {
            self::$restful = true;
            unset($exp[0]);
            $uri = wl::dotslash(join("/", $exp), true);
            self::$uri = $uri;
        }
    }

    public static function getdir($uri, $extra_dir = false)
    {
        $exp = wl::slashexplode($uri);

        $dir = '';

        if ($extra_dir) {
            $dir .= './' . $extra_dir . '/';
        }

        if (count($exp) > 0) {

            $x = 0;

            if ($dir == '') {
                $dir .= wl::dotslash($exp[$x], true);
            } else {
                $dir .= $exp[$x] . '/';
            }

            if (is_directory($dir)) {
                unset($exp[$x]);
            } else {
                if ($dir == '') {
                    $dir .= "./";
                } else {
                    $dir = './' . $extra_dir . '/';
                }
            }

            foreach ($exp as $key => $value) {
                $ant = $dir;
                if ($x == 0) {
                    $dir .= $value;
                } else {
                    $dir .= "/" . $value;
                }
                if (is_directory($dir)) {
                    unset($exp[$key]);
                } else {
                    $dir = $ant;
                    break;
                }
                $x++;
            }

            if (count($exp) == 0) {
                self::$is_standard = true;
                return array(
                    "page" => self::$standard,
                    "parent" => $dir,
                    "params" => false
                );
            } else {
                $exp = wl::resetarray($exp);
                $page = $exp[0];
                unset($exp[0]);
                $params = array();
                foreach ($exp as $key => $value) {
                    array_push($params, $value);
                }
                return array(
                    "page" => $page,
                    "parent" => $dir,
                    "params" => $params
                );
            }
        }
        self::$is_standard = true;
        return array(
            "page" => self::$standard,
            "parent" => "./",
            "params" => false
        );
    }

    public static function return_path_route()
    {
        $parent = self::$parent;
        $router = self::$router;
        $page = self::$page;
        if (is_null($router)) {
            $router = 1;
        }
        switch ($router) {
            case 1:
                return array(
                    "file" => $parent . "routes/" . $page . ".php",
                    "dir" => $parent . "routes/",
                    "page" => $page
                );
                break;

            case 2:
                return array(
                    "file" => "./routes/" . $page . ".php",
                    "dir" => "./routes/",
                    "page" => $page
                );
                break;

            case 3:
                return array(
                    "file" => $parent . "routes/routes.php",
                    "dir" => $parent . "routes/",
                    "page" => "routes"
                );
                break;

            default:
                return array(
                    "file" => "./routes/routes.php",
                    "dir" => "./routes/",
                    "page" => "routes"
                );
                break;
        }
    }

    /**
     * Example:
     * <code>
     * Winged::addRoute('./init/', array(
     *      "index" => "real_path_to_my_view.php"
     * ));
     *
     * or...
     *
     * Winged::addRoute('./init/pattern_rule_for_this_parameter', array(
     *      "index" => "real_path_to_my_view.php"
     *       new Parameter("my_parameter", "./other_file_include.php"),
     * ));
     * </code>
     * @access public
     * @param string $index path or math to search in url.
     * @param array $route is an array with all parameters.
     * @return void
     */
    public static function addroute($index, $route)
    {
        self::$rewrite_obj->addroute($index, $route);
    }

    public static function addrest($index, $rest)
    {
        self::$restful_obj->addrest($index, $rest);
    }

    public function setDefault404()
    {
        self::$restful_obj->setDefault404();
    }

    public static function post()
    {
        return $_POST;
    }

    public static function get()
    {
        return $_GET;
    }

    public static function initialJs()
    {
        return '<script>
                    window.protocol = "' . Winged::$protocol . '"; 
                    window.page_surname = "' . Winged::$page_surname . '"; 
                    window.uri = "' . Winged::$uri . '"; 
                    window.controller_params = JSON.parse(\'' . json_encode(Winged::$controller_params) . '\'); 
                    window.controller_action = "' . Winged::$controller_action . '";                
                </script>';
    }

}

if (file_exists('./winged.globals.php')) {
    include_once './winged.globals.php';
}