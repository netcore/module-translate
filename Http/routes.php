<?php

Route::group(['middleware' => 'web', 'prefix' => 'translate', 'namespace' => 'Modules\Translate\Http\Controllers'], function()
{
    Route::get('/', 'TranslateController@index');
});
