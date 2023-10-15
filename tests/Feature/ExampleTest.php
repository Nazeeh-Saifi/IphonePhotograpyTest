<?php

namespace Tests\Feature;

use App\Models\Achievement;
use App\Models\Badge;
use App\Models\User;
// use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response_and_have_5_keys(): void
    {
        $user = User::factory()->create();

        $response = $this->get("/users/{$user->id}/achievements");

        $response->assertOk()->assertJsonCount(5);
    }

    public function test_the_application_returns_a_successful_response_and_correct_structure(): void
    {
        $user = User::factory()->create();

        $response = $this->get("/users/{$user->id}/achievements");

        $response->assertOk()->assertJsonStructure([
            'unlocked_achievements' => [],
            'next_available_achievements' => [],
            'current_badge',
            'next_badge',
            'remaining_to_unlock_next_badge',
        ]);
    }

    public function test_the_application_returns_a_successful_response_and_correct_response_data_for_newly_created_user(): void
    {
        $user = User::factory()->create();
        $beginner_badge = Badge::where('name', 'Beginner')->first();
        $intermediate_badge = Badge::where('name', 'Intermediate')->first();
        $first_comments_written_achievement = Achievement::where('type', 'comments_written')->where('count_to_reach', 1)->first();
        $first_lessons_watched_achievement = Achievement::where('type', 'lessons_watched')->where('count_to_reach', 1)->first();

        $response = $this->get("/users/{$user->id}/achievements");

        $response->assertOk()->assertJsonFragment([
            'unlocked_achievements' => $user->achievements->pluck('name'),
            'next_available_achievements' => [$first_comments_written_achievement->name, $first_lessons_watched_achievement->name],
            'current_badge' => $beginner_badge->name,
            'next_badge' => $intermediate_badge->name,
            'remaining_to_unlock_next_badge' => $intermediate_badge->achievements_count - $beginner_badge->achievements_count,
        ]);
    }

    public function test_the_application_returns_a_successful_response_and_correct_response_data_for_user_with_first_comment_written_achievement(): void
    {
        $user = User::factory()->create();
        $first_comments_written_achievement = Achievement::where('type', 'comments_written')->where('count_to_reach', 1)->first();
        $three_comments_written_achievement = Achievement::where('type', 'comments_written')->where('count_to_reach', 3)->first();
        $first_lessons_watched_achievement = Achievement::where('type', 'lessons_watched')->where('count_to_reach', 1)->first();
        $user->achievements()->attach($first_comments_written_achievement);

        $response = $this->get("/users/{$user->id}/achievements");

        $response->assertOk()->assertJsonFragment([
            'unlocked_achievements' => $user->achievements->pluck('name'),
            'next_available_achievements' => [$three_comments_written_achievement->name, $first_lessons_watched_achievement->name],
        ]);
    }

    public function test_the_application_returns_a_successful_response_and_correct_response_data_for_user_with_first_lesson_watched_achievement(): void
    {
        $user = User::factory()->create();
        $first_comments_written_achievement = Achievement::where('type', 'comments_written')->where('count_to_reach', 1)->first();
        $first_lesson_watched_achievement = Achievement::where('type', 'lessons_watched')->where('count_to_reach', 1)->get();
        $five_lessons_watched_achievement = Achievement::where('type', 'lessons_watched')->where('count_to_reach', 5)->first();
        $user->achievements()->attach($first_lesson_watched_achievement);

        $response = $this->get("/users/{$user->id}/achievements");

        $response->assertOk()
            ->assertJsonIsArray("unlocked_achievements")
            ->assertJsonCount(1, 'unlocked_achievements')
            ->assertJsonFragment([
                'unlocked_achievements' => $first_lesson_watched_achievement->pluck('name')->toArray(),
                'next_available_achievements' => [$five_lessons_watched_achievement->name, $first_comments_written_achievement->name],
            ]);
    }

    public function test_the_application_returns_a_successful_response_and_correct_response_data_for_user_with_all_lessons_watched_achievements(): void
    {
        $user = User::factory()->create();
        $first_comments_written_achievement = Achievement::where('type', 'comments_written')->where('count_to_reach', 1)->first();
        $all_lessons_watched_achievements = Achievement::where('type', 'lessons_watched')->get();

        $user->achievements()->attach($all_lessons_watched_achievements);

        $response = $this->get("/users/{$user->id}/achievements");

        $response->assertOk()
            ->assertJsonIsArray("unlocked_achievements")
            ->assertJsonCount($all_lessons_watched_achievements->count(), 'unlocked_achievements')
            ->assertJsonFragment([
                'unlocked_achievements' => $all_lessons_watched_achievements->pluck('name')->toArray(),
                'next_available_achievements' => [$first_comments_written_achievement->name],
            ]);

    }

    public function test_the_application_returns_a_successful_response_and_correct_response_data_for_user_with_all_comments_written_achievements(): void
    {
        $user = User::factory()->create();
        $first_lessons_watched_achievement = Achievement::where('type', 'lessons_watched')->where('count_to_reach', 1)->first();
        $all_comments_written_achievements = Achievement::where('type', 'comments_written')->get();

        $user->achievements()->attach($all_comments_written_achievements);

        $response = $this->get("/users/{$user->id}/achievements");

        $response->assertOk()
            ->assertJsonIsArray("unlocked_achievements")
            ->assertJsonCount($all_comments_written_achievements->count(), 'unlocked_achievements')
            ->assertJsonFragment([
                'unlocked_achievements' => $all_comments_written_achievements->pluck('name')->toArray(),
                'next_available_achievements' => [$first_lessons_watched_achievement->name],
            ]);
    }


    public function test_the_application_returns_a_successful_response_and_correct_response_data_for_user_with_all_achievements(): void
    {
        $user = User::factory()->create();
        $all_achievements = Achievement::all();
        $user->achievements()->attach($all_achievements);

        $response = $this->get("/users/{$user->id}/achievements");

        $response->assertOk()
            ->assertJsonIsArray("unlocked_achievements")
            ->assertJsonCount($all_achievements->count(), 'unlocked_achievements')
            ->assertJsonFragment([
                'unlocked_achievements' => $all_achievements->pluck('name')->toArray(),
                'next_available_achievements' => [],
            ]);
    }

    public function test_the_application_returns_a_successful_response_and_correct_response_data_for_user_with_all_badges(): void
    {
        $user = User::factory()->create();
        $all_badges = Badge::all();
        $current_badge = Badge::orderBy('achievements_count', 'desc')->first();
        $user->badges()->attach($all_badges);

        $response = $this->get("/users/{$user->id}/achievements");

        $response->assertOk()
            ->assertJsonFragment([
                'current_badge' => $current_badge->name,
                'next_badge' => '',
                'remaining_to_unlock_next_badge' => 0,
            ]);
    }


}