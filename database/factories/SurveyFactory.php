<?php

namespace Database\Factories;

use App\Enums\SurveyStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Survey>
 */
class SurveyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'public_id' => (string) Str::ulid(),
            'title' => fake()->sentence(4),
            'description' => fake()->sentence(10),
            'access_code_hash' => Hash::make('survey123'),
            'status' => SurveyStatus::Draft,
            'published_at' => null,
            'closed_at' => null,
            'last_response_at' => null,
        ];
    }

    public function published(): static
    {
        return $this->state(fn (): array => [
            'status' => SurveyStatus::Published,
            'published_at' => now(),
        ]);
    }
}
