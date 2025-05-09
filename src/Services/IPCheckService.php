<?php

namespace IpCountryDetector\Services;

use Exception;
use HttpException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use function Laravel\Prompts\error;
use IpCountryDetector\Services\Interfaces\IPCountryServiceInterface;
use IpCountryDetector\Services\Interfaces\CacheServiceInterface;
use InvalidArgumentException;
use RuntimeException;

class IPCheckService implements IPCountryServiceInterface
{
    private const CACHE_TTL = 86400; // 24 hours
    private const VPN_PROVIDERS = [
        'vpn', 'proxy', 'tor', 'hosting'
    ];

    private IPCacheService $ipCacheService;
    private IpApiService $ipApiService;

    public function __construct(
        private readonly CacheServiceInterface $cacheService,
        private readonly IpApiService $ipApiService
    )
    {
        $this->ipCacheService = $ipCacheService;
        $this->ipApiService = $ipApiService;
    }

    /**
     * @throws Exception
     */
    public function getCountryByIp(string $ipAddress): string
    {
        try {
            if (!$this->validateIpAddress($ipAddress)) {
                throw new InvalidArgumentException('Invalid IP address format');
            }

            $cacheKey = $this->getCacheKey($ipAddress);
            if ($cachedCountry = $this->cacheService->get($cacheKey)) {
                return $cachedCountry;
            }

            $country = $this->findCountryInDatabase($ipAddress);
            if ($country) {
                $this->cacheService->set($cacheKey, $country, self::CACHE_TTL);
                return $country;
            }

            $country = $this->ipApiService->getCountry($ipAddress);
            if ($country !== 'Country not found') {
                $this->cacheService->set($cacheKey, $country, self::CACHE_TTL);
                return $country;
            }

            throw new RuntimeException('Country not found for IP address: ' . $ipAddress);
        } catch (Exception $e) {
            Log::error('IP to Country Error', [
                'ip' => $ipAddress,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    public function getCountryByTimezone(string $timezone): ?string
    {
        try {
            $timezoneObj = new \DateTimeZone($timezone);
            $location = $timezoneObj->getLocation();
            return $location['country_code'] ? strtoupper($location['country_code']) : null;
        } catch (Exception $e) {
            Log::warning('Timezone to Country Error', [
                'timezone' => $timezone,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    public function isVpnIp(string $ipAddress): bool
    {
        try {
            $ipInfo = $this->ipApiService->getIpInfo($ipAddress);
            return $this->checkVpnIndicators($ipInfo);
        } catch (Exception $e) {
            Log::warning('VPN Detection Error', [
                'ip' => $ipAddress,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    public function validateIpAddress(string $ipAddress): bool
    {
        return filter_var($ipAddress, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6) !== false;
    }

    private function findCountryInDatabase(string $ipAddress): ?string
    {
        $ipLong = $this->ipToLong($ipAddress);
        if ($ipLong === false) {
            return null;
        }

        return DB::table('ip_country')
            ->where('first_ip', '<=', $ipLong)
            ->where('last_ip', '>=', $ipLong)
            ->value('country');
    }

    private function ipToLong(string $ipAddress): int|false
    {
        if (filter_var($ipAddress, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            return ip2long($ipAddress);
        }
        
        if (filter_var($ipAddress, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            return $this->ipv6ToLong($ipAddress);
        }

        return false;
    }

    private function ipv6ToLong(string $ipv6): int
    {
        $ipv6Long = inet_pton($ipv6);
        if ($ipv6Long === false) {
            return false;
        }
        
        $bin = '';
        for ($i = 0; $i < 16; $i++) {
            $bin .= sprintf('%08b', ord($ipv6Long[$i]));
        }
        
        return bindec($bin);
    }

    private function getCacheKey(string $ipAddress): string
    {
        return config('ipcountry.redis.prefix') . '::' . $ipAddress;
    }

    private function checkVpnIndicators(array $ipInfo): bool
    {
        $indicators = array_merge(
            array_column(self::VPN_PROVIDERS, 'strtolower'),
            ['hosting', 'datacenter']
        );

        $hostname = strtolower($ipInfo['hostname'] ?? '');
        $org = strtolower($ipInfo['org'] ?? '');
        $isp = strtolower($ipInfo['isp'] ?? '');

        foreach ($indicators as $indicator) {
            if (str_contains($hostname, $indicator) ||
                str_contains($org, $indicator) ||
                str_contains($isp, $indicator)) {
                return true;
            }
        }

        return false;
    }
}
