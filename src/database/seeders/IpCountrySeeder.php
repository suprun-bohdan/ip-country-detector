<?php

namespace IpCountryDetector\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use IpCountryDetector\Models\IpCountry;
use IpCountryDetector\Services\CsvFilePathService;
use Symfony\Component\Console\Output\ConsoleOutput;
use Throwable;

class IpCountrySeeder extends Seeder
{
    protected CsvFilePathService $csvFilePathService;

    /**
     * Run the database seeds.
     *
     * @return void
     * @throws Throwable
     */

    public function __construct(CsvFilePathService $csvFilePathService)
    {
        $this->csvFilePathService = $csvFilePathService;
    }
    public function run(): void
    {
        IpCountry::truncate();
        $this->logMessage('info', "Table 'ip_country' has been cleared.");

        Artisan::call('migrate');
        $this->logMessage('info', "Database migrations have been run.");

        $csvFilePath = $this->csvFilePathService->getCsvFilePath();
        $this->logMessage('info', "CSV file path: $csvFilePath");
        sleep(5);

        if (!$handle = fopen($csvFilePath, 'r')) {
            $this->logMessage('error', "Unable to open CSV file: $csvFilePath");
            return;
        }

        try {
            $dataRows = [];
            $batchSize = 1000;
            $rowCount = 0;

            fgetcsv($handle, 1000, ",");

            $this->logMessage('info', "Starting CSV import in batches...");

            while (($data = fgetcsv($handle, 1000, ",")) !== false) {
                [$firstIp, $lastIp, $country, $region, $subregion, $city, , $latitude, $longitude, $timezone] = $data;

                $dataRows[] = [
                    'first_ip' => $this->convertIpToNumeric($firstIp),
                    'last_ip' => $this->convertIpToNumeric($lastIp),
                    'country' => $country,
                    'region' => $region,
                    'subregion' => $subregion,
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                    'city' => $city,
                    'timezone' => $timezone,
                ];

                $rowCount++;

                if (count($dataRows) >= $batchSize) {
                    IpCountry::insertOrIgnore($dataRows);
                    $dataRows = [];

                    $this->logMessage('info', "Inserted {$rowCount} records so far...");
                }
            }

            if (!empty($dataRows)) {
                IpCountry::insertOrIgnore($dataRows);
                $this->logMessage('info', "Final batch inserted. Total records: {$rowCount}.");
            }

            fclose($handle);
            $this->logMessage('info', "CSV import completed successfully.");
        } catch (Throwable $e) {
            $this->logMessage('error', "Failed to import CSV file: {$e->getMessage()}");
        }

    }

    function convertIpToNumeric($ip): float|int|string
    {
        if (is_numeric($ip)) {
            return $ip;
        }

        $numericIp = ip2long($ip);

        if ($numericIp === false) {
            throw new InvalidArgumentException("Wrong format: $ip");
        }

        return $numericIp;
    }


    private function logMessage(string $level, string $message): void
    {
        Log::{$level}($message);

        $output = new ConsoleOutput();
        $output->writeln("<info>{$message}</info>");
    }
}
