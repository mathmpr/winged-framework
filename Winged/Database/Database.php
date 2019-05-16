<?php

namespace Winged\Database;

use Winged\Database\Drivers\Cubrid;
use Winged\Database\Drivers\Firebird;
use Winged\Database\Drivers\MySQL;
use Winged\Database\Drivers\PostgreSQL;
use Winged\Database\Drivers\Sqlite;
use Winged\Database\Drivers\SQLServer;
use Winged\Error\Error;
use WingedConfig;
use Winged\Database\Types\PreparedMysqli;
use Winged\Database\Types\PreparedPDO;
use Winged\Database\Types\NormalMysqli;
use Winged\Database\Types\NormalPDO;


/**
 * provides an ELOQUENT database mannager
 *
 * Class Database
 *
 * @package Winged\Database
 */
class Database
{
    /**
     * @var $db \mysqli | \PDO
     */
    public $db = null;
    public $db_tables = [];
    /**
     * @var $abstract NormalMysqli | NormalPDO | PreparedMysqli | PreparedPDO
     */
    public $abstract = null;
    public $class = null;
    public $driver = null;
    public $cleared = null;
    public $classes = null;
    public $nickname = null;

    public $host = '';
    public $user = '';
    public $password = '';
    public $dbname = '';
    public $port = 0;
    public $schema = '';

    /**
     * @var $queryStringHandler null | Cubrid | Firebird | MySQL | PostgreSQL | Sqlite | SQLServer
     */
    public $queryStringHandler = null;

    const SP_SHOW_TABLES = 'SHOW TABLES';
    const SP_DESC_TABLE = 'DESC TABLE';

    private $drivers = [
        'cubrid' => DB_DRIVER_CUBRID,
        'mysql' => DB_DRIVER_MYSQL,
        'sqlsrv' => DB_DRIVER_PGSQL,
        'pgsql' => DB_DRIVER_SQLSRV,
        'sqlite' => DB_DRIVER_SQLITE
    ];

    private $cleared_drivers = null;

