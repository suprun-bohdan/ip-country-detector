<?php

namespace IpCountryDetector\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use IpCountryDetector\Services\IPCheckService;

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
        $ipAddress = $request->input('ip')
            ?? $request->header('CF-Connecting-IP')
            ?? $request->ip();

        $timeZone = $request->input('timezone', 'UTC');

        try {
            $country = $ipAddress
                ? $this->ipCheckService->ipToCountry($ipAddress)
                : throw new \InvalidArgumentException('IP address is required');
        } catch (\Exception $e) {
            Log::warning("IP to Country failed, switching to timezone: {$e->getMessage()}");
            $country = $this->ipCheckService->timeZoneToCountry($timeZone);
        }

        if (empty($ipAddress) || $ipAddress === '127.0.0.1') {
            return ['timezone' => $timeZone, 'country' => $country];
        }

        return ['ip' => $ipAddress ?? 'Unknown IP', 'country' => $country];
    }



    /**
     * @throws Exception
     */
    public function checkIPFromEntry($ipAddress): ?string
    {
        if ($ipAddress == '127.0.0.1' || $ipAddress == '::1') {
            return null;
        }
        $country = $this->ipCheckService->ipToCountrySimple($ipAddress);

        if (empty($country)) {
            return null;
        }

        return $country;
    }
}
