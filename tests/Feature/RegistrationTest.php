<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\UsernameService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Jetstream\Jetstream;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'terms' => Jetstream::hasTermsAndPrivacyPolicyFeature(),
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('profile.show'));

        // Verify username was generated
        $user = User::where('email', 'test@example.com')->first();
        $this->assertNotNull($user->username);
        $this->assertEquals('testuser', $user->username);
    }

    public function test_username_generation_creates_unique_usernames(): void
    {
        // Create first user
        $user1 = User::factory()->create([
            'name' => 'John Doe',
            'username' => 'johndoe',
        ]);

        // Try to register another user with similar name
        $response = $this->post('/register', [
            'name' => 'John Doe',
            'email' => 'john2@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'terms' => Jetstream::hasTermsAndPrivacyPolicyFeature(),
        ]);

        $this->assertAuthenticated();
        
        // Verify second user got a unique username
        $user2 = User::where('email', 'john2@example.com')->first();
        $this->assertNotNull($user2->username);
        $this->assertNotEquals('johndoe', $user2->username);
        $this->assertStringStartsWith('johndoe', $user2->username);
    }

    public function test_username_service_generates_correct_usernames(): void
    {
        $usernameService = app(UsernameService::class);
        
        $this->assertEquals('harshsvmodi', $usernameService->generateFromName('Harsh Svvv Modi'));
        $this->assertEquals('johndoe', $usernameService->generateFromName('John Doe'));
        $this->assertEquals('maryjane', $usernameService->generateFromName('Mary Jane'));
    }
}
