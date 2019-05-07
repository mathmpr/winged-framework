<?php

namespace Winged\Database\Drivers;

use Winged\Database\Database;

/**
 * Class DriverMiddleware
 *
 * @package Winged\Database\Drivers
 */

abstract class DriverMiddleware{

    /**
     * @var $database null | Database
     */
    protected $database = null;

    /**
     * DriverMiddleware constructor.
     *
     * @param Database $database
     */
    public function __construct($database)
    {
        $this->database = $database;
    }

}