<?php

use Illuminate\Support\Facades\Route;
use wtg\IpCountryDetector\Http\Middleware\IpAuthorization;

Route::middleware(IpAuthorization::class)
    ->namespace('wtg\IpCountryDetector\Http\Controllers')
    ->group(function () {
        Route::get(config('ipcountry.route'), 'IPCheckController@checkIP');
    });