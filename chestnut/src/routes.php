<?php

Route::prefix('api')
    ->middleware('api')
    ->namespace('Chestnut\Auth')
    ->group(function () {
        Route::post('login', "LoginController@apiLogin");
        Route::post('micro_login', "LoginController@microappLogin");
    });

Route::get("/login", function () {
    return view('chestnut::dashboard');
});

Route::get(env("CHESTNUT_PATH_PREFIX", "admin") . "{any}", function () {
    return view('chestnut::dashboard');
})->where('any', '(?!api).*');

Route::prefix('api')->middleware('api', 'auth:api')->get('/options', function () {
    return ['code' => 200, 'message' => 'request success', 'data' => app('shell')];
});
