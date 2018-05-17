<?php
define("DOCUMENT_ROOT", str_replace("\\", "/", dirname(__FILE__) . "/"));
include_once "./Winged/Winged.php";
\Winged\Winged::start();