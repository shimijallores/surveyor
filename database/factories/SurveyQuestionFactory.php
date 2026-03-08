<?php

namespace Database\Factories;

use App\Enums\SurveyQuestionType;
use App\Models\Survey;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SurveyQuestion>
 */
class SurveyQuestionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'survey_id' => Survey::factory(),
            'type' => SurveyQuestionType::OpenEnded,
            'title' => fake()->sentence(6),
            'description' => fake()->optional()->sentence(),
            'is_required' => fake()->boolean(60),
            'position' => 0,
            'settings' => [],
        ];
    }
}
