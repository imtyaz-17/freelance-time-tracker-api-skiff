<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Project;
use App\Models\TimeLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class TimeLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_crud_operations(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create(['user_id' => $user->id]);
        $project = Project::factory()->create(['client_id' => $client->id]);
        $this->actingAs($user, 'sanctum');

        // Create
        $timeLogData = [
            'project_id' => $project->id,
            'start_time' => Carbon::now()->subHours(2)->toISOString(),
            'end_time' => Carbon::now()->toISOString(),
            'description' => 'Test description',
            'is_billable' => true,
        ];

        $createResponse = $this->postJson('/api/time-logs', $timeLogData);
        $createResponse->assertStatus(201);
        $timeLogId = $createResponse->json('data.id');

        // Read
        $getResponse = $this->getJson("/api/time-logs/{$timeLogId}");
        $getResponse->assertStatus(200);

        // Update
        $updateResponse = $this->putJson("/api/time-logs/{$timeLogId}", ['description' => 'Updated']);
        $updateResponse->assertStatus(200);

        // Delete
        $deleteResponse = $this->deleteJson("/api/time-logs/{$timeLogId}");
        $deleteResponse->assertStatus(200);
    }

    public function test_timer_operations(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create(['user_id' => $user->id]);
        $project = Project::factory()->create(['client_id' => $client->id]);
        $this->actingAs($user, 'sanctum');

        // Start timer
        $startResponse = $this->postJson('/api/time-logs/start', [
            'project_id' => $project->id,
            'description' => 'Timer test'
        ]);
        $startResponse->assertStatus(201);
        $timeLogId = $startResponse->json('data.id');

        // Stop timer
        $stopResponse = $this->postJson("/api/time-logs/{$timeLogId}/stop");
        $stopResponse->assertStatus(200);
    }

    public function test_authorization(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        
        $client1 = Client::factory()->create(['user_id' => $user1->id]);
        $project1 = Project::factory()->create(['client_id' => $client1->id]);
        $timeLog = TimeLog::factory()->create(['project_id' => $project1->id]);

        $this->actingAs($user2, 'sanctum');
        
        $response = $this->getJson("/api/time-logs/{$timeLog->id}");
        $response->assertStatus(403);
    }
} 