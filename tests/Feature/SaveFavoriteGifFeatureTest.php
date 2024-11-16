<?php

namespace Tests\Feature;

use App\Models\ServiceLog;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SaveFavoriteGifFeatureTest extends TestCase
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
     * Unauthorized users cannot save favorite GIF.
     */
    public function test_unauthenticated_user_cannot_save_favorite_gif(): void
    {
        $payload = [
            'gif_id' => 'YsTs5ltWtEhnq',
            'alias' => 'alias_gif',
            'user_id' => null,
        ];

        // Act: Make the save request without authentication.
        $response = $this->postJson('/api/v1/gifs', $payload);

        // Assert: Verify that the response returns a 401 Unauthorized error.
        $response->assertStatus(401)
                 ->assertJson([
                     'message' => 'Unauthenticated.',
                 ]);
    }

    /**
     * Authenticated user can save favorite GIF with valid data.
     */
    public function test_authenticated_user_can_save_favorite_gif_with_valid_data()
    {
        $payload = [
            'gif_id' => 'YsTs5ltWtEhnq',
            'alias' => 'alias_gif',
            'user_id' => $this->user->id,
        ];

        // Act: Make the save favorite request
        $response = $this->actingAs($this->user)
                         ->postJson('/api/v1/gifs', $payload);

        // Assert: Verify that the response is successful
        $response->assertStatus(201);

        $log = ServiceLog::latest()->first();

        $this->assertEquals($this->user->id, $log->user_id);
        $this->assertEquals('api/v1/gifs', $log->service);
        $this->assertEquals($payload, $log->request_body);
        $this->assertEquals(201, $log->response_status);
        $this->assertIsArray($log->response_body);
        $this->assertEquals('127.0.0.1', $log->ip_address);
        $this->assertStringEndsWith('ms', $log->duration);
    }

    /**
     * Saving favorite GIF fails when required fields are missing.
     */
    public function test_save_favorite_gif_fails_with_missing_fields(): void
    {
        // Act: Make the save request with missing fields.
        $response = $this->actingAs($this->user)
                         ->postJson('/api/v1/gifs', []);

        // Assert: Verify that the response returns a 422 error with validation errors.
        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['alias', 'user_id', 'gif_id']);

        $log = ServiceLog::latest()->first();

        $this->assertEquals($this->user->id, $log->user_id);
        $this->assertEquals('api/v1/gifs', $log->service);
        $this->assertEquals([], $log->request_body);
        $this->assertEquals(422, $log->response_status);
        $this->assertIsArray($log->response_body);
        $this->assertEquals('127.0.0.1', $log->ip_address);
        $this->assertStringEndsWith('ms', $log->duration);
    }

    /**
     * Saving favorite GIF fails with invalid GIF ID.
     */
    public function test_save_favorite_gif_fails_with_invalid_gif_id(): void
    {
        $payload = [
            'gif_id' => 'invalid$id!',
            'alias' => 'alias_gif',
            'user_id' => $this->user->id,
        ];

        // Act: Make the save request with an invalid GIF ID.
        $response = $this->actingAs($this->user)
                         ->postJson('/api/v1/gifs', $payload);

        // Assert: Verify that the response returns a 422 error with validation errors.
        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['gif_id']);

        $log = ServiceLog::latest()->first();

        $this->assertEquals($this->user->id, $log->user_id);
        $this->assertEquals('api/v1/gifs', $log->service);
        $this->assertEquals($payload, $log->request_body);
        $this->assertEquals(422, $log->response_status);
        $this->assertIsArray($log->response_body);
        $this->assertEquals('127.0.0.1', $log->ip_address);
        $this->assertStringEndsWith('ms', $log->duration);
    }

    /**
     * Saving favorite GIF fails with invalid alias.
     */
    public function test_save_favorite_gif_fails_with_invalid_alias(): void
    {
        $payload = [
            'gif_id' => 'YsTs5ltWtEhnq',
            'alias' => 123,
            'user_id' => $this->user->id,
        ];

        // Act: Make the save request with an invalid alias.
        $response = $this->actingAs($this->user)
                         ->postJson('/api/v1/gifs', $payload);

        // Assert: Verify that the response returns a 422 error with validation errors.
        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['alias']);

        $log = ServiceLog::latest()->first();

        $this->assertEquals($this->user->id, $log->user_id);
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
