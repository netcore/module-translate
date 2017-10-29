<?php
use Illuminate\Routing\Router;

Route::group([
    'middleware' => ['web', 'auth.admin'],
    'namespace'  => null,
    'prefix'     => 'admin',
    'as'         => 'admin.'
], function (Router $router) {
    \Netcore\Translator\Router::adminRoutes($router);
});


Route::group(['middleware' => 'web', 'prefix' => 'translate', 'namespace' => 'Modules\Translate\Http\Controllers'], function()
{
    Route::get('/', 'TranslateController@index');
});
