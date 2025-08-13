<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\ProfileUrlService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileUrlTest extends TestCase
{
    use RefreshDatabase;

    public function test_username_based_url_works()
    {
        $user = User::factory()->create([
            'username' => 'john-doe',
            'is_public' => true,
        ]);

        $response = $this->get("/profile/{$user->username}");
        
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('Profile/DynamicProfile'));
    }

    public function test_id_based_url_works_as_fallback()
    {
        $user = User::factory()->create([
            'username' => null,
            'is_public' => true,
        ]);

        $response = $this->get("/profile/{$user->id}");
        
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('Profile/DynamicProfile'));
    }

    public function test_id_based_url_redirects_to_username_when_available()
    {
        $user = User::factory()->create([
            'username' => 'john-doe',
            'is_public' => true,
        ]);

        $response = $this->get("/profile/{$user->id}");
        
        $response->assertRedirect("/profile/{$user->username}");
        $response->assertStatus(301); // Permanent redirect
    }

    public function test_profile_url_service_returns_correct_identifier()
    {
        $profileUrlService = app(ProfileUrlService::class);
        
        // User with username
        $userWithUsername = User::factory()->create(['username' => 'john-doe']);
        $this->assertEquals('john-doe', $profileUrlService->getPreferredIdentifier($userWithUsername));
        
        // User without username
        $userWithoutUsername = User::factory()->create(['username' => null]);
        $this->assertEquals((string) $userWithoutUsername->id, $profileUrlService->getPreferredIdentifier($userWithoutUsername));
    }

    public function test_private_profile_returns_403_for_non_owners()
    {
        $user = User::factory()->create([
            'username' => 'john-doe',
            'is_public' => false,
        ]);

        $response = $this->get("/profile/{$user->username}");
        
        $response->assertStatus(403);
    }

    public function test_owner_can_access_private_profile()
    {
        $user = User::factory()->create([
            'username' => 'john-doe',
            'is_public' => false,
        ]);

        $response = $this->actingAs($user)->get("/profile/{$user->username}");
        
        $response->assertStatus(200);
    }

    public function test_nonexistent_profile_returns_404()
    {
        $response = $this->get("/profile/nonexistent-username");
        
        $response->assertStatus(404);
    }

    public function test_profile_tabs_work_with_username()
    {
        $user = User::factory()->create([
            'username' => 'john-doe',
            'is_public' => true,
        ]);

        $tabs = ['family', 'video', 'letters', 'photos'];
        
        foreach ($tabs as $tab) {
            $response = $this->get("/profile/{$user->username}/{$tab}");
            $response->assertStatus(200);
        }
    }

    public function test_profile_tabs_work_with_id_fallback()
    {
        $user = User::factory()->create([
            'username' => null,
            'is_public' => true,
        ]);

        $tabs = ['family', 'video', 'letters', 'photos'];
        
        foreach ($tabs as $tab) {
            $response = $this->get("/profile/{$user->id}/{$tab}");
            $response->assertStatus(200);
        }
    }
}
