<?php

use Winged\Route\Route;

Route::get('./examples/users/', function(){
    return [
        'id' => 1,
        'name' => 'Matheus Prado Rodrigues'
    ];
});