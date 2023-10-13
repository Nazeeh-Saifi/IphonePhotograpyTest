<?php

namespace Tests\Feature;

use Database\Seeders\BadgeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class BadgesTableSeederTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     */
    public function test_that_badges_table_has_initial_values(): void
    {
        $badges_names = [
            'Beginner',
            'Intermediate',
            'Advanced',
            'Master',
        ];
        $this->seed(BadgeSeeder::class);

        foreach ($badges_names as $badge_name) {
            $this->assertDatabaseHas('badges', ['name' => $badge_name]);
        }


    }
}