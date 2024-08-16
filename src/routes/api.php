<?php

use Illuminate\Support\Facades\Route;
use IpCountryDetector\Http\Controllers\IPCheckController;

Route::middleware(config('ipcountry.middleware'))
    ->group(function () {
        Route::get(config('ipcountry.route'), [IPCheckController::class, 'checkIP']);
    });