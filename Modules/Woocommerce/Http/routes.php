<?php

Route::group(['middleware' => ['web', 'SetSessionData', 'auth', 'language', 'timezone'], 'prefix' => 'woocommerce', 'namespace' => 'Modules\Woocommerce\Http\Controllers'], function()
{
    Route::get('/install', 'InstallController@index');
    Route::get('/install/update', 'InstallController@update');
    
    Route::get('/', 'WoocommerceController@index');
    Route::get('/api-settings', 'WoocommerceController@apiSettings');
    Route::post('/update-api-settings', 'WoocommerceController@updateSettings');
    Route::get('/sync-categories', 'WoocommerceController@syncCategories');
    Route::get('/sync-products', 'WoocommerceController@syncProducts');
    Route::get('/sync-log', 'WoocommerceController@getSyncLog');
    Route::get('/sync-orders', 'WoocommerceController@syncOrders');
    Route::post('/map-taxrates', 'WoocommerceController@mapTaxRates');
    Route::get('/view-sync-log', 'WoocommerceController@viewSyncLog');
    Route::get('/get-log-details/{id}', 'WoocommerceController@getLogDetails');
});
