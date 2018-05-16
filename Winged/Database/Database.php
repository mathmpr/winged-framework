<?php

namespace Winged\Database;

use Winged\Error\Error;
use Winged\WingedConfig;
use Winged\Database\Types\PreparedMysqli;
use Winged\Database\Types\PreparedPDO;
use Winged\Database\Types\NormalMysqli;
use Winged\Database\Types\NormalPDO;

class Database
{
    /**
     * @var $db mysqli | PDO
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

    const SP_SHOW_TABLES = 'SHOW TABLES';
    const SP_DESC_TABLE = 'DESC TABLE';

    private $drivers = [
        'cubrid' => DB_DRIVER_CUBRID,
        'firebird' => DB_DRIVER_FIREBIRD,
        'mysql' => DB_DRIVER_MYSQL,
        'sqlsrv' => DB_DRIVER_PGSQL,
        'pgsql' => DB_DRIVER_SQLSRV,
        'sqlite' => DB_DRIVER_SQLITE,
        'mysql_unix' => DB_DRIVER_MYSQL_UNIX,
    ];

    private $cleared_drivers = null;

    function __construct($class = false, $driver = false, $nickname = false)
    {

        $this->nickname = $nickname;

        $this->classes = [
            "responsible_class" => [
                USE_PREPARED_STMT => 'PreparedPDO',
                NO_USE_PREPARED_STMT => 'NormalPDO',
            ],
        ];

        $this->cleared_drivers = [
            "cubrid" =>
                [
                    "real_name" => "cubrid",
                    "object" => function () {

                    }
                ],
            "firebird" =>
                [
                    "real_name" => "firebird",
                    "object" => function () {

                    }
                ],
            "mysql" =>
                [
                    "real_name" => "mysql",
                    "object" => function ($args) {
                        /**
                         * @var $host string
                         * @var $user string
                         * @var $password string
                         * @var $dbname string
                         * @var $port string
                         */
                        extract($args);
                        $host = $this->getRealHost($host);
                        $user = $this->getRealUser($user);
                        $password = $this->getRealPassword($password);
                        $dbname = $this->getRealDbname($dbname);
                        $port = $this->getRealPort($port);
                        try {
                            return new \PDO(sprintf($this->drivers['mysql'], $host, $port, $dbname), $user, $password);
                        } catch (\PDOException $error) {
                            return $error->getMessage();
                        }
                    }
                ],
            "sqlsrv" =>
                [
                    "real_name" => "sqlsrv",
                    "object" => function () {

                    }
                ],
            "pgsql" =>
                [
                    "real_name" => "pgsql",
                    "object" => function () {

                    }
                ],
            "sqlite" =>
                [
                    "real_name" => "sqlite",
                    "object" => function ($dbname = false) {
                        $dbname = $this->getRealDbname($dbname);
                        try {
                            return new \PDO(sprintf($this->drivers['sqlite'], $dbname), null, null, [\PDO::ATTR_PERSISTENT => true]);
                        } catch (\PDOException $error) {
                            return $error->getMessage();
                        }
                    }
                ],
            "mysql_unix" =>
                [
                    "real_name" => "mysql",
                    "object" => function ($args) {
                        /**
                         * @var $host string
                         * @var $user string
                         * @var $password string
                         * @var $dbname string
                         * @var $port string
                         */
                        extract($args);
                        $exp = $this->drivers['mysql_unix'];
                        $dns = 'mysql' . end($exp);
                        $host = $this->getRealHost($host);
                        $user = $this->getRealUser($user);
                        $password = $this->getRealPassword($password);
                        $dbname = $this->getRealDbname($dbname);
                        $port = $this->getRealPort($port);
                        try {
                            return new \PDO(sprintf($dns, $host, $port, $dbname), $user, $password);
                        } catch (\PDOException $error) {
                            return $error->getMessage();
                        }
                    }
                ],
        ];

        $WCclass = WingedConfig::$STD_DB_CLASS;
        $WCdriver = WingedConfig::$DB_DRIVER;
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
            Error::push(__CLASS__, "mysqli class don't suports driver " . WingedConfig::$DB_DRIVER . ". Please change the driver in ./config.php to DB_DRIVER_MYSQL ou change STD_DB_CLASS in ./config.php to IS_PDO", __FILE__, __LINE__);
            Error::display(__LINE__, __FILE__);
        }

        $this->class = $WCclass;
        $this->driver = $WCdriver;
        $exp = explode(':', $WCdriver);
        $this->cleared = array_shift($exp);

        return $this;
    }

    public function connect($args = [])
    {
        $vars = [
            'host' => false,
            'user' => false,
            'password' => false,
            'dbname' => false,
            'port' => 3306
        ];

        foreach ($args as $key => $arg) {
            if (array_key_exists($key, $vars)) {
                $vars[$key] = $arg;
            }
        }

        extract($vars);

        /**
         * @var $host string
         * @var $user string
         * @var $password string
         * @var $dbname string
         * @var $port string
         */

        $host = $this->getRealHost($host);
        $user = $this->getRealUser($user);
        $password = $this->getRealPassword($password);
        $dbname = $this->getRealDbname($dbname);
        $port = $this->getRealPort($port);

        if ($this->class === IS_MYSQLI) {
            try {
                $this->db = new \mysqli($host, $user, $password, $dbname, $port);
                $this->db->query('set names ' . WingedConfig::$DATABASE_CHARSET);
            } catch (\mysqli_sql_exception $error) {
                $this->db = $error->getMessage();
            }
            if ($this->analyze_error()) {
                if (WingedConfig::$USE_PREPARED_STMT == USE_PREPARED_STMT) {
                    $this->abstract = new PreparedMysqli($this->db);
                } else {
                    $this->abstract = new NormalMysqli($this->db);
                }
            }
        } else if ($this->class === IS_PDO) {
            $this->db = call_user_func_array($this->cleared_drivers[$this->cleared]['object'], ['args' => $vars]);
            if ($this->analyze_error()) {
                $this->db->exec('set names ' . WingedConfig::$DATABASE_CHARSET);
                $reflection = new \ReflectionClass($this->classes['responsible_class'][WingedConfig::$USE_PREPARED_STMT]);
                $this->abstract = $reflection->newInstanceArgs([$this->db]);
            }
        } else {
            $this->db = false;
        }

        if (!$this->analyze_error()) {
            Error::push(__CLASS__, "Can't connect in database, please check the credentials in ./config.php", __FILE__, __LINE__);
            Error::push(__CLASS__, "Error: " . $this->db, __FILE__, __LINE__);
            Error::display(__LINE__, __FILE__);
        }

        if (WingedConfig::$USE_PREPARED_STMT) {
            $this->db_tables = $this->sp(Database::SP_SHOW_TABLES, []);
        } else {
            $this->db_tables = $this->sp(Database::SP_SHOW_TABLES);
        }

        if ($this->nickname !== false) {
            Connections::newDb($this, $this->nickname);
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

    public function sp($param, $args = [])
    {
        return $this->abstract->sp($param, $args);
    }


    public function getRealHost($host = false)
    {
        if ($host !== false) {
            return $host;
        }
        if (property_exists('Winged\WingedConfig', 'HOST')) {
            return WingedConfig::$HOST;
        }
        return false;
    }

    public function getRealUser($user = false)
    {
        if ($user !== false) {
            return $user;
        }
        if (property_exists('Winged\WingedConfig', 'USER')) {
            return WingedConfig::$USER;
        }
        return false;
    }

    public function getRealPassword($password = false)
    {
        if ($password !== false) {
            return $password;
        }
        if (property_exists('Winged\WingedConfig', 'PASSWORD')) {
            return WingedConfig::$PASSWORD;
        }
        return false;
    }

    public function getRealDbname($dbname = false)
    {
        if ($dbname !== false) {
            return $dbname;
        }
        if (property_exists('Winged\WingedConfig', 'DBNAME')) {
            return WingedConfig::$DBNAME;
        }
        return false;
    }

    public function getRealPort($port = false)
    {
        if ($port !== false) {
            return $port;
        }
        if (property_exists('Winged\WingedConfig', 'PORT')) {
            return WingedConfig::$PORT;
        }
        return false;
    }

    public static function columnExists($columns = array(), $desc = array())
    {
        $columns_ok = true;
        if (is_array($columns) && is_array($desc)) {
            foreach ($desc as $key => $column) {
                if (in_array($column["Field"], $columns)) {
                    $key = array_search($column["Field"], $columns);
                    unset($columns[$key]);
                }
            }
            if (count($columns) > 0) {
                $columns_ok = false;
            }
        } else {
            $columns_ok = false;
        }
        return $columns_ok;
    }
}