    function __construct($class = false, $driver = false, $nickname = false)
    {

        $this->nickname = $nickname;

        $this->classes = [
            "responsible_class" => [
                USE_PREPARED_STMT => 'Winged\Database\Types\PreparedPDO',
                NO_USE_PREPARED_STMT => 'Winged\Database\Types\NormalPDO',
            ],
        ];

        $this->cleared_drivers = [
            "cubrid" =>
                [
                    "handler" => "Winged\Database\Drivers\Cubrid",
                    "real_name" => "cubrid",
                    "object" => function () {

                    }
                ],
            "mysql" =>
                [
                    "handler" => "Winged\Database\Drivers\MySQL",
                    "real_name" => "mysql",
                    "object" => function ($args) {
                        /**
                         * @var $host     string
                         * @var $user     string
                         * @var $password string
                         * @var $dbname   string
                         * @var $port     string
                         */
                        extract($args);
                        $host = $this->getRealHost($host);
                        $user = $this->getRealUser($user);
                        $password = $this->getRealPassword($password);
                        $dbname = $this->getRealDbname($dbname);
                        $port = $this->getRealPort($port);
                        try {
                            $pdo = new \PDO(sprintf($this->drivers['mysql'], $host, $port, $dbname), $user, $password);
                            $pdo->setAttribute(\PDO::ATTR_EMULATE_PREPARES, FALSE);
                            return $pdo;
                        } catch (\PDOException $error) {
                            return $error->getMessage();
                        }
                    }
                ],
            "sqlsrv" =>
                [
                    "handler" => "Winged\Database\Drivers\SQLServer",
                    "real_name" => "sqlsrv",
                    "object" => function () {

                    }
                ],
            "pgsql" =>
                [
                    "handler" => "Winged\Database\Drivers\PostgreSQL",
                    "real_name" => "pgsql",
                    "object" => function ($args) {
                        /**
                         * @var $host       string
                         * @var $user       string
                         * @var $password   string
                         * @var $dbname     string
                         * @var $port       string
                         * @var $schema     string
                         */
                        extract($args);
                        $host = $this->getRealHost($host);
                        $user = $this->getRealUser($user);
                        $password = $this->getRealPassword($password);
                        $dbname = $this->getRealDbname($dbname);
                        $port = $this->getRealPort($port);
                        $schema = $this->getRealPort($schema);
                    }
                ],
            "sqlite" =>
                [
                    "handler" => "Winged\Database\Drivers\Sqlite",
                    "real_name" => "sqlite",
                    "object" => function ($args) {
                        /**
                         * @var $host       string
                         * @var $user       string
                         * @var $password   string
                         * @var $dbname     string
                         * @var $port       string
                         * @var $schema     string
                         */
                        extract($args);
                        $dbname = $this->getRealDbname($dbname);
                        try {
                            return new \PDO(sprintf($this->drivers['sqlite'], $dbname), null, null, [\PDO::ATTR_PERSISTENT => true]);
                        } catch (\PDOException $error) {
                            return $error->getMessage();
                        }
                    }
                ],
        ];

        $WCclass = WingedConfig::$config->db()->STD_DB_CLASS;
        $WCdriver = WingedConfig::$config->db()->DB_DRIVER;
        if ($class !== false && $class === IS_PDO || $class === IS_MYSQLI) {
            $WCclass = $class;
        }

        if (in_array($driver, $this->drivers)) {
            $WCdriver = $driver;
        }

        if ($WCclass !== IS_MYSQLI && $WCclass !== IS_PDO) {
            Error::push(__CLASS__, "Class " . $WCclass . " not suported by Winged dadabase connections.", __FILE__, __LINE__);
            Error::display(__LINE__, __FILE__);
        }

        if (!in_array($WCdriver, $this->drivers)) {
            Error::push(__CLASS__, "Driver " . $WCdriver . " not suported by Winged dadabase connections.", __FILE__, __LINE__);
            Error::display(__LINE__, __FILE__);
        }

        if ($WCdriver !== DB_DRIVER_MYSQL && $WCclass === IS_MYSQLI) {
            Error::push(__CLASS__, "mysqli class don't suports driver " . WingedConfig::$config->db()->DB_DRIVER . ". Please change the driver in ./WingedDatabaseConfig.php to DB_DRIVER_MYSQL ou change STD_DB_CLASS in ./config.php to IS_PDO", __FILE__, __LINE__);
            Error::display(__LINE__, __FILE__);
        }

        $this->class = $WCclass;
        $this->driver = $WCdriver;
        $exp = explode(':', $WCdriver);
        $this->cleared = array_shift($exp);
        $handlerName = $this->cleared_drivers[$this->cleared]['handler'];
        $this->queryStringHandler = new $handlerName($this);

        return $this;
    }

    public function isPdo()
    {
        if ($this->class === IS_MYSQLI) {
            return false;
        }
        return true;
    }

    public function isMysqli()
    {
        if ($this->class === IS_MYSQLI) {
            return true;
        }
        return false;
    }

