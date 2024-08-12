<?php

namespace wtg\IpCountryDetector\Console;

use Illuminate\Console\Command;
use wtg\IpCountryDetector\Jobs\UpdateIpCsvFile;

class UpdateIpDatabaseCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:ip-database';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Download the IP CSV file and update the database with IP ranges and countries';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        UpdateIpCsvFile::dispatchSync();

        $this->call('db:seed', [
            '--class' => 'wtg\IpCountryDetector\Seeders\IpCountrySeeder',
        ]);

        $this->info('IP database has been successfully updated.');
        return 0;
    }
}
