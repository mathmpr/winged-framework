<?php
define("DOCUMENT_ROOT", str_replace("\\", "/", dirname(__FILE__) . "/"));
include_once "./winged/winged.class.php";
Winged::start();