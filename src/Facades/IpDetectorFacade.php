<?php
namespace Vendor\IpDetector\Facades;

use Exception;
use Illuminate\Support\Facades\Facade;
use IpCountryDetector\Http\Controllers\IPCheckController;

class IpDetectorFacade extends Facade
{
    /**
     * @throws Exception
     */
    protected static function getFacadeAccessor(): string
    {
        return 'ip-detector';
    }
}