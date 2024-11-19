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
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
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
            $this->logMessage('info', "Starting CSV import using mysqlimport...");

            $database = config('database.connections.mysql.database');
            $host = config('database.connections.mysql.host');
            $username = config('database.connections.mysql.username');
            $password = config('database.connections.mysql.password');
            $tableName = (new IpCountry())->getTable();

            $command = [
                'mysqlimport',
                '--local',
                '--host=' . $host,
                '--user=' . $username,
                '--password=' . $password,
                '--fields-terminated-by=,',
                '--lines-terminated-by=\n',
                '--ignore-lines=1',
                '--columns=first_ip,last_ip,country,region,subregion,city,latitude,longitude,timezone',
                $database,
                $csvFilePath,
            ];

            $process = new Process($command);
            $process->setTimeout(3600);
            $process->start();

            foreach ($process as $type => $data) {
                if ($process::OUT === $type) {
                    $this->logMessage('info', $data);
                } else { // $process::ERR
                    $this->logMessage('error', $data);
                }
            }

            if (!$process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }

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
