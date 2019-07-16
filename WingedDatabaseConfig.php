<?php

use Winged\WingedDatabaseConfigDefaults;

/**
 * Customize your application database config here
 *
 * Class WingedDatabaseConfig
 */
class WingedDatabaseConfig extends WingedDatabaseConfigDefaults{
    public $USE_DATABASE = true;
    public $VALIDATE_MODELS = true;
    public $DB_DRIVER = DB_DRIVER_MYSQL;
    public $USE_PREPARED_STMT = USE_PREPARED_STMT;
    public $STD_DB_CLASS = IS_MYSQLI;
    public $HOST = "localhost";
    public $USER = "root";
    public $DBNAME = "test";
    public $PASSWORD = "";
    public $SCHEMA = "";
}