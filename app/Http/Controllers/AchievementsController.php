<?php

namespace App\Http\Controllers;

use App\Models\Achievement;
use App\Models\Badge;
use App\Models\User;

class AchievementsController extends Controller
{
    public function index(User $user)
    {
        $unlocked_achievements = $user->achievements->pluck('name');

        $current_comments_written_achievement = $user->achievements()
            ->where('type', 'comments_written')
            ->orderBy('count_to_reach', 'desc')
            ->first();

        $current_lessons_watched_achievement = $user->achievements()
            ->where('type', 'lessons_watched')
            ->orderBy('count_to_reach', 'desc')
            ->first();

        $next_comments_written_achievement = Achievement::where('count_to_reach', '>', $current_comments_written_achievement?->count_to_reach ?? 0)
            ->where('type', 'comments_written')
            ->first();

        $next_lessons_watched_achievement = Achievement::where('count_to_reach', '>', $current_lessons_watched_achievement?->count_to_reach ?? 0)
            ->where('type', 'lessons_watched')
            ->first();

        $next_available_achievements = [];
        if (!is_null($next_comments_written_achievement)) {
            array_push($next_available_achievements, $next_comments_written_achievement->name);
        }

        if (!is_null($next_lessons_watched_achievement)) {
            array_push($next_available_achievements, $next_lessons_watched_achievement->name);
        }
        $current_badge = $user->badges()->orderBy('achievements_count', 'desc')->firstOrFail();

        $next_badge = Badge::where('achievements_count', '>', $current_badge->achievements_count)->orderBy('achievements_count')->first();

        $remaining_to_unlock_next_badge = 0;
        if (!is_null($next_badge)) {
            $remaining_to_unlock_next_badge = ($next_badge->achievements_count - $unlocked_achievements->count());
        }

        return response()->json([
            'unlocked_achievements' => $unlocked_achievements,
            'next_available_achievements' => $next_available_achievements,
            'current_badge' => $current_badge->name,
            'next_badge' => $next_badge?->name ?? '',
            'remaining_to_unlock_next_badge' => $remaining_to_unlock_next_badge,
        ]);
    }
}