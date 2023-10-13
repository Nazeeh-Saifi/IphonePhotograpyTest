<?php

namespace Tests\Feature\Achievement;

use App\Models\Achievement;
use App\Models\User;
use Database\Seeders\AchievementSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AchievementAndUserRelationshipTest extends TestCase
{
    /**
     * test user and achievement relationship attaching/detaching
     */
    public function test_the_user_can_have_achievements(): void
    {
        // Create a user
        $user = User::factory()->create();

        // seed the achievements 
        $this->seed(AchievementSeeder::class);

        // get a number of achievements
        $number_of_achievements = 2;
        $achievements = Achievement::all()->random($number_of_achievements);

        // attach user to a number of achievements
        $user->achievements()->attach($achievements);

        // assert that the user has the attached achievements
        $this->assertCount($number_of_achievements, $user->achievements);
        foreach ($achievements as $achievement) {
            $this->assertTrue($user->achievements->contains($achievement));
        }


        // keep copy to assert false
        $detached_achievement = $achievements->first();

        // detach a achievement from the user
        $user->achievements()->detach($detached_achievement);

        // reload the user from the database to refresh the relationships
        $user->refresh();

        // remove the achievement also from achievements array
        $achievements->shift();

        // Assert that the user no longer has the detached achievement
        $this->assertCount(1, $user->achievements);
        $this->assertFalse($user->achievements->contains($detached_achievement));
        foreach ($achievements as $achievement) {
            $this->assertTrue($user->achievements->contains($achievement));
        }

    }
}