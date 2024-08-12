<?php

namespace wtg\IpCountryDetector\Tests;

use Orchestra\Testbench\TestCase as OrchestraTestCase;
use wtg\IpCountryDetector\IpCountryDetectorServiceProvider;

abstract class TestCase extends OrchestraTestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            IpCountryDetectorServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('ipcountry.auth_key', 'test-key');
    }
}
