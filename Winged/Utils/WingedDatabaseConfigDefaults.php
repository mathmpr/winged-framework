<?php

namespace Winged;



/**
 * This file is responsible for the general configuration of the framework
 * all the properties in it are defined and exist. You are free to create
 * constants and properties within the WingedConfig class to use in your
 * project globally at a later time. You can also override the properties
 * within other config.php files. These files must be created inside
 * other directories if you want to overwrite some properties at runtime.
 */
class WingedDatabaseConfigDefaults
{
    /**
     * @property $DATABASE_CHARSET string
     * set charset for database names
     */
    public $DATABASE_CHARSET = "utf8";

    /**
     * @property $USE_DATABASE bool
     * on | off mysql extensions and all class. Inflicts DelegateQuery, AbstractEloquent, CurrentDB, Connections, Database, DbDict, Models and Migrate class
     */
    public $USE_DATABASE = false;

    /**
     * @property $USE_PREPARED_STMT bool
     * on | off prepared statements
     * view more of prepared statements in
     * <your_domain_name>/winged/what_is_prepared_statement
     */
    public $USE_PREPARED_STMT = USE_PREPARED_STMT;

    /**
     * @property $DB_DRIVER string
     * defines what type of database your project will use.
     * if your server does not support the PDO class.
     * only mysql will be available for use. To see the availability of classes and functions of your server,
     * go to <your_domain_name>/winged/available#database
     */
    public $DB_DRIVER = DB_DRIVER_MYSQL;

    /**
     * @property $STD_DB_CLASS string
     * defines which class will be used for the interaction between PHP and the database
     */
    public $STD_DB_CLASS = IS_PDO;

    /**
     * @property $HOST string
     * defines default server name for mysql connection
     */
    public $HOST = '';

    /**
     * @property $USER string
     * default user name for mysql connection
     */
    public $USER = '';

    /**
     * @property $DBNAME string
     * default database name for mysql connection
     */
    public $DBNAME = '';

    /**
     * @property $PASSWORD string
     * default password for mysql connection
     */
    public $PASSWORD = '';

    /**
     * @property $SCHEMA string
     * default password for mysql connection
     */
    public $SCHEMA = '';

    /**
     * @property $PORT int
     * default password for mysql connection
     */
    public $PORT = 3306;

    /**
     * @var \WingedConfig | null
     */
    private $generalConfigs = null;

    /**
     * WingedDatabaseConfigDefaults constructor.
     *
     * @param \WingedConfig | WingedConfigDefaults $generalConfigs
     */
    public function __construct($generalConfigs)
    {
        $this->generalConfigs = $generalConfigs;
    }

    /**
     * return an access to general configs
     *
     * @return WingedConfigDefaults|\WingedConfig|null
     */
    public function config(){
        return $this->generalConfigs;
    }

}