<?php

namespace Tests\Feature;

use App\Models\Badge;
use App\Models\Comment;
use App\Models\User;
use Database\Seeders\BadgeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class BadgeAndUserRelationshipTest extends TestCase
{
    use RefreshDatabase;


    /**
     * test user and badge relationship attaching/detaching
     */
    public function test_the_user_can_have_badges(): void
    {
        // Create a user
        $user = User::withoutEvents(function () {
            return User::factory()->create();
        });

        // seed the badges 
        //$this->seed(BadgeSeeder::class);

        // get a number of badges
        $number_of_badges = 2;
        $badges = Badge::all()->random($number_of_badges);

        // attach user to a number of badges
        $user->badges()->attach($badges);

        // assert that the user has the attached badges
        $this->assertCount($number_of_badges, $user->badges);
        foreach ($badges as $badge) {
            $this->assertTrue($user->badges->contains($badge));
        }


        // keep copy to assert false
        $detached_badge = $badges->first();

        // detach a badge from the user
        $user->badges()->detach($detached_badge);

        // reload the user from the database to refresh the relationships
        $user->refresh();

        // remove the badge also from badges array
        $badges->shift();

        // Assert that the user no longer has the detached badge
        $this->assertCount(1, $user->badges);
        $this->assertFalse($user->badges->contains($detached_badge));
        foreach ($badges as $badge) {
            $this->assertTrue($user->badges->contains($badge));
        }

    }

    public function test_the_newly_created_user_have_beginner_badge(): void
    {
        $beginner_badge = Badge::where('achievements_count', 0)->first();

        $user = User::factory()->create();
        $badge = $user->badges()->first();

        $this->assertCount(1, $user->badges);
        $this->assertTrue($beginner_badge->is($badge));
    }

    public function test_the_newly_created_user_by_comment_factory_have_beginner_badge(): void
    {
        $beginner_badge = Badge::where('achievements_count', 0)->first();

        $user = Comment::factory()->create()->user;
        $badge = $user->badges()->first();

        $this->assertCount(1, $user->badges);
        $this->assertTrue($beginner_badge->is($badge));
    }


}