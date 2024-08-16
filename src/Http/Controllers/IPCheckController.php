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
    public function checkIP(Request $request): JsonResponse
    {
        $ipAddress = $request->input('ip');
        $country = $this->ipCheckService->ipToCountry($ipAddress);

        return response()->json(['ip' => $ipAddress, 'country' => $country]);
    }
}
