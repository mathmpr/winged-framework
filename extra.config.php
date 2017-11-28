<?php

Container::$self->attach('whenControllerNotFound', function(){
    return false;
});

Container::$self->attach('beforeSearchController', function(){
    return null;
});