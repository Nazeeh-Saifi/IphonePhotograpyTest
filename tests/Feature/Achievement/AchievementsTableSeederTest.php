<?php

namespace Tests\Feature\Achievement;

use Database\Seeders\AchievementSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AchievementsTableSeederTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     */
    public function test_the_achievements_table_has_initial_values(): void
    {
        $this->seed(AchievementSeeder::class);

        $lessons_watched_achievements = [
            ['count_to_reach' => 1, 'name' => 'First Lesson Watched'],
            ['count_to_reach' => 5, 'name' => '5 Lessons Watched'],
            ['count_to_reach' => 10, 'name' => '10 Lessons Watched'],
            ['count_to_reach' => 25, 'name' => '25 Lessons Watched'],
            ['count_to_reach' => 50, 'name' => '50 Lessons Watched'],
        ];

        $comments_written_achievements = [
            ['count_to_reach' => 1, 'name' => 'First Comment Written'],
            ['count_to_reach' => 3, 'name' => '3 Comments Written'],
            ['count_to_reach' => 5, 'name' => '5 Comments Written'],
            ['count_to_reach' => 10, 'name' => '10 Comments Written'],
            ['count_to_reach' => 20, 'name' => '20 Comments Written'],
        ];


        foreach ($lessons_watched_achievements as $lessons_watched_achievement) {
            $this->assertDatabaseHas('achievements', [
                'type' => 'lessons_watched',
                'name' => $lessons_watched_achievement['name'],
                'count_to_reach' => $lessons_watched_achievement['count_to_reach']
            ]);
        }

        foreach ($comments_written_achievements as $comments_written_achievement) {
            $this->assertDatabaseHas('achievements', [
                'type' => 'comments_written',
                'name' => $comments_written_achievement['name'],
                'count_to_reach' => $comments_written_achievement['count_to_reach']
            ]);
        }
    }
}