    public function connect($args = [])
    {
        $vars = [
            'host' => false,
            'user' => false,
            'password' => false,
            'dbname' => false,
            'schema' => false,
            'port' => 3306
        ];

        foreach ($args as $key => $arg) {
            if (array_key_exists($key, $vars)) {
                $vars[$key] = $arg;
            }
        }

        extract($vars);

        /**
         * @var $host       string
         * @var $user       string
         * @var $password   string
         * @var $dbname     string
         * @var $port       string
         * @var $schema     string
         */

        $host = $this->getRealHost($host);
        $user = $this->getRealUser($user);
        $password = $this->getRealPassword($password);
        $dbname = $this->getRealDbname($dbname);
        $port = $this->getRealPort($port);
        $schema = $this->getRealPort($schema);

        $this->host = $host;
        $this->user = $user;
        $this->password = $password;
        $this->dbname = $dbname;
        $this->port = $port;
        $this->schema = $schema;

        if ($this->class === IS_MYSQLI) {
            try {
                $this->db = new \mysqli($host, $user, $password, $dbname, $port);
            } catch (\mysqli_sql_exception $error) {
                $this->db = $error->getMessage();
            }
            if ($this->analyze_error()) {
                if (WingedConfig::$config->db()->USE_PREPARED_STMT == USE_PREPARED_STMT) {
                    $this->abstract = new PreparedMysqli($this->db);
                } else {
                    $this->abstract = new NormalMysqli($this->db);
                }
            }
        } else if ($this->class === IS_PDO) {
            $this->db = call_user_func_array($this->cleared_drivers[$this->cleared]['object'], ['args' => $vars]);
            if ($this->analyze_error()) {
                $reflection = new \ReflectionClass($this->classes['responsible_class'][WingedConfig::$config->db()->USE_PREPARED_STMT]);
                $this->abstract = $reflection->newInstanceArgs([$this->db]);
            }
        } else {
            $this->db = false;
        }

        if (!$this->analyze_error()) {
            Error::push(__CLASS__, "Can't connect in database, please check the credentials in ./WingedDatabaseConfig.php", __FILE__, __LINE__);
            Error::push(__CLASS__, "Error: " . $this->db, __FILE__, __LINE__);
            Error::display(__LINE__, __FILE__);
        }

        if ($this->nickname !== false) {
            Connections::newDb($this, $this->nickname, true);
        }

        $this->queryStringHandler->setEncoding();

        $this->db_tables = $this->show();

        foreach ($this->db_tables as $table => $info) {
            $this->db_tables[$table] = array_merge($info, ['fields' => $this->describe($table)]);
        }

        return $this;
    }

    private function analyze_error()
    {
        if (is_object($this->db)) {
            return true;
        }
        return false;
    }

    public function execute($query, $args = [])
    {
        return $this->abstract->execute($query, $args);
    }

    public function insert($query, $args = [])
    {
        return $this->abstract->insert($query, $args);
    }

    public function fetch($query, $args = [])
    {
        return $this->abstract->fetch($query, $args);
    }

    public function count($query = '', $args = [])
    {
        if ($query === '') {
            return $this->abstract->count();
        } else {
            return $this->abstract->count($query, $args);
        }
    }

    public function show()
    {
        return $this->abstract->show();
    }

    public function describe($tableName)
    {
        return $this->abstract->describe($tableName);
    }


    public function getRealHost($host = false)
    {
        if ($host !== false) {
            return $host;
        }
        if (is_string(WingedConfig::$config->db()->HOST)) {
            return WingedConfig::$config->db()->HOST;
        }
        return false;
    }

    public function getRealUser($user = false)
    {
        if ($user !== false) {
            return $user;
        }
        if (is_string(WingedConfig::$config->db()->USER)) {
            return WingedConfig::$config->db()->USER;
        }
        return false;
    }

    public function getRealPassword($password = false)
    {
        if ($password !== false) {
            return $password;
        }
        if (is_string(WingedConfig::$config->db()->PASSWORD)) {
            return WingedConfig::$config->db()->PASSWORD;
        }
        return false;
    }

    public function getRealDbname($dbname = false)
    {
        if ($dbname !== false) {
            return $dbname;
        }
        if (is_string(WingedConfig::$config->db()->DBNAME)) {
            return WingedConfig::$config->db()->DBNAME;
        }
        return false;
    }

    public function getRealPort($port = false)
    {
        if ($port !== false) {
            return $port;
        }
        if (is_string(WingedConfig::$config->db()->PORT)) {
            return WingedConfig::$config->db()->PORT;
        }
        return false;
    }

    public function getRealSchema($schema = false)
    {
        if ($schema !== false) {
            return $schema;
        }
        if (is_string(WingedConfig::$config->db()->SCHEMA)) {
            return WingedConfig::$config->db()->SCHEMA;
        }
        return false;
    }

    public static function columnExists($columns = [], $desc = [])
    {
        $columns_ok = true;
        if (is_array($columns) && is_array($desc)) {
            foreach ($desc as $key => $column) {
                if (in_array($column["Field"], $columns)) {
                    $key = array_search($column["Field"], $columns);
                    unset($columns[$key]);
                }
            }
            if (count7($columns) > 0) {
                $columns_ok = false;
            }
        } else {
            $columns_ok = false;
        }
        return $columns_ok;
    }
}