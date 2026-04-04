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
            'categories',
            'questions.options',
            'questions.category',
            'questions.answers',
            'responses.answers',
        ]);

        $questions = $survey->questions
            ->sortBy('position')
            ->values()
            ->map(fn(SurveyQuestion $question): array => $this->questionAnalytics($question));

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
                'question_response_segments' => $questions
                    ->map(fn(array $question): array => [
                        'label' => $question['title'],
                        'count' => $question['response_count'],
                        'percentage' => $responses->isEmpty()
                            ? 0
                            : (int) round(($question['response_count'] / $responses->count()) * 100),
                    ])
                    ->all(),
                'category_response_segments' => $survey->categories
                    ->sortBy('position')
                    ->values()
                    ->map(fn($category): array => [
                        'label' => $category->name,
                        'count' => $survey->questions
                            ->where('survey_category_id', $category->id)
                            ->sum(fn(SurveyQuestion $question): int => $question->answers->count()),
                    ])
                    ->all(),
            ],
            'questions' => $questions->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function baseQuestionAnalytics(SurveyQuestion $question): array
    {
        return [
            'question_id' => $question->id,
            'type' => $question->type->value,
            'title' => $question->title,
            'description' => $question->description,
            'response_count' => $question->answers->count(),
            'demographic_key' => $question->settings['demographic_key'] ?? null,
            'category_id' => $question->category?->id,
            'category_name' => $question->category?->name,
        ];
    }

    /**
     * @param  array<string, int>  $segments
     * @return array<int, array<string, int|string>>
     */
    protected function countedSegments(array $segments): array
    {
        $total = array_sum($segments);

        return collect($segments)
            ->map(fn(int $count, string $label): array => [
                'label' => $label,
                'count' => $count,
                'percentage' => $total === 0 ? 0 : (int) round(($count / $total) * 100),
            ])
            ->values()
            ->all();
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
            ->map(static fn(string $value): string => trim($value))
            ->filter()
            ->values();

        $segments = $answers
            ->groupBy(static fn(string $value): string => mb_strtolower($value))
            ->map(fn($group): array => [
                'label' => $group->first(),
                'count' => $group->count(),
            ])
            ->sortByDesc('count')
            ->values()
            ->take(6)
            ->map(fn(array $segment): array => [
                ...$segment,
                'percentage' => $answers->isEmpty()
                    ? 0
                    : (int) round(($segment['count'] / $answers->count()) * 100),
            ]);

        return [
            ...$this->baseQuestionAnalytics($question),
            'responses' => $answers->all(),
            'segments' => $segments->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function yesNoAnalytics(SurveyQuestion $question): array
    {
        $answers = $question->answers;

        return [
            ...$this->baseQuestionAnalytics($question),
            'segments' => $this->countedSegments([
                'Yes' => $answers->where('boolean_value', true)->count(),
                'No' => $answers->where('boolean_value', false)->count(),
            ]),
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

                return [$option->label => $count];
            });

        return [
            ...$this->baseQuestionAnalytics($question),
            'segments' => $this->countedSegments($counts->collapse()->all()),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function ratingScaleAnalytics(SurveyQuestion $question): array
    {
        $ratings = $question->answers
            ->pluck('numeric_value')
            ->filter(static fn($value): bool => is_int($value))
            ->values();

        $min = (int) ($question->settings['min'] ?? 1);
        $max = (int) ($question->settings['max'] ?? 5);

        $segments = collect(range($min, $max))
            ->mapWithKeys(fn(int $value): array => [
                (string) $value => $ratings->filter(fn(int $rating): bool => $rating === $value)->count(),
            ]);

        return [
            ...$this->baseQuestionAnalytics($question),
            'average' => $ratings->isEmpty() ? null : round($ratings->avg(), 2),
            'segments' => $this->countedSegments($segments->all()),
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
            ...$this->baseQuestionAnalytics($question),
            'segments' => $totals->all(),
        ];
    }
}
