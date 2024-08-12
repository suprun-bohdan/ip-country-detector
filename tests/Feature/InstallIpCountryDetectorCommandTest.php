<?php

namespace wtg\IpCountryDetector\Tests\Feature;

use wtg\IpCountryDetector\Tests\TestCase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Testing\RefreshDatabase;

class InstallIpCountryDetectorCommandTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_runs_the_install_command_successfully(): void
    {
        Storage::fake('local');

        $exitCode = Artisan::call('ip-country-detector:install');

        $this->assertEquals(0, $exitCode);

        Storage::disk('local')->assertExists('asn-country-ipv4.csv');

        $this->assertDatabaseHas('ip_country', [
            'country' => 'IS',
        ]);
    }
}
