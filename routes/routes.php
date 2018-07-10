<?php
use Winged\Winged;

Winged::addRoute("./home/", [
	"index" => "./views/home.php",
]);

Winged::addRest('./home/', [

]);

