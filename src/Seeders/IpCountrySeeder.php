<?php

namespace IpCountryDetector\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use IpCountryDetector\Services\CsvFilePathService;
use Throwable;
use Symfony\Component\Console\Output\ConsoleOutput;

class IpCountrySeeder extends Seeder
{
    protected string $tableName = 'ip_country';

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
        $csvFilePath = $this->csvFilePathService->getCsvFilePath();
        $this->logMessage('info', "CSV file path: $csvFilePath");
        sleep(5);

        if (!$handle = fopen($csvFilePath, 'r')) {
            $this->logMessage('error', "Unable to open CSV file: $csvFilePath");
            return;
        }

        try {
            DB::transaction(function () use ($handle) {
                $dataRows = [];
                $rowCount = 1;

                while (($data = fgetcsv($handle, 1000, ",")) !== false) {
                    $dataRows[] = $data;
                }

                usort($dataRows, function ($a, $b) {
                    return strcmp($a[2], $b[2]);
                });

                $totalRows = count($dataRows);

                rewind($handle);

                foreach ($dataRows as $data) {
                    [$firstIp, $lastIp, $country] = $data;

                    $record = [
                        'first_ip' => ip2long($firstIp),
                        'last_ip' => ip2long($lastIp),
                        'country' => $country,
                    ];

                    DB::table($this->tableName)->updateOrInsert($record);

                    $percentage = number_format(($rowCount / $totalRows) * 100, 1);

                    $mysqlLoad = null;
                    if (strtolower(config('database.default')) == 'mysql') {
                        $mysqlLoad = shell_exec("ps aux | grep mysqld | grep -v grep | awk '{print $3}'");
                        $mysqlLoad = trim($mysqlLoad) ?: 'N/A';
                    }

                    $this->logMessage('info', sprintf(
                        "[%6s%% | %6d / 100%% | %6d] - [%2s] - [%s - %s] | [MySQL Load: %s%%]",
                        $percentage,
                        $rowCount,
                        $totalRows,
                        $country,
                        str_pad($firstIp, 15, " ", STR_PAD_RIGHT),
                        str_pad($lastIp, 15, " ", STR_PAD_RIGHT),
                        $mysqlLoad
                    ));

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
        $output->writeln("<info>{$message}</info>");
    }
}
