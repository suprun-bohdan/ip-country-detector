<?php

namespace IpCountryDetector\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class IpCountrySeeder extends Seeder
{
    private const TEMP_CSV_FILE = 'asn-country-ipv4.csv';

    /**
     * Run the database seeds.
     *
     * @return void
     * @throws Throwable
     */
    public function run(): void
    {
        $csvFilePath = storage_path(self::TEMP_CSV_FILE);
        $this->logMessage("CSV file path: $csvFilePath", 'info');

        if (!$handle = fopen($csvFilePath, 'r')) {
            $this->logMessage("Unable to open CSV file: $csvFilePath", 'error');
            return;
        }

        try {
            DB::transaction(function () use ($handle) {
                $rowCount = 1;

                while (($data = fgetcsv($handle, 1000, ",")) !== false) {
                    [$firstIp, $lastIp, $country] = $data;

                    $record = [
                        'first_ip' => ip2long($firstIp),
                        'last_ip' => ip2long($lastIp),
                        'country' => $country,
                    ];

                    $this->logMessage("Row № {$rowCount}: Processing record - First IP: {$firstIp}, Last IP: {$lastIp}, Country: {$country}", 'info');

                    $this->insertOrUpdate([$record]);

                    $this->logMessage("Row № {$rowCount}: Record inserted successfully.", 'info');
                    $rowCount++;
                }

                fclose($handle);
                $this->logMessage("CSV processing completed and file closed.", 'info');
            });
        } catch (Throwable $e) {
            $this->logMessage("Failed to process CSV file: {$e->getMessage()}", 'error');
        }

    }

    private function logMessage(string $message, string $level): void
    {
        Log::{$level}($message);
    }

    private function insertOrUpdate(array $records): void
    {
        foreach ($records as $record) {
            DB::table('ip_country')->updateOrInsert(
                ['first_ip' => $record['first_ip'], 'last_ip' => $record['last_ip']],
                ['country' => $record['country']]
            );
        }
    }
}
