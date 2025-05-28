<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Project;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ProjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // For each client, create 1-4 projects
        Client::all()->each(function ($client) {
            $numProjects = rand(1, 4);
            for ($i = 0; $i < $numProjects; $i++) {
                $isActive = rand(0, 1) === 1;
                
                Project::create([
                    'client_id' => $client->id,
                    'title' => fake()->catchPhrase(),
                    'description' => fake()->paragraph(),
                    'status' => $isActive ? 'active' : 'completed',
                    'deadline' => $isActive ? Carbon::now()->addDays(rand(7, 60)) : Carbon::now()->subDays(rand(1, 30)),
                ]);
            }
        });

        // Create specific projects for the demo user's clients
        $acmeClient = Client::where('name', 'Acme Corporation')->first();
        if ($acmeClient) {
            Project::create([
                'client_id' => $acmeClient->id,
                'title' => 'Website Redesign',
                'description' => 'Complete overhaul of the company website with modern design and improved UX.',
                'status' => 'active',
                'deadline' => Carbon::now()->addDays(30),
            ]);

            Project::create([
                'client_id' => $acmeClient->id,
                'title' => 'Mobile App Development',
                'description' => 'Creating a new mobile app for customer engagement.',
                'status' => 'active',
                'deadline' => Carbon::now()->addDays(60),
            ]);
        }

        $wayneClient = Client::where('name', 'Wayne Enterprises')->first();
        if ($wayneClient) {
            Project::create([
                'client_id' => $wayneClient->id,
                'title' => 'Security Audit',
                'description' => 'Comprehensive security audit of all IT systems.',
                'status' => 'active',
                'deadline' => Carbon::now()->addDays(15),
            ]);
        }
    }
} 