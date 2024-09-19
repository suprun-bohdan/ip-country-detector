<?php

namespace IpCountryDetector\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class IpCountrySeeder extends Seeder
{
    private const TEMP_CSV_FILE = 'asn-country-ipv4.csv';
    protected string $tableName;

    public function __construct()
    {
        $this->tableName = $this->command->option('table') ?? 'default_table_name';
    }

    /**
     * Run the database seeds.
     *
     * @return void
     * @throws Throwable
     */
    public function run(): void
    {
        $this->tableName = $this->command->option('table') ?? 'default_table_name';

        $csvFilePath = storage_path(self::TEMP_CSV_FILE);
        $this->logMessage("CSV file path: $csvFilePath", 'info');

        if (!$handle = fopen($csvFilePath, 'r')) {
            $this->logMessage("Unable to open CSV file: $csvFilePath", 'error');
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

                    echo "[{$rowCount}/{$totalRows}] - {$firstIp}\n";

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
