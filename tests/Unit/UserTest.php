<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_relationships(): void
    {
        $user = User::factory()->create();
        $clients = Client::factory()->count(3)->create(['user_id' => $user->id]);

        $this->assertCount(3, $user->clients);
        $this->assertInstanceOf(Client::class, $user->clients->first());
    }

    public function test_password_hashing(): void
    {
        $user = User::factory()->create([
            'password' => 'plain_password',
        ]);

        $this->assertTrue(Hash::check('plain_password', $user->password));
        $this->assertNotEquals('plain_password', $user->password);
    }

    public function test_api_token_creation(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token');

        $this->assertNotNull($token->plainTextToken);
        $this->assertStringContainsString('|', $token->plainTextToken);
    }
} 