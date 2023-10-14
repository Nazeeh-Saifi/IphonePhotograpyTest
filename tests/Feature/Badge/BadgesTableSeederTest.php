<?php

namespace Tests\Feature\Badge;

use Database\Seeders\BadgeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class BadgesTableSeederTest extends TestCase
{
    use RefreshDatabase;
    /**
     * test badges table seeder works
     */
    public function test_that_badges_table_has_initial_values(): void
    {
        $badges = [
            ['name' => 'Beginner', 'achievements_count' => 0],
            ['name' => 'Intermediate', 'achievements_count' => 4],
            ['name' => 'Advanced', 'achievements_count' => 8],
            ['name' => 'Master', 'achievements_count' => 10],
        ];
        //$this->seed(BadgeSeeder::class);

        foreach ($badges as $badge) {
            $this->assertDatabaseHas('badges', ['name' => $badge['name'], 'achievements_count' => $badge['achievements_count']]);
        }


    }
}