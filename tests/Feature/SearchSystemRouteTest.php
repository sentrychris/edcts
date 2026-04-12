<?php

namespace Tests\Feature;

use App\Models\System;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SearchSystemRouteTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_direct_route_when_systems_are_within_jump_range(): void
    {
        $from = System::factory()->atCoords(0, 0, 0)->create();
        $to = System::factory()->atCoords(10, 0, 0)->create();

        $response = $this->getJson("/api/systems/search/route?from={$from->slug}&to={$to->slug}&ly=50");

        $response->assertOk();
        $response->assertJsonCount(2, 'data');
        $response->assertJsonPath('data.0.name', $from->name);
        $response->assertJsonPath('data.1.name', $to->name);
        $response->assertJsonPath('data.0.jump', 0);
        $response->assertJsonPath('data.1.jump', 1);
        $response->assertJsonPath('data.0.distance', 0);
    }

    public function test_returns_multi_hop_route_through_intermediate_systems(): void
    {
        $from = System::factory()->atCoords(0, 0, 0)->create();
        $intermediate = System::factory()->atCoords(20, 0, 0)->create();
        $to = System::factory()->atCoords(40, 0, 0)->create();

        // Jump range of 25 means direct 40 LY jump is impossible; must hop via intermediate
        $response = $this->getJson("/api/systems/search/route?from={$from->slug}&to={$to->slug}&ly=25");

        $response->assertOk();
        $response->assertJsonCount(3, 'data');
        $response->assertJsonPath('data.0.name', $from->name);
        $response->assertJsonPath('data.1.name', $intermediate->name);
        $response->assertJsonPath('data.2.name', $to->name);
    }

    public function test_returns_404_when_no_route_exists(): void
    {
        $from = System::factory()->atCoords(0, 0, 0)->create();
        $to = System::factory()->atCoords(1000, 0, 0)->create();

        // Jump range of 5 LY — no intermediate systems, no route possible
        $response = $this->getJson("/api/systems/search/route?from={$from->slug}&to={$to->slug}&ly=5");

        $response->assertNotFound();
        $response->assertJsonPath('message', 'No route found within the given jump range.');
    }

    public function test_returns_single_system_when_from_and_to_are_the_same(): void
    {
        $system = System::factory()->atCoords(0, 0, 0)->create();

        $response = $this->getJson("/api/systems/search/route?from={$system->slug}&to={$system->slug}&ly=50");

        $response->assertOk();
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.name', $system->name);
    }

    public function test_validates_required_fields(): void
    {
        $response = $this->getJson('/api/systems/search/route');

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['from', 'to', 'ly']);
    }

    public function test_validates_that_systems_exist(): void
    {
        $response = $this->getJson('/api/systems/search/route?from=nonexistent-slug&to=also-fake&ly=50');

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['from', 'to']);
    }

    public function test_validates_jump_range_limits(): void
    {
        $from = System::factory()->create();
        $to = System::factory()->create();

        $response = $this->getJson("/api/systems/search/route?from={$from->slug}&to={$to->slug}&ly=501");

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['ly']);
    }

    public function test_route_response_includes_distance_data(): void
    {
        $from = System::factory()->atCoords(0, 0, 0)->create();
        $to = System::factory()->atCoords(30, 0, 0)->create();

        $response = $this->getJson("/api/systems/search/route?from={$from->slug}&to={$to->slug}&ly=50");

        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                '*' => ['jump', 'id', 'id64', 'name', 'coords', 'slug', 'distance', 'total_distance'],
            ],
        ]);
        $response->assertJsonPath('data.1.distance', 30);
        $response->assertJsonPath('data.1.total_distance', 30);
    }
}
