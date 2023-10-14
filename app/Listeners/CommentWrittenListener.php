<?php

namespace App\Listeners;

use App\Events\AchievementUnlocked;
use App\Events\CommentWritten;
use App\Models\Achievement;
use App\Models\Comment;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class CommentWrittenListener
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
    public function handle(CommentWritten $event): void
    {
        // get user model
        $user_id = $event->comment->user_id;
        $user = User::findOrFail($user_id);

        //get number of comments
        $number_of_comments = Comment::where('user_id', $user->id)->count();

        // get the achievement using number of comments or null
        $achievement = $this->getAchievementByNumberOfCommentsAndType($number_of_comments, 'comments_written');

        $user->achievements()->attach([$achievement->id]);

        if (!is_null($achievement)) {
            AchievementUnlocked::dispatch($achievement->name, $user);
        }

    }

    function getAchievementByNumberOfCommentsAndType(int $number_of_comments, string $type): Achievement|null
    {
        $achievement = Achievement::
            where('count_to_reach', $number_of_comments)
            ->where('type', 'comments_written')
            ->first();

        return $achievement;
    }
}