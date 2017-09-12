<?php

class CurrentDB
{
    /**
     * @var $current Database
     */
    public static $current;

    public static function execute($query, $args = [])
    {
        return self::$current->execute($query, $args);
    }

    public static function insert($query, $args = [])
    {
        return self::$current->insert($query, $args);
    }

    public static function fetch($query, $args = [])
    {
        return self::$current->fetch($query, $args);
    }

    public static function count($query = '', $args = [])
    {
        return self::$current->count($query, $args);
    }

    public static function sp($param, $args = [])
    {
        return self::$current->sp($param, $args);
    }

    public static function tableExists($table_name)
    {
        if (in_array($table_name, self::$current->db_tables)) {
            return true;
        }
        return false;
    }

}

class Connections
{
    public static $connections = [];
    public static $default = 'winged';

    /**
     * init fisrt default Database connection using params founded in ./config.php
     * @param string $nickname
     */
    public static function init($nickname = 'winged')
    {
        self::$connections[$nickname] = (new Database())->connect();
        self::$connections[$nickname]->nickname = $nickname;
        CurrentDB::$current = self::$connections[$nickname];
    }

    /**
     * @param string $key
     * @return Database
     */
    public static function getDb($key = '')
    {
        if (array_key_exists($key, self::$connections)) {
            return self::$connections[$key];
        }
    }

