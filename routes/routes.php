<?php
use Winged\Route\Route;

Route::post('./create/token/', [
    'expires' => 3600
]);

Route::post('./create/token/credentials/', [
    'expires' => 7200
])->credentials('matheusprador@gmail.com', 'pokemon');

Route::get('./users/{user_id}/comments/{limit?}/', "Usuarios@letsTry")->where([
    'user_id' => '\d'
])->credentials('matheusprador@gmail.com', 'pokemon')->session();