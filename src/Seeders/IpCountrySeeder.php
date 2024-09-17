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

        $records = [];

        try {
            DB::transaction(function () use ($handle, &$records) {
                $chunkSize = 1000;
                $chunkCount = 1;

                while (($data = fgetcsv($handle, 1000, ",")) !== false) {
                    [$firstIp, $lastIp, $country] = $data;

                    $records[] = [
                        'first_ip' => ip2long($firstIp),
                        'last_ip' => ip2long($lastIp),
                        'country' => $country,
                    ];

                    if (count($records) === $chunkSize) {
                        $this->logMessage("№ {$chunkCount}: Inserting chunk of $chunkSize records", 'info');
                        $this->insertOrUpdate($records);
                        $this->logMessage("Chunk inserted successfully.", 'info');
                        $chunkCount++;
                        $records = [];
                    }
                }

                if ($records) {
                    $this->logMessage("№ {$chunkCount}: Inserting last chunk of records", 'info');
                    $this->insertOrUpdate($records);
                    $this->logMessage("Last chunk inserted successfully.", 'info');
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
