<?php

namespace IpCountryDetector\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use IpCountryDetector\Services\IPCheckService;
use Illuminate\Http\JsonResponse;

class IPCheckController extends Controller
{
    private IPCheckService $ipCheckService;

    public function __construct(IPCheckService $ipCheckService)
    {
        $this->ipCheckService = $ipCheckService;
    }

    /**
     * @throws Exception
     */
    public function checkIP(Request $request): array
    {
        $ipAddress = $request->input('ip');
        $country = $this->ipCheckService->ipToCountry($ipAddress);

        return ['ip' => $ipAddress, 'country' => $country];
    }

    /**
     * @throws Exception
     */
    public function checkIPFromEntry($ipAddress): ?string
    {
        if ($ipAddress == '127.0.0.1' || $ipAddress == '::1') {
            return 'localhost';
        }
        $country = $this->ipCheckService->ipToCountrySimple($ipAddress);

        if (empty($country)) {
            return null;
        }

        return $country;
    }
}
