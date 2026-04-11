<?php

namespace Tests\Feature;

use App\Models\Commander;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommanderControllerTest extends TestCase
{
    use RefreshDatabase;

    private function userWithCommander(): User
    {
        $user = User::factory()->create();
        Commander::factory()->create(['user_id' => $user->id]);

        return $user->fresh('commander');
    }

    public function test_unauthenticated_request_is_rejected(): void
    {
        $response = $this->putJson('/api/commander', [
            'inara_api_key' => 'new-key',
        ]);

        $response->assertUnauthorized();
    }

    public function test_authenticated_user_without_commander_is_rejected(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')->putJson('/api/commander', [
            'inara_api_key' => 'new-key',
        ]);

        $response->assertBadRequest();
    }

    public function test_commander_keys_can_be_updated(): void
    {
        $user = $this->userWithCommander();

        $response = $this->actingAs($user, 'sanctum')->putJson('/api/commander', [
            'inara_api_key' => 'new-inara-key',
            'edsm_api_key' => 'new-edsm-key',
        ]);

        $response->assertOk()->assertJson(['message' => 'Commander updated successfully']);

        $this->assertDatabaseHas('commanders', [
            'user_id' => $user->id,
            'inara_api_key' => 'new-inara-key',
            'edsm_api_key' => 'new-edsm-key',
        ]);
    }

    public function test_only_inara_key_can_be_updated(): void
    {
        $user = $this->userWithCommander();
        $originalEdsmKey = $user->commander->edsm_api_key;

        $response = $this->actingAs($user, 'sanctum')->putJson('/api/commander', [
            'inara_api_key' => 'updated-inara-key',
        ]);

        $response->assertOk();

        $this->assertDatabaseHas('commanders', [
            'user_id' => $user->id,
            'inara_api_key' => 'updated-inara-key',
            'edsm_api_key' => $originalEdsmKey,
        ]);
    }

    public function test_keys_can_be_cleared_with_null(): void
    {
        $user = $this->userWithCommander();

        $response = $this->actingAs($user, 'sanctum')->putJson('/api/commander', [
            'inara_api_key' => null,
            'edsm_api_key' => null,
        ]);

        $response->assertOk();

        $this->assertDatabaseHas('commanders', [
            'user_id' => $user->id,
            'inara_api_key' => null,
            'edsm_api_key' => null,
        ]);
    }

    public function test_non_string_keys_fail_validation(): void
    {
        $user = $this->userWithCommander();

        $response = $this->actingAs($user, 'sanctum')->putJson('/api/commander', [
            'inara_api_key' => ['array', 'value'],
        ]);

        $response->assertUnprocessable();
    }
}
