<?php

namespace Tests\Feature;

use App\Events\AchievementUnlocked;
use App\Events\BadgeUnlocked;
use App\Events\LessonWatched;
use App\Listeners\LessonWatchedListener;
use App\Models\Achievement;
use App\Models\Badge;
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
        $user->lessons()->attach($lesson, ['watched' => 1]);

        //$this->seed(AchievementSeeder::class);
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

    /**
     * make sure that the LessonWatchedListener did not fire when achievement not reached
     */
    public function test_the_LessonWatchedListener_did_not_fire_when_achievement_not_reached(): void
    {
        //$this->seed(AchievementSeeder::class);
        $user = User::factory()->create();
        $lessons = Lesson::factory()->count(2)->create();
        $user->lessons()->attach($lessons);
        Event::fake();

        $lesson_watched_listener = new LessonWatchedListener();
        $lesson_watched_event = new LessonWatched($lessons[1], $user);
        $lesson_watched_listener->handle($lesson_watched_event);

        Event::assertNotDispatched(AchievementUnlocked::class);
    }

    public function test_the_number_of_previous_lessons_watched_achievements_is_correct_having_one_achievement(): void
    {
        //$this->seed(AchievementSeeder::class);
        $user = User::factory()->create();
        $lesson_watched_listener = new LessonWatchedListener();

        $lesson1 = Lesson::factory()->create();
        $user->lessons()->attach($lesson1, ['watched' => 1]);
        $lesson_watched_event1 = new LessonWatched($lesson1, $user);
        $lesson_watched_listener->handle($lesson_watched_event1);

        $lesson2 = Lesson::factory()->create();
        $user->lessons()->attach($lesson2, ['watched' => 1]);
        $lesson_watched_event2 = new LessonWatched($lesson2, $user);
        $lesson_watched_listener->handle($lesson_watched_event2);

        $this->assertCount(2, $user->lessons);
        $this->assertCount(1, $user->achievements);
    }

    public function test_the_number_of_previous_lessons_watched_achievements_is_correct_having_more_than_one_achievement(): void
    {
        //$this->seed(AchievementSeeder::class);
        $user = User::factory()->create();
        $lesson_watched_listener = new LessonWatchedListener();
        $lessons = Lesson::factory()->count(6)->create();

        foreach ($lessons as $lesson) {
            $user->lessons()->attach($lesson, ['watched' => 1]);
            $lesson_watched_event = new LessonWatched($lesson, $user);
            $lesson_watched_listener->handle($lesson_watched_event);
        }

        $this->assertCount(6, $user->lessons);
        $this->assertCount(2, $user->achievements);
    }


    public function test_the_LessonsWatchedListener_fired_BadgeUnlocked_event(): void
    {
        $user = User::factory()->create();
        $lesson_watched_listener = new LessonWatchedListener();
        $lessons = Lesson::factory()->count(25)->create();
        $badge = Badge::where('achievements_count', 4)->first();

        Event::fake();
        foreach ($lessons as $lesson) {
            $user->lessons()->attach($lesson, ['watched' => 1]);
            $lesson_watched_event = new LessonWatched($lesson, $user);
            $lesson_watched_listener->handle($lesson_watched_event);
        }

        $this->assertCount(25, $user->lessons);
        $this->assertCount(4, $user->achievements);
        Event::assertDispatched(
            BadgeUnlocked::class,
            function (BadgeUnlocked $event) use ($badge, $user) {
                return ($event->badge_name === $badge->name) && $user->is($event->user);
            }
        );
    }

}