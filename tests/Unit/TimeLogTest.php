<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Client;
use App\Models\Project;
use App\Models\TimeLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TimeLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_time_log_relationships(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create(['user_id' => $user->id]);
        $project = Project::factory()->create(['client_id' => $client->id]);
        $timeLog = TimeLog::factory()->create(['project_id' => $project->id]);

        $this->assertInstanceOf(Project::class, $timeLog->project);
        $this->assertEquals($project->id, $timeLog->project->id);
    }

    public function test_calculate_hours_method(): void
    {
        $timeLog = new TimeLog();
        $timeLog->start_time = Carbon::parse('2024-01-01 09:00:00');
        $timeLog->end_time = Carbon::parse('2024-01-01 17:00:00'); // 8 hours

        $timeLog->calculateHours();

        $this->assertEquals(8.0, $timeLog->hours);
    }

    public function test_calculate_hours_with_partial_hours(): void
    {
        $timeLog = new TimeLog();
        $timeLog->start_time = Carbon::parse('2024-01-01 09:00:00');
        $timeLog->end_time = Carbon::parse('2024-01-01 11:30:00'); // 2.5 hours

        $timeLog->calculateHours();

        $this->assertEquals(2.5, $timeLog->hours);
    }

    public function test_calculate_hours_with_null_end_time(): void
    {
        $timeLog = new TimeLog();
        $timeLog->start_time = Carbon::parse('2024-01-01 09:00:00');
        $timeLog->end_time = null;

        $timeLog->calculateHours();

        $this->assertNull($timeLog->hours);
    }

    public function test_time_log_factory_creates_valid_time_log(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create(['user_id' => $user->id]);
        $project = Project::factory()->create(['client_id' => $client->id]);
        
        $timeLog = TimeLog::factory()->create(['project_id' => $project->id]);

        $this->assertNotNull($timeLog->project_id);
        $this->assertNotNull($timeLog->start_time);
        $this->assertNotNull($timeLog->end_time);
        $this->assertNotNull($timeLog->description);
        $this->assertNotNull($timeLog->hours);
        $this->assertIsBool($timeLog->is_billable);
        $this->assertGreaterThan(0, $timeLog->hours);
    }

    public function test_factory_states(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create(['user_id' => $user->id]);
        $project = Project::factory()->create(['client_id' => $client->id]);
        
        // Test running state
        $runningLog = TimeLog::factory()->running()->create(['project_id' => $project->id]);
        $this->assertNotNull($runningLog->start_time);
        $this->assertNull($runningLog->end_time);
        $this->assertNull($runningLog->hours);
        
        // Test billable state
        $billableLog = TimeLog::factory()->billable()->create(['project_id' => $project->id]);
        $this->assertTrue($billableLog->is_billable);
    }
} 