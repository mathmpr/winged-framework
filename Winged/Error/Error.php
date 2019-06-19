<?php

namespace Winged\Error;

use Winged\Buffer\Buffer;
use Winged\Utils\WingedLib;
use Winged\Winged;

register_shutdown_function(["Winged\Error\ShutdownCallback", "shutdownHandler"]);

set_error_handler(["Winged\Error\ShutdownCallback", "errorHandler"], E_ALL);


/**
 * Class Error
 *
 * @package Winged\Error
 */
class Error
{
    public static $errors = [];

    /**
     * delete all errors
     */
    public static function clear()
    {
        self::$errors = [];
    }

    /**
     * check error count
     *
     * @return int
     */
    public static function errorCount()
    {
        return count7(self::$errors);
    }

    /**
     * delete last error
     *
     * @return mixed
     */
    public static function deleteLastError()
    {
        if (self::errorCount() > 0) {
            return array_pop(self::$errors);
        }
    }

    /**
     * push a error Error class
     *
     * @param      $errno
     * @param      $errstr
     * @param      $errfile
     * @param      $errline
     * @param bool $errcontext
     */
    public static function push($errno, $errstr, $errfile, $errline, $errcontext = false)
    {
        self::$errors[] = [
            "error_type" => $errno,
            "error_str" => $errstr,
            "error_file" => $errfile,
            "error_line" => $errline,
            "error_trace" => $errcontext
        ];
    }

    /**
     * check if errors exists
     *
     * @return array|bool
     */
    public static function exists()
    {
        if (is_array(self::$errors) && count7(self::$errors)) {
            return self::$errors;
        }
        return false;
    }

    /**
     * register a new error in error stack with E_USER_ERROR type
     *
     * @param string $message
     */
    public static function error($message = "")
    {
        trigger_error($message, E_USER_ERROR);
    }

    /**
     * register a new error in error stack with E_USER_WARNING type
     *
     * @param string $message
     */
    public static function warning($message = "")
    {
        trigger_error($message, E_USER_WARNING);
    }

    /**
     * register a new error in error stack with E_USER_NOTICE type
     *
     * @param string $message
     */
    public static function notice($message = "")
    {
        trigger_error($message, E_USER_NOTICE);
    }

    /**
     * if a error, notice and warning was occured during script execution, drop current buffer and display all into custom and friendly table
     *
     * @param bool $exit
     */
    public static function display($exit = true)
    {
        if (self::exists()) {
            Buffer::reset();
            ?>
            <html>
            <head>
                <base href="<?= Winged::$protocol . "Winged/" ?>">
                <title>Stack Trace</title>
                <meta charset="utf-8"/>
                <link href="Error/assets/winged.error.css" rel="stylesheet" type="text/css"/>
                <meta name="viewport" content="width=device-width,user-scalable=0,initial-scale=1"/>
                <link rel="icon" href="Error/assets/favicon.png"/>
            </head>
            <body>
            <?php
            ob_start();
            ?>
            <table>
                <thead>
                <tr>
                    <th style="width: 100px;">Error type</th>
                    <th class="amp">Error description</th>
                    <th style="width: 300px;">Error in file</th>
                    <th style="width: 100px;">Error on line</th>
                </tr>
                </thead>
                <tbody>
                <?php
                $ignored = [];
                $showing = 0;
                foreach (\WingedConfig::$config->IGNORE_ERRORS as $error) {
                    $ignored[] = ShutdownCallback::$errors[$error];
                }
                foreach (self::$errors as $key => $error) {
                    if (!in_array($error["error_type"], $ignored) && !in_array(E_ALL, \WingedConfig::$config->IGNORE_ERRORS)) {
                        $showing++;
                        ?>
                        <tr>
                            <td style="width: 100px;"><?= $error["error_type"] ?></td>
                            <td class="amp <?= empty($error['error_trace']) ? 'pad' : '' ?>">
                                <?= $error["error_str"] ?>
                                <?php
                                if (!empty($error['error_trace'])) {
                                    ?>
                                    <p><b>Stack trace: </b></p>
                                    <table>
                                        <thead>
                                        <tr>
                                            <th>Error in file</th>
                                            <th>Call in line</th>
                                            <th>Class</th>
                                            <th>Function</th>
                                            <th>Type</th>
                                        </tr>
                                        </thead>
                                        <?php
                                        foreach ($error["error_trace"] as $key => $array) {
                                            ?>
                                            <tr>
                                                <td><?= array_key_exists('file', $array) ? str_replace(DOCUMENT_ROOT, './', WingedLib::convertslash($array["file"])) : '[internal function]' ?></td>
                                                <td><?= array_key_exists('line', $array) ? $array["line"] : 'internal' ?></td>
                                                <td><?= array_key_exists('class', $array) ? $array["class"] : 'procedural call' ?></td>
                                                <td><?= $array["function"] ?></td>
                                                <td><?= $array["type"] == '::' ? 'static' : 'normal' ?></td>
                                            </tr>
                                            <?php
                                        }
                                        ?>
                                    </table>
                                    <?php
                                }
                                ?>
                            </td>
                            <td style="width: 300px;"><?= str_replace(DOCUMENT_ROOT, './', WingedLib::convertslash($error["error_file"])) ?></td>
                            <td style="width: 100px;"><?= $error["error_line"] ?></td>
                        </tr>
                        <?php
                    }
                }
                ?>
                </tbody>
            </table>
            <?php
            $table = ob_get_contents();
            ob_end_clean();
            if ($showing === 0) {
                ?>
                <p class="showing">If you are seeing this, it is because your PHP script contains errors. However, the
                    types of errors
                    in your script may be within the ignored types. Open <span>./config.php</span>, go to <span><em
                                class="o">public</em> <em class="p_">$IGNORE_ERRORS</em></span> and
                    check ignored types.</p>

                <p class="showing">Otherwise you can disable debugging altogether. But this does not guarantee the
                    expected result in
                    case of <span>E_ERROR</span>, <span>E_PARSE</span>, <span>E_CORE_ERROR</span>,
                    <span>E_COMPILE_ERROR</span>, <span>E_USER_ERROR</span>, <span>E_RECOVERABLE_ERROR</span>.</p>
                <?php
            } else {
                echo $table;
            }
            ?>
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