    /**
     * engine use current connection for insert, select and update queries
     * @param $key string
     * @return bool
     */
    public static function setCurrent($key = '')
    {
        if (array_key_exists($key, self::$connections)) {
            if (get_class(self::$connections[$key]) == 'Database') {
                if (isset(self::$connections[$key])) {
                    self::$default = $key;
                    CurrentDB::$current = self::$connections[$key];
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * optional register Database object in connections
     * @param $db Database
     * @param $key string
     */
    public static function newDb($db, $key = '', $set_current = false)
    {
        self::$connections[$key] = $db;
        if ($set_current) {
            self::setCurrent($key);
        }
    }

}

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
                            return new PDO(sprintf($this->drivers['mysql'], $host, $port, $dbname), $user, $password);
                        } catch (PDOException $error) {
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
                            return new PDO(sprintf($this->drivers['sqlite'], $dbname), null, null, [PDO::ATTR_PERSISTENT => true]);
                        } catch (PDOException $error) {
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
                            return new PDO(sprintf($dns, $host, $port, $dbname), $user, $password);
                        } catch (PDOException $error) {
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
            $warn = Winged::push_warning(__CLASS__, "Class " . $WCclass . " not suported by Winged dadabase connections.", true);
            winged_error_handler("8", $warn["error_description"], __FILE__, "in class : " . __LINE__, $warn["real_backtrace"]);
            Winged::get_errors(__LINE__, __FILE__);
        }

        if (!in_array($WCdriver, $this->drivers)) {
            $warn = Winged::push_warning(__CLASS__, "Driver " . $WCdriver . " not suported by Winged dadabase connections.", true);
            winged_error_handler("8", $warn["error_description"], __FILE__, "in class : " . __LINE__, $warn["real_backtrace"]);
            Winged::get_errors(__LINE__, __FILE__);
        }

        if ($WCdriver !== DB_DRIVER_MYSQL && $WCclass === IS_MYSQLI) {
            Winged::push_warning(__CLASS__, "mysqli class don't suports driver " . WingedConfig::$DB_DRIVER . ". Please change the driver in ./config.php to DB_DRIVER_MYSQL ou change STD_DB_CLASS in ./config.php to IS_PDO", true);
            Winged::convert_warnings_into_erros();
            Winged::get_errors(__LINE__, __FILE__);
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
                $this->db = new mysqli($host, $user, $password, $dbname, $port);
                $this->db->set_charset(WingedConfig::$DATABASE_CHARSET);
            } catch (mysqli_sql_exception $error) {
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
                $reflection = new ReflectionClass($this->classes['responsible_class'][WingedConfig::$USE_PREPARED_STMT]);
                $this->abstract = $reflection->newInstanceArgs([$this->db]);
            }
        } else {
            $this->db = false;
        }

        if (!$this->analyze_error()) {
            Winged::push_warning(__CLASS__, "Can't connect in database, please check the credentials in ./config.php", true);
            Winged::push_warning(__CLASS__, "Error: " . $this->db, true);
            Winged::convert_warnings_into_erros();
            Winged::get_errors(__LINE__, __FILE__);
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
        if (property_exists('WingedConfig', 'HOST')) {
            return WingedConfig::$HOST;
        }
        return false;
    }

    public function getRealUser($user = false)
    {
        if ($user !== false) {
            return $user;
        }
        if (property_exists('WingedConfig', 'USER')) {
            return WingedConfig::$USER;
        }
        return false;
    }

    public function getRealPassword($password = false)
    {
        if ($password !== false) {
            return $password;
        }
        if (property_exists('WingedConfig', 'PASSWORD')) {
            return WingedConfig::$PASSWORD;
        }
        return false;
    }

    public function getRealDbname($dbname = false)
    {
        if ($dbname !== false) {
            return $dbname;
        }
        if (property_exists('WingedConfig', 'DBNAME')) {
            return WingedConfig::$DBNAME;
        }
        return false;
    }

    public function getRealPort($port = false)
    {
        if ($port !== false) {
            return $port;
        }
        if (property_exists('WingedConfig', 'PORT')) {
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


class NormalMysqli
{
    /** @var $refer mysqli */
    private $refer = null;

    /** @var $last_stmt mysqli_stmt */
    public $last_stmt = null;

    /** @var $last_result mysqli_result */
    public $last_result = null;

    public function __construct(mysqli $db)
    {
        $this->refer = $db;
    }

    public function execute($query = '')
    {
        $stmt = $this->refer->query($query);
        $this->last_stmt = $stmt;
        return $stmt === true ? true : false;
    }

    public function insert($query = '')
    {
        $stmt = $this->refer->query($query);
        $this->last_stmt = $stmt;
        return $stmt === true ? $this->refer->insert_id : false;
    }

    public function fetch($query = '', $register_last = true)
    {
        $stmt = $this->refer->query($query);
        if ($register_last !== false) {
            $this->last_stmt = $stmt;
        }
        if ($stmt) {
            $tuple = [];
            while ($row = $stmt->fetch_assoc()) {
                $tuple[] = $row;
            }
            return empty($tuple) ? null : $tuple;
        }
        return null;
    }

    public function count($query = '')
    {
        if ($query === '') {
            return $this->refer->affected_rows !== -1 ? $this->refer->affected_rows : 0;
        }
        $stmt = $this->refer->query($query);
        if ($stmt !== false && $stmt !== null) {
            return $this->refer->affected_rows;
        }
        return null;
    }

    public function sp($param = '', $args = [], $register_last = false)
    {
        switch ($param) {
            case Database::SP_DESC_TABLE:
                if (array_key_exists('table_name', $args)) {
                    $result = $this->fetch('DESC ' . $args['table_name'], $register_last);
                    $desc = [];
                    foreach ($result as $field) {
                        $name = $field['Field'];
                        unset($field['Field']);
                        $desc[$name] = [];
                        foreach ($field as $key => $prop) {
                            $desc[$name][strtolower($key)] = $prop;
                        }
                    }
                    unset($result);
                    return $desc;
                }
                break;
            case Database::SP_SHOW_TABLES:
                $result = $this->fetch('SHOW TABLES', $register_last);
                $tables = [];
                foreach ($result as $table) {
                    $keys = array_keys($table);
                    $tables[] = $table[$keys[0]];
                }
                return $tables;
                break;
            default:
                return null;
                break;
        }
        return null;
    }

}

class PreparedMysqli
{

    /** @var $refer mysqli */
    private $refer = null;

    /** @var $last_stmt mysqli_stmt */
    public $last_stmt = null;

    /** @var $last_result mysqli_result */
    public $last_result = null;

    public function __construct(mysqli $db)
    {
        $this->refer = $db;
    }

    public function execute($query = '', $args = [])
    {
        $stmt = $this->refer->prepare($query);
        if ($stmt === false) {
            return $this->refer->error;
        }
        if ($this->bind_param($stmt, $args)) {
            $ret = false;
            try {
                $ret = $stmt->execute();
            } catch (mysqli_sql_exception $error) {
                Winged::push_warning(__CLASS__, "DB error: " . $error->getMessage(), true);
            }
            $this->last_stmt = $ret !== false && $ret !== null ? $stmt : false;
            return $ret !== false && $ret !== null ? true : false;
        }
        return false;
    }

    public function insert($query = '', $args = [])
    {
        $stmt = $this->refer->prepare($query);
        if ($stmt === false) {
            Winged::push_warning(__CLASS__, "DB error: can't prepare query - " . $query . ' : ' . $this->refer->error, true);
            return $this->refer->error;
        }

        if ($this->bind_param($stmt, $args)) {
            $ret = false;
            try {
                $ret = $stmt->execute();
            } catch (mysqli_sql_exception $error) {
                Winged::push_warning(__CLASS__, "DB error: " . $error->getMessage(), true);
            }
            $this->last_stmt = $ret !== false && $ret !== null ? $stmt : false;
            return $ret !== false ? $this->refer->insert_id : false;
        }
        return false;
    }

    public function fetch($query = '', $args = [], $register_last = true)
    {
        $stmt = $this->refer->prepare($query);
        if ($stmt === false) {
            Winged::push_warning(__CLASS__, "DB error: can't prepare query - " . $query . ' : ' . $this->refer->error, true);
            return $this->refer->error;
        }

        $cont = true;

        if ($stmt->param_count > 0) {
            $cont = $this->bind_param($stmt, $args);
        }

        if ($cont) {
            $ret = false;
            try {
                $ret = $stmt->execute();
            } catch (mysqli_sql_exception $error) {
                Winged::push_warning(__CLASS__, "DB error: " . $error->getMessage(), true);
            }
            if ($register_last) {
                $this->last_stmt = $ret !== false && $ret !== null ? $stmt : false;
            }
            if ($ret !== false) {
                $ret = $stmt->get_result();
                if ($ret !== false && $ret !== null) {
                    if ($register_last !== false) {
                        $this->last_result = $ret;
                    }
                    $tuple = [];
                    while ($row = $ret->fetch_assoc()) {
                        $tuple[] = $row;
                    }
                    return empty($tuple) ? null : $tuple;
                }
            }
        }
        return null;
    }

    public function count($query = '', $args = [])
    {
        $ret = false;

        if ($query === '') {
            return $this->last_result ? ($this->last_result->num_rows > 0 ? $this->last_result->num_rows : 0) : $this->last_stmt ? ($this->last_stmt->affected_rows > -1 ? $this->last_stmt->affected_rows : 0) : 0;
        }

        $stmt = $this->refer->prepare($query);
        if ($stmt === false) {
            Winged::push_warning(__CLASS__, "DB error: can't prepare query - " . $query, true);
            return $this->refer->error;
        }

        $cont = true;

        if ($stmt->param_count > 0) {
            $cont = $this->bind_param($stmt, $args);
        }

        if ($cont) {
            try {
                $stmt->execute();
                $this->last_stmt = $stmt;
                $ret = $stmt->get_result();
                if ($ret) {
                    $this->last_result = $ret;
                }
            } catch (mysqli_sql_exception $error) {
                Winged::push_warning(__CLASS__, "DB error: " . $error->getMessage(), true);
            }
            return $ret ? ($ret->num_rows > 0 ? $ret->num_rows : 0) : $stmt ? ($stmt->affected_rows > -1 ? $stmt->affected_rows : 0) : 0;
        }
        return 0;

    }

    public function sp($param = '', $args = [], $register_last = false)
    {
        switch ($param) {
            case Database::SP_DESC_TABLE:
                if (array_key_exists('table_name', $args)) {
                    $result = $this->fetch('DESC ' . $args['table_name'], [], $register_last);
                    $desc = [];
                    foreach ($result as $field) {
                        $name = $field['Field'];
                        unset($field['Field']);
                        $desc[$name] = [];
                        foreach ($field as $key => $prop) {
                            $desc[$name][strtolower($key)] = $prop;
                        }
                    }
                    unset($result);
                    return $desc;
                }
                break;
            case Database::SP_SHOW_TABLES:
                $result = $this->fetch('SHOW TABLES', [], $register_last);
                $tables = [];
                foreach ($result as $table) {
                    $keys = array_keys($table);
                    $tables[] = $table[$keys[0]];
                }
                return $tables;
                break;
            default:
                return null;
                break;
        }
        return null;
    }

    /**
     * @param $stmt mysqli_stmt
     * @param $args array
     * @return bool
     */
    private function bind_param(&$stmt, $args)
    {
        $ref = [];
        foreach ($args as $key => $arg) {
            $ref[] =& $args[$key];
        }
        return call_user_func_array([$stmt, 'bind_param'], $ref);
    }
}

class NormalPDO
{

    /** @var $refer PDO */
    private $refer = null;

    /** @var $last_stmt PDOStatement */
    public $last_stmt = null;

    /** @var $last_result PDOStatement */
    public $last_result = null;

    public function __construct(PDO $db)
    {
        $this->refer = $db;
    }

    public function execute($query = '')
    {
        $stmt = $this->refer->prepare($query);
        if ($stmt === false) {
            Winged::push_warning(__CLASS__, "DB error: can't prepare query - " . $query . ' : ' . $this->refer->errorInfo(), true);
            return $this->refer->errorInfo();
        } else {
            $ret = false;
            try {
                $ret = $stmt->execute();
            } catch (PDOException $error) {
                Winged::push_warning(__CLASS__, "DB error: " . $error->getMessage(), true);
            }
            $this->last_stmt = $ret !== false ? $stmt : false;
            return $ret !== false ? true : false;
        }
    }

    public function insert($query = '')
    {
        $stmt = $this->refer->prepare($query);
        if ($stmt === false) {
            Winged::push_warning(__CLASS__, "DB error: can't prepare query - " . $query . ' : ' . $this->refer->errorInfo(), true);
            return $this->refer->errorInfo();
        } else {
            $ret = false;
            try {
                $ret = $stmt->execute();
            } catch (PDOException $error) {
                Winged::push_warning(__CLASS__, "DB error: " . $error->getMessage(), true);
            }
            $this->last_stmt = $ret !== false ? $stmt : false;
            return $ret !== false ? $this->refer->lastInsertId() : false;
        }
    }

    public function fetch($query = '', $register_last = true)
    {
        $stmt = $this->refer->prepare($query);
        if ($stmt === false) {
            Winged::push_warning(__CLASS__, "DB error: can't prepare query - " . $query . ' : ' . $this->refer->errorInfo(), true);
            return $this->refer->errorInfo();
        } else {
            $ret = false;
            try {
                $ret = $stmt->execute();
            } catch (PDOException $error) {
                Winged::push_warning(__CLASS__, "DB error: " . $error->getMessage(), true);
            }
            if ($ret) {
                if ($register_last !== false) {
                    $this->last_stmt = $stmt;
                }
                $all = $stmt->fetchAll(PDO::FETCH_ASSOC);
                return empty($all) || $all === false ? null : $all;
            } else {
                return null;
            }
        }
    }

    public function count($query = '')
    {
        if ($query === '') {
            return $this->last_stmt !== null && $this->last_stmt !== false ? $this->last_stmt->rowCount() : 0;
        } else {
            $stmt = $this->refer->query($query);
            if ($stmt !== false && $stmt !== null) {
                $this->last_stmt = $stmt;
                return $stmt->rowCount();
            }
            return 0;
        }
    }

    public function sp($param = '', $args = [], $register_last = false)
    {
        switch ($param) {
            case Database::SP_DESC_TABLE:
                if (array_key_exists('table_name', $args)) {
                    $result = $this->fetch('DESC ' . $args['table_name'], $register_last);
                    $desc = [];
                    foreach ($result as $field) {
                        $name = $field['Field'];
                        unset($field['Field']);
                        $desc[$name] = [];
                        foreach ($field as $key => $prop) {
                            $desc[$name][strtolower($key)] = $prop;
                        }
                    }
                    unset($result);
                    return $desc;
                }
                break;
            case Database::SP_SHOW_TABLES:
                $result = $this->fetch('SHOW TABLES', $register_last);
                $tables = [];
                foreach ($result as $table) {
                    $keys = array_keys($table);
                    $tables[] = $table[$keys[0]];
                }
                return $tables;
                break;
            default:
                return null;
                break;
        }
        return null;
    }
}

class PreparedPDO
{

    /** @var $refer PDO */
    private $refer = null;

    /** @var $last_stmt PDOStatement */
    public $last_stmt = null;

    /** @var $last_result PDOStatement */
    public $last_result = null;

    public function __construct(PDO $db)
    {
        $this->refer = $db;
    }

    public function execute($query = '', $args = [])
    {
        $stmt = $this->refer->prepare($query);
        if ($stmt === false) {
            Winged::push_warning(__CLASS__, "DB error: can't prepare query - " . $query . ' : ' . $this->refer->errorInfo(), true);
            return $this->refer->errorInfo();
        } else {
            $ret = false;
            try {
                $ret = $stmt->execute($args);
            } catch (PDOException $error) {
                Winged::push_warning(__CLASS__, "DB error: " . $error->getMessage(), true);
            }
            $this->last_stmt = $ret !== false ? $stmt : false;
            return $ret !== false ? true : false;
        }
    }

    public function insert($query = '', $args = [])
    {
        $stmt = $this->refer->prepare($query);
        if ($stmt === false) {
            Winged::push_warning(__CLASS__, "DB error: can't prepare query - " . $query . ' : ' . $this->refer->errorInfo(), true);
            return $this->refer->errorInfo();
        } else {
            $ret = false;
            try {
                $ret = $stmt->execute($args);
            } catch (PDOException $error) {
                Winged::push_warning(__CLASS__, "DB error: " . $error->getMessage(), true);
            }
            $this->last_stmt = $ret !== false ? $stmt : false;
            return $ret !== false ? $this->refer->lastInsertId() : false;
        }
    }

    public function fetch($query = '', $args = [], $register_last = true)
    {
        $stmt = $this->refer->prepare($query);
        if ($stmt === false) {
            Winged::push_warning(__CLASS__, "DB error: can't prepare query - " . $query . ' : ' . $this->refer->errorInfo(), true);
            return $this->refer->errorInfo();
        } else {
            $ret = false;

            try {
                $ret = $stmt->execute($args);
            } catch (PDOException $error) {
                Winged::push_warning(__CLASS__, "DB error: " . $error->getMessage(), true);
            }
            if ($register_last !== false) {
                $this->last_stmt = $ret !== false ? $stmt : false;
            }

            if ($ret) {
                $all = $stmt->fetchAll(PDO::FETCH_ASSOC);

                return empty($all) || $all === false ? null : $all;
            } else {
                return null;
            }
        }
    }

    public function count($query = '', $args = [])
    {
        if ($query === '') {
            return $this->last_stmt !== null && $this->last_stmt !== false ? $this->last_stmt->rowCount() : 0;
        } else {
            $count = 0;
            $stmt = $this->refer->prepare($query);
            if ($stmt !== false && $stmt !== null) {
                $stmt->execute($args);
                if ($stmt->fetchColumn() > 0) {
                    foreach ($this->refer->query($query) as $row) {
                        $count++;
                    }
                }
                return $count;
            }
            Winged::push_warning(__CLASS__, "DB error: can't prepare query - " . $query . ' : ' . $this->refer->errorInfo(), true);
            return null;
        }
    }

    public function sp($param = '', $args = [], $register_last = false)
    {
        switch ($param) {
            case Database::SP_DESC_TABLE:
                if (array_key_exists('table_name', $args)) {
                    $result = $this->fetch('DESC ' . $args['table_name'], [], $register_last);
                    $desc = [];
                    foreach ($result as $field) {
                        $name = $field['Field'];
                        unset($field['Field']);
                        $desc[$name] = [];
                        foreach ($field as $key => $prop) {
                            $desc[$name][strtolower($key)] = $prop;
                        }
                    }
                    unset($result);
                    return $desc;
                }
                break;
            case Database::SP_SHOW_TABLES:
                $result = $this->fetch('SHOW TABLES', [], $register_last);
                $tables = [];
                foreach ($result as $table) {
                    $keys = array_keys($table);
                    $tables[] = $table[$keys[0]];
                }
                return $tables;
                break;
            default:
                return null;
                break;
        }
        return null;
    }
}