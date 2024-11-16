<?php

namespace Tests\Feature;

use App\Models\ServiceLog;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SearchGifsFeatureTest extends TestCase
{
    protected $user;

    /**
     * Setup the test environment for each test method.
     */
    protected function setUp(): void
    {
        parent::setUp();

        DB::beginTransaction();
        $this->user = User::factory()->create();
    }

    /**
     * Error GIF searching without authentication.
     */
    public function test_unauthenticated_user_cannot_search_gifs(): void
    {
        // Act: Make the search request without authentication.
        $response = $this->getJson('/api/v1/gifs?q=batman&limit=10&offset=1');

        // Assert: Verify that the response returns a 401 Unauthorized error.
        $response->assertStatus(401)
                 ->assertJson([
                     'message' => 'Unauthenticated.',
                 ]);
    }

    /**
     * Successful GIF search by authenticated user.
     */
    public function test_authenticated_user_can_search_gifs_successfully(): void
    {
        $payload = [
            'q' => 'batman',
            'limit' => 10,
            'offset' => 1,
        ];

        // Act: Make the search request.
        $response = $this->actingAs($this->user)
                         ->getJson('/api/v1/gifs?q=batman&limit=10&offset=1');

        // Assert: Verify that the response has the gif data and pagination information.
        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'data' => [
                         '*' => [
                            'id',
                            'url',
                            'title',
                            'images' => [
                                'original' => ['url', 'width', 'height'],
                                'fixed_width' => ['url', 'width', 'height'],
                                'fixed_height' => ['url', 'width', 'height'],
                            ],
                         ],
                     ],
                     'pagination' => [
                         'offset',
                         'total_count',
                         'count',
                     ],
                 ]);

        $log = ServiceLog::latest()->first();

        $this->assertEquals('api/v1/gifs', $log->service);
        $this->assertEquals($payload, $log->request_body);
        $this->assertEquals(200, $log->response_status);
        $this->assertIsArray($log->response_body);
        $this->assertEquals('127.0.0.1', $log->ip_address);
        $this->assertStringEndsWith('ms', $log->duration);
    }

    /**
     * GIF search returns no results.
     */
    public function test_search_gifs_returns_empty_results_when_no_gifs_found(): void
    {
        $payload = [
            'q' => 'nonexistentkeyword',
        ];

        // Act: Make the search request with a query without result.
        $response = $this->actingAs($this->user)
                         ->getJson('/api/v1/gifs?q=nonexistentkeyword');

        // Assert: Verify that the response has the gif data empty and pagination information.
        $response->assertStatus(200)
                 ->assertJson([
                     'data' => [],
                     'pagination' => [
                         'total_count' => 0,
                         'count' => 0,
                         'offset' => 0,
                     ],
                 ]);

        $log = ServiceLog::latest()->first();

        $this->assertEquals('api/v1/gifs', $log->service);
        $this->assertEquals($payload, $log->request_body);
        $this->assertEquals(200, $log->response_status);
        $this->assertIsArray($log->response_body);
        $this->assertEquals('127.0.0.1', $log->ip_address);
        $this->assertStringEndsWith('ms', $log->duration);
    }

    /**
     * GIF search uses default limit and offset values when not provided.
     */
    public function test_search_gifs_defaults_limit_and_offset_when_not_provided(): void
    {
        $payload = [
            'q' => 'batman',
        ];

        // Act: Make the search request with just the required fields.
        $response = $this->actingAs($this->user)
                         ->getJson('/api/v1/gifs?q=batman');

        // Assert: Verify that the response has the gif data and pagination information.
        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'data' => [
                         '*' => [
                            'id',
                            'url',
                            'title',
                            'images' => [
                                'original' => ['url', 'width', 'height'],
                                'fixed_width' => ['url', 'width', 'height'],
                                'fixed_height' => ['url', 'width', 'height'],
                            ],
                         ],
                     ],
                     'pagination' => [
                         'total_count',
                         'count',
                         'offset',
                     ],
                 ]);

        $log = ServiceLog::latest()->first();

        $this->assertEquals('api/v1/gifs', $log->service);
        $this->assertEquals($payload, $log->request_body);
        $this->assertEquals(200, $log->response_status);
        $this->assertIsArray($log->response_body);
        $this->assertEquals('127.0.0.1', $log->ip_address);
        $this->assertStringEndsWith('ms', $log->duration);
    }

    /**
     * Error GIF search with invalid limit or offset values.
     */
    public function test_search_gifs_fails_with_invalid_limit_or_offset(): void
    {
        // Act: Make the search request with limit field as a negative number.
        $response = $this->actingAs($this->user)
                         ->getJson('/api/v1/gifs?q=batman&limit=-10&offset=1');

        // Assert: Verify that the response returns a 422 error.
        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['limit']);

        // Act: Make the search request with offset field as a negative number.
        $response = $this->actingAs($this->user)
                         ->getJson('/api/v1/gifs?q=batman&limit=10&offset=-5');

        // Assert: Verify that the response returns a 422 error.
        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['offset']);

        // Act: Make the search request with offset and limit fields as an string.
        $response = $this->actingAs($this->user)
                         ->getJson('/api/v1/gifs?q=batman&limit=abc&offset=xyz');

        // Assert: Verify that the response returns a 422 error.
        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['limit', 'offset']);
    }

    /**
     * Error GIF search with missing query.
     */
    public function test_search_gifs_fails_with_missing_query(): void
    {
        $payload = [
            'q' => null,
        ];

        // Act: Make the search request without query value.
        $response = $this->actingAs($this->user)
                         ->getJson('/api/v1/gifs?q=');

        // Assert: Verify that the response returns a 422 error.
        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['q']);

        $log = ServiceLog::latest()->first();

        $this->assertEquals('api/v1/gifs', $log->service);
        $this->assertEquals($payload, $log->request_body);
        $this->assertEquals(422, $log->response_status);
        $this->assertIsArray($log->response_body);
        $this->assertEquals('127.0.0.1', $log->ip_address);
        $this->assertStringEndsWith('ms', $log->duration);
    }

    /**
     * Tear down the test environment for each test method.
     */
    protected function tearDown(): void
    {
        DB::rollBack();
    }
}
