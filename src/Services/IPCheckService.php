<?php

namespace IpCountryDetector\Services;

use Exception;
use HttpException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use function Laravel\Prompts\error;

class IPCheckService
{
    private IPCacheService $ipCacheService;
    private IpApiService $ipApiService;

    public function __construct(IPCacheService $ipCacheService, IpApiService $ipApiService)
    {
        $this->ipCacheService = $ipCacheService;
        $this->ipApiService = $ipApiService;
    }

    /**
     * @throws Exception
     */
    public function ipToCountry(string $ipAddress): string
    {
        try {
            $cachedCountry = $this->ipCacheService->getCountryFromCache($ipAddress);
            if ($cachedCountry) {
                return $cachedCountry;
            }

            $ipLong = ip2long($ipAddress);
            if ($ipLong === false) {
                throw new \InvalidArgumentException('Invalid IP address');
            }

            $country = $this->findCountryByIp($ipLong);
            if ($country !== "IP Address not found in the range.") {
                $this->ipCacheService->setCountryToCache($ipAddress, $country);
                return $country;
            }

            $country = $this->fetchCountryFromApi($ipAddress);
            if ($country !== 'Country not found') {
                $this->ipCacheService->setCountryToCache($ipAddress, $country);
                return $country;
            }

            throw new \RuntimeException('Country not found for this IP address');
        } catch (\Exception $e) {
            Log::error("IP to Country Error: {$e->getMessage()}");
            throw new HttpException(500, 'Internal Server Error');
        }
    }

    public function timeZoneToCountry(string $timeZone): ?string
    {
        try {
            $timezone = new \DateTimeZone($timeZone);
            $region = $timezone->getLocation();

            return strtoupper($region['country_code']);
        } catch (\Exception $e) {
            return 'Unknown';
        }
    }

    public function ipToCountrySimple($ipAddress)
    {
        if (empty($ipAddress) || ($ipLong = ip2long($ipAddress)) === false) {
            return;
        }

        $country = $this->findCountryByIp($ipLong);

        if ($country !== "IP Address not found in the range.") {
            return $country;
        }

        $country = $this->fetchCountryFromApi($ipAddress);

        return $country != 'Country not found' ? $country : null;
    }

    private function findCountryByIp(int $ipLong): string
    {
        $result = DB::table('ip_country')
            ->where('first_ip', '<=', $ipLong)
            ->where('last_ip', '>=', $ipLong)
            ->select('country')
            ->first();

        if ($result) {
            return $result->country;
        }

        return "IP Address not found in the range.";
    }

    private function fetchCountryFromApi(string $ipAddress): string
    {
        return $this->ipApiService->getCountry($ipAddress);
    }
}
