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
        // Get the two clients
        $client1 = Client::where('name', 'Acme Corporation')->first();
        $client2 = Client::where('name', 'Wayne Enterprises')->first();

        // Create 2 projects for Ckien 1
        Project::create([
            'client_id' => $client1->id,
            'title' => 'Website Redesign',
            'description' => 'Complete overhaul of the company website with modern design and improved UX.',
            'status' => 'active',
            'deadline' => Carbon::now()->addDays(30),
        ]);

        Project::create([
            'client_id' => $client1->id,
            'title' => 'Mobile App Development',
            'description' => 'Creating a new mobile app for customer engagement.',
            'status' => 'active',
            'deadline' => Carbon::now()->addDays(60),
        ]);

        // Create 1 project for Client 2
        Project::create([
            'client_id' => $client2->id,
            'title' => 'Security Audit',
            'description' => 'Comprehensive security audit of all IT systems.',
            'status' => 'active',
            'deadline' => Carbon::now()->addDays(15),
        ]);
    }
} 