<?php

namespace IpCountryDetector\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class InstallIpCountryDetectorCommand extends Command
{
    protected $signature = 'ip-country-detector:install';

    protected $description = 'Install the IP Country Detector package, update IP database, and seed the data.';

    private const CSV_URL = 'https://cdn.jsdelivr.net/npm/@ip-location-db/asn-country/asn-country-ipv4.csv';

    private string $filename = 'asn-country-ipv4.csv';

    public function handle(): int
    {
        $storageFilePath = storage_path($this->filename);

        $this->info('Starting installation of IP Country Detector package...');

        if (!file_exists($storageFilePath)) {
            $this->info('CSV file not found. Downloading...');
            $response = Http::get(self::CSV_URL);

            if ($response->ok()) {
                Storage::put($this->filename, $response->body());
                $this->info('CSV file downloaded successfully.');
            } else {
                $this->error('Failed to download CSV file.');
                return 1;
            }
        } else {
            $this->info('CSV file already exists. Skipping download.');
        }

        $this->call('migrate');

        Artisan::call('db:seed', [
            '--class' => 'IpCountryDetector\Seeders\IpCountrySeeder',
        ]);
        $this->info('Database seeded successfully.');

        $this->info('IP Country Detector package installed successfully.');
        return 0;
    }
}
