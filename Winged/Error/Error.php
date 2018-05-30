<?php

namespace Winged\Error;

use Winged\Buffer\Buffer;
use Winged\Utils\WingedLib;

register_shutdown_function(["Winged\Error\ShutdownCallback", "shutdownHandler"]);

set_error_handler(["Winged\Error\ShutdownCallback", "errorHandler"], E_ALL);

/**
 * Class Error
 * @package Winged\Error
 */
class Error
{

    private static $ignore_errors = ["E_DEPRECATED"];
    public static $errors = [];

    private static function protocol()
    {
        $self = WingedLib::convertslash(server("php_self"));
        $host = WingedLib::convertslash(server("server_name"));
        $selfs = WingedLib::slashexplode($self);

        $self = "";
        for ($x = 0; $x < count($selfs); $x++) {
            if ($selfs[$x] != "index.php") {
                if ($x == 0) {
                    $self = $selfs[$x];
                } else {
                    $self .= "/" . $selfs[$x];
                }
            }
        }

        if ($self == "") {
            $https = "https://" . $host . "/";
            $http = "http://" . $host . "/";
        } else {
            $https = "https://" . $host . "/" . $self . "/";
            $http = "http://" . $host . "/" . $self . "/";
        }

        if (server('https')) {
            if (server('https') != 'off') {
                return $https;
            }
        }
        return $http;
    }

    public static function _die($description = '', $call = false, $file = false, $line = false)
    {
        Buffer::reset();
        ?>
        <html>
        <head>
            <base href="<?= self::protocol() . "winged/" ?>">
            <title>Trace error</title>
            <meta charset="utf-8"/>
            <link href="error/assets/winged.error.css?get=<?= time() ?>" rel="stylesheet" type="text/css"/>
            <meta name="viewport" content="width=device-width,user-scalable=0,initial-scale=1"/>
            <link rel="icon" href="assets/img/fav.png"/>
            <style>
                td, th {
                    width: 11%;
                    word-wrap: break-word;
                    word-break: break-all;
                }
            </style>
        </head>
        <body>
        <table>
            <thead>
            <tr>
                <th>Error type</th>
                <th class="amp">Error description</th>
                <th>Called in line</th>
                <th class="amp">Error in file</th>
                <th>Error on line</th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td>Die | Fatal</td>
                <td class="amp"><?= $description ?></td>
                <td><?= $call ? $call : 'undefined' ?></td>
                <td class="amp"><?= $file ? $file : 'undefined' ?></td>
                <td><?= $line ? $line : 'undefined' ?></td>
            </tr>
            </tbody>
        </table>
        </body>
        </html>
        <?php
        Buffer::flush();
        exit;
    }

    public static function clear()
    {
        self::$errors = [];
    }

    /**
     * @return int
     */
    public static function errorCount(){
        return count(self::$errors);
    }

    /**
     * @return mixed
     */
    public static function deleteLastError(){
        if(self::errorCount() > 0){
            return array_pop(self::$errors);
        }
    }

    public static function push($errno, $errstr, $errfile, $errline, $errcontext = false)
    {
        if (!in_array($errno, self::$ignore_errors)) {
            self::$errors[] = [
                "error_type" => $errno,
                "error_str" => $errstr,
                "error_file" => $errfile,
                "error_line" => $errline
            ];
        }
    }

    public static function exists()
    {
        if (is_array(self::$errors) && count(self::$errors)) {
            return self::$errors;
        }
        return false;
    }

    public static function display($line, $file, $exit = true)
    {
        if (self::exists()) {
            Buffer::reset();
            ?>
            <html>
            <head>
                <base href="<?= self::protocol() . "winged/" ?>">
                <title>Trace error</title>
                <meta charset="utf-8"/>
                <link href="error/assets/winged.error.css?get=<?= time() ?>" rel="stylesheet" type="text/css"/>
                <meta name="viewport" content="width=device-width,user-scalable=0,initial-scale=1"/>
                <link rel="icon" href="assets/img/fav.png"/>
                <style>
                    td, th {
                        width: 11%;
                        word-wrap: break-word;
                        word-break: break-all;
                    }
                </style>
            </head>
            <body>
            <table>
                <thead>
                <tr>
                    <th>Break occurs in</th>
                    <th>File</th>
                    <th>Line</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td style="width: 10%;"></td>
                    <td style="width: 45%;">File: <?= $file ?></td>
                    <td style="width: 45%;">Line: <?= $line ?></td>
                </tr>
                </tbody>
            </table>
            <table>
                <thead>
                <tr>
                    <th>Error type</th>
                    <th class="amp">Error description</th>
                    <th>Called in line</th>
                    <th class="amp">Error in file</th>
                    <th>Error on line</th>
                </tr>
                </thead>
                <tbody>
                <?php
                foreach (self::$errors as $key => $error) {
                    if (!in_array($error["error_type"], self::$ignore_errors)) {
                        ?>
                        <tr>
                            <td><?= $error["error_type"] ?></td>
                            <td class="amp"><?= $error["error_str"] ?></td>
                            <td><?= $error["error_line"] ?></td>
                            <td class="amp"><?= $error["error_file"] ?></td>
                            <td><?= $error["error_line"] ?></td>
                        </tr>
                        <?php
                    }
                }
                ?>
                </tbody>
            </table>
            </body>
            </html>
            <?php
            Buffer::flush();
            if ($exit) {
                exit;
            }
        }
    }
}