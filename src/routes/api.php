<?php

use Illuminate\Support\Facades\Route;
use IpCountryDetector\Http\Controllers\IPCheckController;

Route::middleware(config('ipcountry.middleware'))
    ->group(function () {
        Route::match(['get', 'post'], config('ipcountry.route'), [IPCheckController::class, 'checkIP']);
    });