<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ClientTest extends TestCase
{
    use RefreshDatabase;

    public function test_crud_operations(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        // Create
        $clientData = [
            'name' => 'Test Client',
            'email' => 'test@client.com',
            'contact_person' => 'John Doe',
        ];

        $createResponse = $this->postJson('/api/clients', $clientData);
        $createResponse->assertStatus(201);
        $clientId = $createResponse->json('data.id');

        // Read
        $getResponse = $this->getJson("/api/clients/{$clientId}");
        $getResponse->assertStatus(200);

        // Update
        $updateResponse = $this->putJson("/api/clients/{$clientId}", ['name' => 'Updated']);
        $updateResponse->assertStatus(200);

        // Delete
        $deleteResponse = $this->deleteJson("/api/clients/{$clientId}");
        $deleteResponse->assertStatus(200);
    }

    public function test_authorization(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        
        $client = Client::factory()->create(['user_id' => $user1->id]);
        $this->actingAs($user2, 'sanctum');

        $response = $this->getJson("/api/clients/{$client->id}");
        $response->assertStatus(403);
    }
} 