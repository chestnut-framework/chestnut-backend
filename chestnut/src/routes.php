<?php

Route::prefix('api/auth')
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

Route::prefix('api')->middleware('api', 'auth:chestnut')->get('/options', function () {
    return ['code' => 200, 'message' => 'request success', 'data' => app('shell')];
});

Route::prefix('api')->middleware('api')->get('/settings', function () {
    $data = [];
    if (!empty(Auth::guard("chestnut")->user())) {
        $data = app("shell")->jsonSerialize();
    }

    $data = array_merge($data, [
        "appName"     => env("APP_NAME", "CHESTNUT"),
        "description" => env("DESCRIPTION", "Chestnut Resource Manage System"),
        "routePrefix" => env("CHESTNUT_ROUTE_PREFIX", ""),
    ]);

    return ['code' => 200, 'message' => 'request success', 'data' => $data];
});
