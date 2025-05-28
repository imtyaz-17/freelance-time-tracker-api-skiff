<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create a demo user
        User::create([
            'name' => 'Imtyaz Ahmed',
            'email' => 'imtyaz@example.com',
            'password' => Hash::make('password'),
        ]);
    }
} 