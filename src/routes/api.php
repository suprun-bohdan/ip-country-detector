<?php

use Illuminate\Support\Facades\Route;
use wtg\IpCountryDetector\Http\Controllers\IPCheckController;

Route::get('/api/check-ip', [IPCheckController::class, 'checkIP']);
