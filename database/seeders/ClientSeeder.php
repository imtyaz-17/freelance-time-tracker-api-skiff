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
        $user = User::where('email', 'imtyaz@example.com')->first();

        // Create exactly 2 clients for the demo user
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
    }
} 