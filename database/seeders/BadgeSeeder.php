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
        $badges_names = [
            'Beginner',
            'Intermediate',
            'Advanced',
            'Master',
        ];
        foreach ($badges_names as $badge_name) {
            Badge::create(["name" => $badge_name]);
        }
    }
}