<?php

use Illuminate\Support\Facades\Route;
use wtg\IpCountryDetector\Http\Controllers\IPCheckController;

Route::post('/check-ip', [IPCheckController::class, 'checkIP']);
