<?php



\Winged\Utils\Container::$self->attach('whenControllerNotFound', function(){
    return false;
});

\Winged\Utils\Container::$self->attach('beforeSearchController', function(){
    return null;
});