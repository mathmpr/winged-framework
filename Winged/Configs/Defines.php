<?php

$persists = 0;

if (!defined('DOCUMENT_ROOT')) {
    $document_root = str_replace('\\', '/', $_SERVER['SCRIPT_FILENAME']);
    $document_root = explode('/', $document_root);
    array_pop($document_root);
    $document_root = join('/', $document_root);
    while (!file_exists($document_root . '/Winged')) {
        $document_root = explode('/', $document_root);
        array_pop($document_root);
        if (count($document_root) <= 1) {
            $persists++;
        }
        $document_root = join('/', $document_root);
        if ($persists === 2) {
            echo 'Die. Folder Winged not found in any location.';
            exit;
        }
    }
    define('DOCUMENT_ROOT', $document_root . '/');
}

if (defined('PARENT_DIR_PAGE_NAME')) {
    return null;
}

define("PARENT_DIR_PAGE_NAME", 1);
define("ROOT_ROUTES_PAGE_NAME", 2);
define("PARENT_ROUTES_ROUTE_PHP", 3);
define("ROOT_ROUTES_ROUTE_PHP", 4);

define("USE_PREPARED_STMT", true);
define("NO_USE_PREPARED_STMT", false);
define("IS_PDO", "PDO");
define("IS_MYSQLI", "MYSQLI");

define("DB_DRIVER_CUBRID", "cubrid:host=%s;port=%s;dbname=%s");
define("DB_DRIVER_MYSQL", "mysql:host=%s;port=%s;dbname=%s");
define("DB_DRIVER_SQLSRV", "sqlsrv:Server=%s,%s;Database=%s");
define("DB_DRIVER_PGSQL", "pgsql:host=%s;port=%s;dbname=%s;");
/** a contant here */
define("DB_DRIVER_SQLITE", "sqlite:%s");

define("PATH_CONFIG", DOCUMENT_ROOT . "WingedConfig.php");
define("PATH_DATABASE_CONFIG", DOCUMENT_ROOT . "WingedDatabaseConfig.php");
define("EXTRAS", DOCUMENT_ROOT . "Extras.php");
define("CLASS_PATH", DOCUMENT_ROOT . "Winged/");