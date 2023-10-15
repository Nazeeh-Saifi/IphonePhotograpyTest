<?php

namespace Tests\Feature;

use App\Events\AchievementUnlocked;
use App\Events\BadgeUnlocked;
use App\Events\CommentWritten;
use App\Listeners\CommentWrittenListener;
use App\Models\Achievement;
use App\Models\Badge;
use App\Models\Comment;
use App\Models\User;
use Database\Seeders\AchievementSeeder;
use Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommentWrittenTest extends TestCase
{
    use RefreshDatabase;

    /**
     * make sure that the CommentWritten event is dispatched with correct data
     */
    public function test_the_CommentWrittenEvent_is_fired(): void
    {
        Event::fake();

        $comment = Comment::factory()->create();
        CommentWritten::dispatch($comment);

        Event::assertDispatched(
            CommentWritten::class,
            function (CommentWritten $event) use ($comment) {
                return ($comment->id === $event->comment->id) && ($comment->user_id === $event->comment->user_id);
            }
        );
        // make sure that event -> listener are added to EventServiceProvider
        Event::assertListening(CommentWritten::class, CommentWrittenListener::class);
    }

    /**
     * make sure that the CommentWrittenListener handled the event
     */
    public function test_the_CommentWrittenListener_handled_the_event_and_fired_AchievementUnlocked_event(): void
    {
        Event::fake();
        $comment = Comment::factory()->create();
        $user = $comment->user;
        //$this->seed(AchievementSeeder::class);
        $achievement = Achievement::where('type', 'comments_written')->where('count_to_reach', 1)->first();

        $comment_written_event = new CommentWritten($comment);
        $comment_written_listener = new CommentWrittenListener();
        $comment_written_listener->handle($comment_written_event);

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
     * make sure that the CommentWrittenListener did not fire when achievement not reached
     */
    public function test_the_CommentWrittenListener_did_not_fire_when_achievement_not_reached(): void
    {
        //$this->seed(AchievementSeeder::class);
        $user = User::factory()->create();
        $comments = Comment::factory()->count(2)->create(["user_id" => $user->id]);

        Event::fake();
        $comment_written_listener = new CommentWrittenListener();
        $comment_written_event = new CommentWritten($comments[1]);
        $comment_written_listener->handle($comment_written_event);

        Event::assertNotDispatched(AchievementUnlocked::class);
    }

    public function test_the_number_of_previous_comments_written_achievements_is_correct_having_one_previous_achievement(): void
    {
        //$this->seed(AchievementSeeder::class);
        $user = User::factory()->create();
        $comment_written_listener = new CommentWrittenListener();

        for ($i = 0; $i < 2; $i++) {
            $comment = Comment::factory()->create(['user_id' => $user->id]);
            $comment_written_event = new CommentWritten($comment);
            $comment_written_listener->handle($comment_written_event);
        }

        $this->assertCount(2, $user->comments);
        $this->assertCount(1, $user->achievements);
    }

    public function test_the_number_of_previous_comments_written_achievements_is_correct_having_more_than_one_previous_achievement(): void
    {
        //$this->seed(AchievementSeeder::class);
        $user = User::factory()->create();
        $comment_written_listener = new CommentWrittenListener();

        for ($i = 0; $i < 5; $i++) {
            $comment = Comment::factory()->create(['user_id' => $user->id]);
            $comment_written_event = new CommentWritten($comment);
            $comment_written_listener->handle($comment_written_event);
        }

        $this->assertCount(5, $user->comments);
        $this->assertCount(3, $user->achievements);
    }

    public function test_the_CommentWrittenListener_fired_BadgeUnlocked_event(): void
    {
        $user = User::factory()->create();
        $comment_written_listener = new CommentWrittenListener();
        $badge = Badge::where('achievements_count', 4)->first();

        Event::fake();
        for ($i = 0; $i < 10; $i++) {
            $comment = Comment::factory()->create(['user_id' => $user->id]);
            $comment_written_event = new CommentWritten($comment);
            $comment_written_listener->handle($comment_written_event);
        }

        $this->assertCount(4, $user->achievements);
        $this->assertCount(2, $user->badges);
        Event::assertDispatched(
            BadgeUnlocked::class,
            function (BadgeUnlocked $event) use ($badge, $user) {
                return ($event->badge_name === $badge->name) && $user->is($event->user);
            }
        );
    }


}