<?php

use Illuminate\Support\Facades\Route;
use IpCountryDetector\Http\Controllers\IPCheckController;

Route::match(['get', 'post'], config('ipcountry.route'), [IPCheckController::class, 'checkIP']);