<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Project;
use App\Models\TimeLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_basic_report_generation(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create(['user_id' => $user->id]);
        $project = Project::factory()->create(['client_id' => $client->id]);
        
        TimeLog::factory()->count(3)->create([
            'project_id' => $project->id,
            'start_time' => now()->subDays(5),
            'end_time' => now()->subDays(5)->addHours(4),
            'hours' => 4,
        ]);
        $this->actingAs($user, 'sanctum');

        $response = $this->getJson('/api/reports?from=' . now()->subWeek()->format('Y-m-d') . '&to=' . now()->format('Y-m-d'));

        $response->assertStatus(200)
            ->assertJsonStructure(['message', 'data']);
    }

    public function test_pdf_export(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create(['user_id' => $user->id]);
        $project = Project::factory()->create(['client_id' => $client->id]);
        
        TimeLog::factory()->count(2)->create(['project_id' => $project->id]);
        $this->actingAs($user, 'sanctum');

        $response = $this->getJson('/api/reports/export-pdf?from=' . now()->subWeek()->format('Y-m-d') . '&to=' . now()->format('Y-m-d'));

        $response->assertStatus(200)
            ->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_authorization(): void
    {
        $response = $this->getJson('/api/reports?from=' . now()->subWeek()->format('Y-m-d') . '&to=' . now()->format('Y-m-d'));
        $response->assertStatus(401);
    }
} 