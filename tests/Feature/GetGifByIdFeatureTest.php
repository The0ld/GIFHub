<?php

namespace Tests\Feature;

use App\Models\ServiceLog;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class GetGifByIdFeatureTest extends TestCase
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
     * Error GET GIF without authentication.
     */
    public function test_unauthenticated_user_cannot_search_gif_by_id(): void
    {
        // Act: Make the search request without authentication.
        $response = $this->getJson('/api/v1/gifs/YsTs5ltWtEhnq');

        // Assert: Verify that the response returns a 401 Unauthorized error.
        $response->assertStatus(401)
                 ->assertJson([
                     'message' => 'Unauthenticated.',
                 ]);
    }

    /**
     * Successful GET GIF by authenticated user.
     */
    public function test_authenticated_user_can_search_gif_by_id_successfully(): void
    {
        // Act: Make the get request.
        $response = $this->actingAs($this->user)
                         ->getJson('/api/v1/gifs/YsTs5ltWtEhnq');

        // Assert: Verify that the response has the gif data.
        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'data' => [
                         'id',
                         'url',
                         'title',
                         'images' => [
                             'original' => ['url', 'width', 'height'],
                             'fixed_width' => ['url', 'width', 'height'],
                             'fixed_height' => ['url', 'width', 'height'],
                         ],
                    ],
                 ]);

        $log = ServiceLog::orderBy('id', 'desc')->first();

        $this->assertEquals($this->user->id, $log->user_id);
        $this->assertEquals('api/v1/gifs/YsTs5ltWtEhnq', $log->service);
        $this->assertIsArray($log->request_body);
        $this->assertIsArray($log->response_body);
        $this->assertEquals(200, $log->response_status);
        $this->assertEquals('127.0.0.1', $log->ip_address);
        $this->assertStringEndsWith('ms', $log->duration);
    }

    /**
     * No GIF found for the given ID.
     */
    public function test_search_gif_by_id_returns_404_when_gif_not_found(): void
    {
        // Act: Make the get request with an non-existent id.
        $response = $this->actingAs($this->user)
                         ->getJson('/api/v1/gifs/xH0BU4VyKF40qLqjma');

        // Assert: Verify that the response returns a 404 error.
        $response->assertStatus(404);

        $log = ServiceLog::orderBy('id', 'desc')->first();

        $this->assertEquals($this->user->id, $log->user_id);
        $this->assertEquals('api/v1/gifs/xH0BU4VyKF40qLqjma', $log->service);
        $this->assertIsArray($log->request_body);
        $this->assertIsArray($log->response_body);
        $this->assertEquals(404, $log->response_status);
        $this->assertEquals('127.0.0.1', $log->ip_address);
        $this->assertStringEndsWith('ms', $log->duration);
    }

    /**
     * Invalid GIF ID provided.
     */
    public function test_search_gif_by_id_fails_with_invalid_id(): void
    {
        // Act: Make the get request with an invalid id.
        $response = $this->actingAs($this->user)
                         ->getJson('/api/v1/gifs/invalid$id');

        // Assert: Verify that the response returns a 422 error.
        $response->assertStatus(422);

        $log = ServiceLog::orderBy('id', 'desc')->first();

        $this->assertEquals($this->user->id, $log->user_id);
        $this->assertEquals('api/v1/gifs/invalid$id', $log->service);
        $this->assertIsArray($log->request_body);
        $this->assertIsArray($log->response_body);
        $this->assertEquals(422, $log->response_status);
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
