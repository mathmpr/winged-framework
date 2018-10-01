<?php

namespace Winged;

use Winged\Date\Microtime;
use Winged\Buffer\Buffer;
use Winged\Error\Error;
use Winged\Utils\Container;
use Winged\Database\Connections;
use Winged\Utils\FileTree;

/**
 * Class WingedHead
 * @package Winged
 */
class WingedHead
{
    public static function init()
    {
        if (file_exists('./winged.globals.php')) {
            include_once './winged.globals.php';
        }

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
        define("DB_DRIVER_MYSQL", "mysql:host=%s;port=%s;dbname=%s"); //host, port, dbname, user, pass
        define("DB_DRIVER_SQLSRV", "sqlsrv:Server=%s,%s;Database=%s"); //host, port, dbname, user, pass
        define("DB_DRIVER_PGSQL", "pgsql:host=%s;port=%s;dbname=%s;"); //host, port, dbname, user, pass
        define("DB_DRIVER_SQLITE", "sqlite:%s"); //dbname

        define("PATH_CONFIG", DOCUMENT_ROOT . "config.php");
        define("PATH_EXTRA_CONFIG", DOCUMENT_ROOT . "extra.config.php");
        define("CLASS_PATH", DOCUMENT_ROOT . "Winged/");
        define("STD_CONFIG", DOCUMENT_ROOT . "Winged/config/config.php");
        define("STD_ROUTES", DOCUMENT_ROOT . "Winged/routes/");

        ini_set("display_errors", true);

        ini_set("display_startup_errors", true);

        umask(0);

        clearstatcache();

        global $_ORIGINAL_POST, $_ORIGINAL_GET;

        $_ORIGINAL_POST = $_POST;
        $_ORIGINAL_GET = $_GET;

        global $__autoload__cache;
        $__autoload__cache = false;

        include_once CLASS_PATH . 'External/MatthiasMullie/Autoload.php';
        include_once CLASS_PATH . 'Utils/Functions.php';
        include_once CLASS_PATH . 'Utils/FileTree.php';
        include_once CLASS_PATH . 'Autoload.Cache.php';

        date_default_timezone_set('GMT');

        /**
         * @param $className
         */
        function findClass($className)
        {
            $tree = new FileTree();
            $tree->gemTree(CLASS_PATH, ['php']);
            $files = $tree->getFiles();

            $cache = "<?php global " . '$__autoload__cache' . "; ";
            $exec = '$__autoload__cache' . " = [";
            foreach ($files as $file) {
                $exec .= '
    "' . $file . '",';
                if (is_int(strpos($file, $className))) {
                    include_once $file;
                }
            }
            $exec = substr($exec, 0, strlen($exec) - 1);
            $exec .= '
];';
            eval($exec);
            file_put_contents(CLASS_PATH . 'autoload.cache.php', $cache . $exec);
        }

        /**
         * @param $className
         */
        function findCache($className)
        {
            global $__autoload__cache;
            $included = false;
            if (is_array($__autoload__cache)) {
                foreach ($__autoload__cache as $file) {
                    if (is_int(strpos($file, $className))) {
                        if (file_exists($file)) {
                            $included = true;
                            include_once $file;
                        }
                    }
                }
                if (!$included) {
                    findClass($className);
                }
            } else {
                findClass($className);
            }
        }

        spl_autoload_register(function ($className) {
            $className = explode('\\', $className);
            $className = end($className);
            findCache($className);
        });

        /**
         * Auto load for folder ./models/
         * @param $className
         */
        spl_autoload_register(function ($className) {
            $className = explode('\\', $className);
            $className = end($className);
            if (file_exists("./models/" . $className . ".php")) {
                include_once "./models/" . $className . ".php";
            }
            if (file_exists(Winged::$parent . "models/" . $className . ".php")) {
                include_once Winged::$parent . "models/" . $className . ".php";
            }
            if (!is_null(WingedConfig::$INCLUDES)) {
                if (gettype(WingedConfig::$INCLUDES) == "array") {
                    for ($x = 0; $x < count7(WingedConfig::$INCLUDES); $x++) {
                        if (file_exists(WingedConfig::$INCLUDES[$x] . "models/" . $className . ".php")) {
                            include_once WingedConfig::$INCLUDES[$x] . "models/" . $className . ".php";
                        }
                    }
                } else {
                    if (file_exists(WingedConfig::$INCLUDES . "models/" . $className . ".php")) {
                        include_once WingedConfig::$INCLUDES . "models/" . $className . ".php";
                    }
                }
            }
        });

        Microtime::init();

        Buffer::start();

        Container::$self = new Container(Container::$self);

        if (!file_exists(PATH_CONFIG)) {
            Error::_die('file config.php do not exists.', 110, 'winged.class.php', 110);
        } else {
            include_once PATH_CONFIG;

            if (!class_exists('Winged\WingedConfig')) {
                Error::_die('class WingedConfig do not exists in config.php', 101, 'winged.class.php', 101);
            }

            if (WingedConfig::$DBEXT) {
                Connections::init();
                $_GET = no_injection_array($_ORIGINAL_GET);
                $_POST = no_injection_array($_ORIGINAL_POST);
            }

            if (file_exists(PATH_EXTRA_CONFIG)) {
                include_once PATH_EXTRA_CONFIG;
            }

            if (!is_null(WingedConfig::$TIMEZONE)) {
                date_default_timezone_set(WingedConfig::$TIMEZONE);
            } else {
                date_default_timezone_set("Brazil/West");
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
    }
}