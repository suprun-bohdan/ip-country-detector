<?php

namespace IpCountryDetector\Tests\Feature;

use IpCountryDetector\Tests\TestCase;
use Illuminate\Support\Facades\Route;

class IpAuthorizationMiddlewareTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Route::middleware('ipcountry.auth')->get('/test-route', function () {
            return response()->json(['message' => 'Authorized']);
        });
    }

    /** @test */
    public function it_blocks_requests_without_authorization_key()
    {
        $response = $this->getJson('/test-route');

        $response->assertStatus(401)
            ->assertJson(['message' => 'Unauthorized']);
    }

    /** @test */
    public function it_allows_requests_with_valid_authorization_key()
    {
        $response = $this->getJson('/test-route', ['Authorization' => 'test-key']);

        $response->assertStatus(200)
            ->assertJson(['message' => 'Authorized']);
    }
}
