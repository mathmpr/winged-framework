<?php

use Winged\Route\Route;

Route::get('./json/', function(){
    return [
        'foo' => 'Hello',
        'bar' => 'World'
    ];
});