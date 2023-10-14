<?php

namespace Tests\Feature;

use App\Events\AchievementUnlocked;
use App\Events\CommentWritten;
use App\Listeners\CommentWrittenListener;
use App\Models\Achievement;
use App\Models\Comment;
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
        $this->seed(AchievementSeeder::class);
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

}