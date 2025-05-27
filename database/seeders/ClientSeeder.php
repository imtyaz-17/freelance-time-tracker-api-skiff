<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\User;
use Illuminate\Database\Seeder;

class ClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the demo user
        $user = User::where('email', 'demo@example.com')->first();

        // Create some clients for the demo user
        Client::create([
            'user_id' => $user->id,
            'name' => 'Acme Corporation',
            'email' => 'info@acme.com',
            'contact_person' => 'John Doe',
        ]);

        Client::create([
            'user_id' => $user->id,
            'name' => 'Wayne Enterprises',
            'email' => 'contact@wayne.com',
            'contact_person' => 'Bruce Wayne',
        ]);

        Client::create([
            'user_id' => $user->id,
            'name' => 'Stark Industries',
            'email' => 'hello@stark.com',
            'contact_person' => 'Tony Stark',
        ]);

        // For each of the other users, create 1-3 clients
        User::where('email', '!=', 'demo@example.com')->each(function ($user) {
            $numClients = rand(1, 3);
            for ($i = 0; $i < $numClients; $i++) {
                Client::create([
                    'user_id' => $user->id,
                    'name' => fake()->company(),
                    'email' => fake()->companyEmail(),
                    'contact_person' => fake()->name(),
                ]);
            }
        });
    }
} 