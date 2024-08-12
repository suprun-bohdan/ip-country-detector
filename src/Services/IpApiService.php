<?php

namespace wtg\IpCountryDetector\Services;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use JetBrains\PhpStorm\NoReturn;

class IpApiService
{
    public function getCountry(string $ipAddress): string
    {

        /*
        try {
            $data = $this->fetchFromIpApi($ipAddress);
        } catch (Exception $e) {
            $data = $this->fetchFromCleanTalk($ipAddress);
        }
        */

        $data = $this->fetchFromCleanTalk($ipAddress);

        if (!isset($data['countryCode'])) {
            return response()->json(['error' => 'Unable to determine country'], 404);
        }

        return $data['countryCode'];
    }

    private function fetchFromIpApi(string $ipAddress): array
    {
        $response = Http::get("https://ip-api.com/php/$ipAddress");

        $data = unserialize($response->body());


        if ($response->ok()) {
            $data = unserialize($response->body());
            return $data['countryCode'];
        }

        return ['error' => 'Country not found'];
    }

    private function fetchFromCleanTalk(string $ipAddress): array
    {
        $response = Http::get("https://api.cleantalk.org/?method_name=ip_info&ip=$ipAddress");

        $data = $response->json();

        return [
            'countryCode' => $data['data'][$ipAddress]['country_code'] ?? 'Country not found'
        ];
    }
}
