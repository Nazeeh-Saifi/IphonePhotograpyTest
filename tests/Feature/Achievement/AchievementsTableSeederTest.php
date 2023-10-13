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
        $lessons_watched_achievements = [
            'First Lesson Watched',
            '5 Lessons Watched',
            '10 Lessons Watched',
            '25 Lessons Watched',
            '50 Lessons Watched',
        ];

        $comments_written_achievements = [
            'First Comment Written',
            '3 Comments Written',
            '5 Comments Written',
            '10 Comments Written',
            '20 Comments Written',
        ];

        $this->seed(AchievementSeeder::class);

        foreach ($lessons_watched_achievements as $lessons_watched_achievement) {
            $this->assertDatabaseHas('achievements', ['type' => 'lessons_watched', 'name' => $lessons_watched_achievement]);
        }

        foreach ($comments_written_achievements as $comments_written_achievement) {
            $this->assertDatabaseHas('achievements', ['type' => 'comments_written', 'name' => $comments_written_achievement]);
        }
    }
}