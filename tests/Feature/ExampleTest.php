<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_videos_page_returns_results(): void
    {
        $response = $this->get('/api/videos/nl');

        $response->assertStatus(200);
    }
}
