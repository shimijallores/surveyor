<?php

namespace App\Services;

use App\Enums\SurveyQuestionType;
use App\Models\Survey;
use App\Models\SurveyQuestion;

class SurveyAnalyticsService
{
    /**
     * @return array<string, mixed>
     */
    public function build(Survey $survey): array
    {
        $survey->loadMissing([
            'questions.options',
            'questions.answers',
            'responses.answers',
        ]);

        $questions = $survey->questions
            ->sortBy('position')
            ->values()
            ->map(fn (SurveyQuestion $question): array => $this->questionAnalytics($question));

        $responses = $survey->responses;
        $completedResponses = $responses->where('is_completed', true);

        return [
            'summary' => [
                'response_count' => $responses->count(),
                'completed_count' => $completedResponses->count(),
                'question_count' => $survey->questions->count(),
                'completion_rate' => $responses->isEmpty()
                    ? 0
                    : (int) round(($completedResponses->count() / $responses->count()) * 100),
            ],
            'questions' => $questions->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function questionAnalytics(SurveyQuestion $question): array
    {
        return match ($question->type) {
            SurveyQuestionType::OpenEnded => $this->openEndedAnalytics($question),
            SurveyQuestionType::YesNo => $this->yesNoAnalytics($question),
            SurveyQuestionType::MultipleChoice => $this->multipleChoiceAnalytics($question),
            SurveyQuestionType::RatingScale => $this->ratingScaleAnalytics($question),
            SurveyQuestionType::Ranking => $this->rankingAnalytics($question),
        };
    }

    /**
     * @return array<string, mixed>
     */
    protected function openEndedAnalytics(SurveyQuestion $question): array
    {
        $answers = $question->answers()
            ->whereNotNull('text_value')
            ->pluck('text_value')
            ->filter()
            ->values();

        return [
            'question_id' => $question->id,
            'type' => $question->type->value,
            'title' => $question->title,
            'description' => $question->description,
            'responses' => $answers->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function yesNoAnalytics(SurveyQuestion $question): array
    {
        $answers = $question->answers;

        return [
            'question_id' => $question->id,
            'type' => $question->type->value,
            'title' => $question->title,
            'description' => $question->description,
            'segments' => [
                ['label' => 'Yes', 'count' => $answers->where('boolean_value', true)->count()],
                ['label' => 'No', 'count' => $answers->where('boolean_value', false)->count()],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function multipleChoiceAnalytics(SurveyQuestion $question): array
    {
        $counts = $question->options
            ->sortBy('position')
            ->values()
            ->map(function ($option) use ($question): array {
                $count = $question->answers
                    ->filter(function ($answer) use ($option): bool {
                        $values = $answer->json_value['option_ids'] ?? [];

                        return in_array($option->id, $values, true);
                    })
                    ->count();

                return [
                    'label' => $option->label,
                    'count' => $count,
                ];
            });

        return [
            'question_id' => $question->id,
            'type' => $question->type->value,
            'title' => $question->title,
            'description' => $question->description,
            'segments' => $counts->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function ratingScaleAnalytics(SurveyQuestion $question): array
    {
        $ratings = $question->answers
            ->pluck('numeric_value')
            ->filter(static fn ($value): bool => is_int($value))
            ->values();

        $min = (int) ($question->settings['min'] ?? 1);
        $max = (int) ($question->settings['max'] ?? 5);

        $segments = collect(range($min, $max))
            ->map(fn (int $value): array => [
                'label' => (string) $value,
                'count' => $ratings->filter(fn (int $rating): bool => $rating === $value)->count(),
            ]);

        return [
            'question_id' => $question->id,
            'type' => $question->type->value,
            'title' => $question->title,
            'description' => $question->description,
            'average' => $ratings->isEmpty() ? null : round($ratings->avg(), 2),
            'segments' => $segments->all(),
            'scale' => [
                'min' => $min,
                'max' => $max,
                'min_label' => $question->settings['min_label'] ?? null,
                'max_label' => $question->settings['max_label'] ?? null,
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function rankingAnalytics(SurveyQuestion $question): array
    {
        $totals = $question->options
            ->sortBy('position')
            ->values()
            ->map(function ($option) use ($question): array {
                $score = $question->answers
                    ->reduce(function (int $carry, $answer) use ($option): int {
                        $orderedIds = $answer->json_value['ranked_option_ids'] ?? [];
                        $position = array_search($option->id, $orderedIds, true);

                        if ($position === false) {
                            return $carry;
                        }

                        return $carry + ((count($orderedIds) - $position) * 10);
                    }, 0);

                return [
                    'label' => $option->label,
                    'score' => $score,
                ];
            });

        return [
            'question_id' => $question->id,
            'type' => $question->type->value,
            'title' => $question->title,
            'description' => $question->description,
            'segments' => $totals->all(),
        ];
    }
}
