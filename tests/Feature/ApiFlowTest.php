<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_full_api_flow(): void
    {
        // Health
        $this->getJson('/api/health')
            ->assertOk()
            ->assertJson(['ok' => true, 'db' => true]);

        // Protected routes require auth (checked before any login)
        $this->getJson('/api/me')->assertUnauthorized();

        // Register (seeds system categories + Daily Check-In + referral code)
        $res = $this->postJson('/api/auth/register', [
            'email' => 'alex@example.com',
            'password' => 'password123',
            'fullName' => 'Alex',
        ]);
        $res->assertOk()->assertJsonStructure(['user' => ['id', 'email', 'referralCode'], 'token']);
        $token = $res->json('token');
        $auth = ['Authorization' => 'Bearer '.$token];

        // Me
        $this->getJson('/api/me', $auth)->assertOk()->assertJsonPath('email', 'alex@example.com');

        // Seeded Daily Check-In present
        $this->getJson('/api/bits', $auth)->assertOk()->assertJsonFragment(['title' => 'Daily Check-In']);

        // Create a bit (camelCase in, camelCase out)
        $create = $this->postJson('/api/bits', [
            'title' => 'Walk 30 min',
            'startDate' => '2026-10-17',
            'goalPerWeek' => 5,
            'reminderDays' => [1, 2, 3],
        ], $auth);
        $create->assertSuccessful()
            ->assertJsonPath('title', 'Walk 30 min')
            ->assertJsonPath('goalPerWeek', 5)
            ->assertJsonPath('startDate', '2026-10-17')
            ->assertJsonPath('reminderDays', [1, 2, 3]);
        $bitId = $create->json('id');

        // Upsert an entry (check it off)
        $this->putJson('/api/entries', [
            'bitId' => $bitId, 'date' => '2026-10-17', 'status' => 'done',
        ], $auth)->assertSuccessful()->assertJsonPath('status', 'done');

        // Entries list for that bit
        $this->getJson('/api/entries?bitId='.$bitId, $auth)
            ->assertOk()->assertJsonCount(1)->assertJsonPath('0.bitId', $bitId);

        // PATCH the bit's goal → logs a bit-change
        $this->patchJson('/api/bits/'.$bitId, ['goalPerWeek' => 6], $auth)
            ->assertOk()->assertJsonPath('goalPerWeek', 6);
        $this->getJson('/api/bits/all-changes', $auth)
            ->assertOk()->assertJsonFragment(['field' => 'goal', 'toVal' => '6']);
    }
}
