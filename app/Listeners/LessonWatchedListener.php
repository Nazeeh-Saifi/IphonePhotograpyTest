<?php

namespace App\Listeners;

use App\Events\AchievementUnlocked;
use App\Events\LessonWatched;
use App\Models\Achievement;
use App\Models\Lesson;
use DB;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class LessonWatchedListener
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(LessonWatched $event): void
    {
        // get user model
        $user = $event->user;

        //get number of lessons
        $number_of_lessons = DB::table('lesson_user')->where('user_id', $user->id)->count();

        // get the achievement using number of comments or null
        $achievement = $this->getAchievementByNumberOfLessonsAndType($number_of_lessons, 'lessons_watched');

        $user->achievements()->attach($achievement);

        if (!is_null($achievement)) {
            AchievementUnlocked::dispatch($achievement->name, $user);
        }
    }

    function getAchievementByNumberOfLessonsAndType(int $number_of_lessons, string $type): Achievement|null
    {
        $achievement = Achievement::
            where('count_to_reach', $number_of_lessons)
            ->where('type', 'lessons_watched')
            ->first();

        return $achievement;
    }
}