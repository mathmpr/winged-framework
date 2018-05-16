<?php
define("DOCUMENT_ROOT", str_replace("\\", "/", dirname(__FILE__) . "/"));
include_once "./Winged/winged.class.php";
\Winged\Winged::start();