<?php

namespace Tests\Feature;

use App\Models\ServiceLog;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class LoginFeatureTest extends TestCase
{
    /**
     * Setup the test environment for each test method.
     */
    protected function setUp(): void
    {
        parent::setUp();

        DB::beginTransaction();
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);
    }

    /**
     * Successful login test with valid credentials.
     */
    public function test_user_can_login_with_valid_credentials()
    {
        $payload = [
            'email' => 'test@example.com',
            'password' => 'password123',
        ];

        // Act: Make the login request.
        $response = $this->postJson('/api/auth/login', $payload);

        $tokenData = $response->json('data');
        $expiresAt = $tokenData['expires_at'];

        // Assert: Verify that the response has the token and is successful.
        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'data' => [
                         'access_token',
                         'token_type',
                         'expires_at',
                     ],
                 ]);

        // Assert: Verify that the token expiration is 30 minutes.
        $this->assertNotNull($expiresAt, 'The token must have an expiration date.');
        $this->assertEquals(
            now()->addMinutes(30)->format('Y-m-d H:i:s'),
            \Carbon\Carbon::parse($expiresAt)->format('Y-m-d H:i:s'),
            'The token should expire in 30 minutes.'
        );

        $log = ServiceLog::latest()->first();

        unset($payload['password']);

        $this->assertEquals('api/auth/login', $log->service);
        $this->assertEquals($payload, $log->request_body);
        $this->assertEquals(200, $log->response_status);
        $this->assertIsArray($log->response_body);
        $this->assertEquals('127.0.0.1', $log->ip_address);
        $this->assertStringEndsWith('ms', $log->duration);
    }

    /**
     * Authentication error test with invalid credentials.
     */
    public function test_user_cannot_login_with_invalid_credentials()
    {
        $payload = [
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
        ];

        // Act: Trying to log in with incorrect credentials.
        $response = $this->postJson('/api/auth/login', $payload);

        // Assert: Verify that the response returns a 401 error.
        $response->assertStatus(401)
                 ->assertJson([
                     'message' => 'Invalid credentials',
                 ]);

        $log = ServiceLog::latest()->first();

        unset($payload['password']);

        $this->assertEquals('api/auth/login', $log->service);
        $this->assertEquals($payload, $log->request_body);
        $this->assertEquals(401, $log->response_status);
        $this->assertIsArray($log->response_body);
        $this->assertEquals('127.0.0.1', $log->ip_address);
        $this->assertStringEndsWith('ms', $log->duration);
    }

    /**
     * Validation error test when fields are missing.
     */
    public function test_login_fails_when_fields_are_missing()
    {
        // Act & Assert: Send request without password field
        $response = $this->postJson('/api/auth/login', [
            'email' => 'test@example.com',
        ]);

        // Assert: Verify that the response returns a 422 error.
        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['password']);

        $log = ServiceLog::latest()->first();

        $this->assertEquals('api/auth/login', $log->service);
        $this->assertEquals(422, $log->response_status);
        $this->assertIsArray($log->response_body);
        $this->assertEquals('127.0.0.1', $log->ip_address);
        $this->assertStringEndsWith('ms', $log->duration);

        // Act & Assert: Send request without the email field
        $response = $this->postJson('/api/auth/login', [
            'password' => 'password123',
        ]);

        // Assert: Verify that the response returns a 422 error.
        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['email']);

        $log = ServiceLog::latest()->first();

        $this->assertEquals('api/auth/login', $log->service);
        $this->assertEquals(422, $log->response_status);
        $this->assertIsArray($log->response_body);
        $this->assertEquals('127.0.0.1', $log->ip_address);
        $this->assertStringEndsWith('ms', $log->duration);

        // Act & Assert: Send request without any field
        $response = $this->postJson('/api/auth/login', []);

        // Assert: Verify that the response returns a 422 error.
        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['email', 'password']);

        $log = ServiceLog::latest()->first();

        $this->assertEquals('api/auth/login', $log->service);
        $this->assertEquals(422, $log->response_status);
        $this->assertIsArray($log->response_body);
        $this->assertEquals('127.0.0.1', $log->ip_address);
        $this->assertStringEndsWith('ms', $log->duration);
    }

    /**
     * Validation error test when fields are wrong.
     */
    public function test_login_fails_when_fields_are_wrong()
    {
        // Act & Assert: Send request with a wrong email format
        $response = $this->postJson('/api/auth/login', [
            'email' => 'test',
            'password' => 'wrongpassword'
        ]);

        // Assert: Verify that the response returns a 422 error.
        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['email']);

        $log = ServiceLog::latest()->first();

        $this->assertEquals('api/auth/login', $log->service);
        $this->assertEquals(['email' => 'test'], $log->request_body);
        $this->assertEquals(422, $log->response_status);
        $this->assertIsArray($log->response_body);
        $this->assertEquals('127.0.0.1', $log->ip_address);
        $this->assertStringEndsWith('ms', $log->duration);

        // Act & Assert: Send request with a too short password
        $response = $this->postJson('/api/auth/login', [
            'email' => 'test@gmail.com',
            'password' => 'passw',
        ]);

        // Assert: Verify that the response returns a 422 error.
        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['password']);

        $log = ServiceLog::latest()->first();

        $this->assertEquals('api/auth/login', $log->service);
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
