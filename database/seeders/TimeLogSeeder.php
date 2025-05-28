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
        // Get the 3 projects
        $websiteProject = Project::where('title', 'Website Redesign')->first();
        $appProject = Project::where('title', 'Mobile App Development')->first();
        $securityProject = Project::where('title', 'Security Audit')->first();

        // Create 4 time logs for Website Redesign project
        $this->createTimeLog(
            $websiteProject->id,
            Carbon::now()->subDays(5)->setHour(9)->setMinute(0),
            Carbon::now()->subDays(5)->setHour(12)->setMinute(30),
            'Initial design mockups and wireframes',
            true
        );

        $this->createTimeLog(
            $websiteProject->id,
            Carbon::now()->subDays(3)->setHour(13)->setMinute(0),
            Carbon::now()->subDays(3)->setHour(17)->setMinute(0),
            'Frontend development - homepage',
            true
        );

        $this->createTimeLog(
            $websiteProject->id,
            Carbon::now()->subDays(2)->setHour(10)->setMinute(0),
            Carbon::now()->subDays(2)->setHour(15)->setMinute(30),
            'Responsive design implementation',
            true
        );

        $this->createTimeLog(
            $websiteProject->id,
            Carbon::now()->subDay()->setHour(9)->setMinute(0),
            Carbon::now()->subDay()->setHour(11)->setMinute(0),
            'Client feedback meeting and revisions',
            true
        );

        // Create 3 time logs for Mobile App Development project
        $this->createTimeLog(
            $appProject->id,
            Carbon::now()->subDays(4)->setHour(10)->setMinute(0),
            Carbon::now()->subDays(4)->setHour(16)->setMinute(0),
            'App architecture planning',
            true
        );

        $this->createTimeLog(
            $appProject->id,
            Carbon::now()->subDays(2)->setHour(9)->setMinute(0),
            Carbon::now()->subDays(2)->setHour(13)->setMinute(0),
            'UI/UX design for main screens',
            true
        );

        $this->createTimeLog(
            $appProject->id,
            Carbon::now()->subDay()->setHour(14)->setMinute(0),
            Carbon::now()->subDay()->setHour(18)->setMinute(0),
            'API integration planning',
            false
        );

        // Create 3 time logs for Security Audit project
        $this->createTimeLog(
            $securityProject->id,
            Carbon::now()->subDays(3)->setHour(9)->setMinute(0),
            Carbon::now()->subDays(3)->setHour(17)->setMinute(0),
            'Initial security assessment and planning',
            true
        );

        $this->createTimeLog(
            $securityProject->id,
            Carbon::now()->subDays(2)->setHour(10)->setMinute(0),
            Carbon::now()->subDays(2)->setHour(16)->setMinute(0),
            'Vulnerability scanning and analysis',
            true
        );

        $this->createTimeLog(
            $securityProject->id,
            Carbon::now()->subDay()->setHour(13)->setMinute(0),
            Carbon::now()->subDay()->setHour(18)->setMinute(0),
            'Report preparation and recommendations',
            true
        );
    }

    /**
     * Helper method to create a time log
     */
    private function createTimeLog(int $projectId, Carbon $startTime, Carbon $endTime, string $description, bool $isBillable): void
    {
        $timeLog = new TimeLog([
            'project_id' => $projectId,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'description' => $description,
            'is_billable' => $isBillable,
        ]);
        
        $timeLog->calculateHours();
        $timeLog->save();
    }
} 