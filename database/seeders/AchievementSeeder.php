<?php

namespace Database\Seeders;

use App\Models\Achievement;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
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

        foreach ($lessons_watched_achievements as $lessons_watched_achievement) {
            Achievement::create(['type' => 'lessons_watched', 'name' => $lessons_watched_achievement]);
        }

        foreach ($comments_written_achievements as $comments_written_achievement) {
            Achievement::create(['type' => 'comments_written', 'name' => $comments_written_achievement]);
        }
    }
}