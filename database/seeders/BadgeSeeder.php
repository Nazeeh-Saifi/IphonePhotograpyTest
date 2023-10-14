<?php

namespace Database\Seeders;

use App\Models\Badge;
use Illuminate\Database\Seeder;

class BadgeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $badges = [
            ['name' => 'Beginner', 'achievements_count' => 0],
            ['name' => 'Intermediate', 'achievements_count' => 4],
            ['name' => 'Advanced', 'achievements_count' => 8],
            ['name' => 'Master', 'achievements_count' => 10],
        ];
        foreach ($badges as $badge) {
            Badge::create(["name" => $badge['name'], 'achievements_count' => $badge['achievements_count']]);
        }
    }
}