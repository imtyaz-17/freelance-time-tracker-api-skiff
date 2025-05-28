<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ProjectTest extends TestCase
{
    use RefreshDatabase;

    public function test_crud_operations(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create(['user_id' => $user->id]);
        $this->actingAs($user, 'sanctum');

        // Create
        $projectData = [
            'client_id' => $client->id,
            'title' => 'Test Project',
            'description' => 'Test description',
            'status' => 'active',
        ];

        $createResponse = $this->postJson('/api/projects', $projectData);
        $createResponse->assertStatus(201);
        $projectId = $createResponse->json('data.id');

        // Read
        $getResponse = $this->getJson("/api/projects/{$projectId}");
        $getResponse->assertStatus(200);

        // Update
        $updateResponse = $this->putJson("/api/projects/{$projectId}", ['title' => 'Updated']);
        $updateResponse->assertStatus(200);

        // Delete
        $deleteResponse = $this->deleteJson("/api/projects/{$projectId}");
        $deleteResponse->assertStatus(200);
    }

    public function test_project_filtering(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create(['user_id' => $user->id]);
        
        Project::factory()->active()->count(2)->create(['client_id' => $client->id]);
        Project::factory()->completed()->create(['client_id' => $client->id]);
        $this->actingAs($user, 'sanctum');

        // Test filtering for active projects
        $response = $this->getJson('/api/projects?status=active');
        $response->assertStatus(200);
        
        // Test filtering for completed projects
        $response = $this->getJson('/api/projects?status=completed');
        $response->assertStatus(200);
    }

    public function test_authorization(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        
        $client = Client::factory()->create(['user_id' => $user1->id]);
        $project = Project::factory()->create(['client_id' => $client->id]);
        $this->actingAs($user2, 'sanctum');

        $response = $this->getJson("/api/projects/{$project->id}");
        $response->assertStatus(403);
    }
} 