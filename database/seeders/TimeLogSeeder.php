<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\TimeLog;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class TimeLogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all active projects
        $projects = Project::where('status', 'active')->get();

        // For each project, create some time logs
        $projects->each(function ($project) {
            // Create 5-15 time logs per project
            $numLogs = rand(5, 15);
            
            for ($i = 0; $i < $numLogs; $i++) {
                // Generate random start time within the last 30 days
                $startTime = Carbon::now()->subDays(rand(0, 30))->subHours(rand(0, 23));
                
                // Random duration between 30 minutes and 4 hours
                $durationHours = rand(30, 240) / 60;
                $endTime = (clone $startTime)->addMinutes($durationHours * 60);
                
                // Create the time log
                $timeLog = new TimeLog([
                    'project_id' => $project->id,
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                    'description' => fake()->sentence(),
                    'is_billable' => rand(0, 10) > 2, // 80% chance of being billable
                ]);
                
                $timeLog->calculateHours();
                $timeLog->save();
            }
            
            // Create a few logs for today for the demo user's projects
            if ($project->client->user->email === 'demo@example.com') {
                for ($i = 0; $i < 3; $i++) {
                    $startTime = Carbon::now()->subHours(rand(1, 8));
                    $durationHours = rand(30, 180) / 60;
                    $endTime = (clone $startTime)->addMinutes($durationHours * 60);
                    
                    $timeLog = new TimeLog([
                        'project_id' => $project->id,
                        'start_time' => $startTime,
                        'end_time' => $endTime,
                        'description' => fake()->sentence(),
                        'is_billable' => true,
                    ]);
                    
                    $timeLog->calculateHours();
                    $timeLog->save();
                }
                
                // Add one ongoing timer (no end_time)
                if (rand(0, 1) === 1) {
                    $timeLog = new TimeLog([
                        'project_id' => $project->id,
                        'start_time' => Carbon::now()->subMinutes(rand(5, 60)),
                        'description' => 'Working on ' . $project->title,
                        'is_billable' => true,
                    ]);
                    
                    $timeLog->save();
                }
            }
        });
    }
} 