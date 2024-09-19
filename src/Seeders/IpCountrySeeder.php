<?php

namespace IpCountryDetector\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;
use Symfony\Component\Console\Output\ConsoleOutput;

class IpCountrySeeder extends Seeder
{
    private const TEMP_CSV_FILE = 'asn-country-ipv4.csv';
    protected string $tableName = 'ip_country';

    /**
     * Run the database seeds.
     *
     * @return void
     * @throws Throwable
     */
    public function run(): void
    {
        $csvFilePath = storage_path('app/' . self::TEMP_CSV_FILE);
        $this->logMessage('info', "CSV file path: $csvFilePath");

        if (!$handle = fopen($csvFilePath, 'r')) {
            $this->logMessage('error', "Unable to open CSV file: $csvFilePath");
            return;
        }

        try {
            DB::transaction(function () use ($handle) {
                $rowCount = 1;
                $totalRows = 0;

                while (($data = fgetcsv($handle, 1000, ",")) !== false) {
                    $totalRows++;
                }

                rewind($handle);

                while (($data = fgetcsv($handle, 1000, ",")) !== false) {
                    [$firstIp, $lastIp, $country] = $data;

                    $record = [
                        'first_ip' => ip2long($firstIp),
                        'last_ip' => ip2long($lastIp),
                        'country' => $country,
                    ];

                    DB::table($this->tableName)->insert($record);

                    $this->logMessage('info', "[№ $rowCount / {$totalRows}] - [{$firstIp} - {$lastIp}] - [{$country}]");

                    $rowCount++;
                }

                fclose($handle);
                $this->logMessage('info', "CSV processing completed and file closed.");
            });
        } catch (Throwable $e) {
            $this->logMessage('error', "Failed to process CSV file: {$e->getMessage()}");
        }
    }

    private function logMessage(string $level, string $message): void
    {
        Log::{$level}($message);

        $output = new ConsoleOutput();
        $output->writeln("<comment>{$message}</comment>");
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
