<?php

namespace wtg\IpCountryDetector\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use wtg\IpCountryDetector\Jobs\UpdateIpCsvFile;

class InstallIpCountryDetectorCommand extends Command
{
    protected $signature = 'ip-country-detector:install';

    protected $description = 'Install the IP Country Detector package, update IP database, and seed the data.';

    public function handle(): int
    {
        $this->info('Starting installation of IP Country Detector package...');

        UpdateIpCsvFile::dispatchSync();
        $this->info('CSV file downloaded successfully.');

        $this->call('migrate');

        Artisan::call('db:seed', [
            '--class' => 'wtg\IpCountryDetector\Seeders\IpCountrySeeder',
        ]);
        $this->info('Database seeded successfully.');

        $this->info('IP Country Detector package installed successfully.');
        return 0;
    }
}
