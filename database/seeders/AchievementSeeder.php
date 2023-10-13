<?php

namespace Database\Seeders;

use App\Models\Achievement;
use Illuminate\Database\Seeder;

class AchievementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // NOTE need to create a separate table for achievements types
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
            Achievement::create([
                'type' => 'lessons_watched',
                'name' => $lessons_watched_achievement['name'],
                'count_to_reach' => $lessons_watched_achievement['count_to_reach'],
            ]);
        }

        foreach ($comments_written_achievements as $comments_written_achievement) {
            Achievement::create([
                'type' => 'comments_written',
                'name' => $comments_written_achievement['name'],
                'count_to_reach' => $comments_written_achievement['count_to_reach'],
            ]);
        }
    }
}