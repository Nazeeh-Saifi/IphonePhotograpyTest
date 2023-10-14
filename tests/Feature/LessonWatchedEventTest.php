<?php

namespace Tests\Feature;

use App\Events\AchievementUnlocked;
use App\Events\LessonWatched;
use App\Listeners\LessonWatchedListener;
use App\Models\Achievement;
use App\Models\Lesson;
use App\Models\User;
use Database\Seeders\AchievementSeeder;
use Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class LessonWatchedEventTest extends TestCase
{
    use RefreshDatabase;
    /**
     * make sure that the LessonWatched event is dispatched with correct data
     */
    public function test_the_LessonWatchedEvent_is_fired(): void
    {
        Event::fake();

        $lesson = Lesson::factory()->create();
        $user = User::factory()->create();
        LessonWatched::dispatch($lesson, $user);

        Event::assertDispatched(
            LessonWatched::class,
            function (LessonWatched $event) use ($lesson, $user) {
                return ($user->is($event->user)) && ($lesson->is($event->lesson));
            }
        );
        // make sure that event -> listener are added to EventServiceProvider
        Event::assertListening(LessonWatched::class, LessonWatchedListener::class);
    }

    /**
     * make sure that the CommentWrittenListener handled the event
     */
    public function test_the_LessonWatchedListener_handled_the_event_and_fired_AchievementUnlocked_event(): void
    {
        Event::fake();
        $lesson = Lesson::factory()->create();
        $user = User::factory()->create();
        $user->lessons()->attach($lesson);

        $this->seed(AchievementSeeder::class);
        $achievement = Achievement::where('type', 'lessons_watched')->where('count_to_reach', 1)->first();

        $lesson_watched_event = new LessonWatched($lesson, $user);
        $lesson_watched_listener = new LessonWatchedListener();
        $lesson_watched_listener->handle($lesson_watched_event);

        $this->assertCount(1, $user->achievements);
        $this->assertTrue($user->achievements->contains($achievement));
        Event::assertDispatched(
            AchievementUnlocked::class,
            function (AchievementUnlocked $event) use ($achievement, $user) {
                return ($achievement->name === $event->achievement_name) && $event->user->is($user);
            }
        );
    }
}