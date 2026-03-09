<?php

namespace App\Concerns;

use App\Enums\SurveyQuestionType;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

trait SurveyValidationRules
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    protected function surveyRules(bool $accessCodeRequired = true): array
    {
        $questionTypes = array_map(
            static fn(SurveyQuestionType $type): string => $type->value,
            SurveyQuestionType::cases(),
        );

        return [
            'title' => ['required', 'string', 'max:160'],
            'description' => ['nullable', 'string', 'max:5000'],
            'access_code' => [$accessCodeRequired ? 'required' : 'nullable', 'string', 'min:4', 'max:64'],
            'questions' => ['required', 'array', 'min:1', 'max:50'],
            'questions.*.type' => ['required', 'string', Rule::in($questionTypes)],
            'questions.*.title' => ['required', 'string', 'max:255'],
            'questions.*.description' => ['nullable', 'string', 'max:1000'],
            'questions.*.is_required' => ['required', 'boolean'],
            'questions.*.position' => ['required', 'integer', 'min:0'],
            'questions.*.settings' => ['nullable', 'array'],
            'questions.*.settings.min' => ['nullable', 'integer', 'min:0', 'max:100'],
            'questions.*.settings.max' => ['nullable', 'integer', 'min:1', 'max:100'],
            'questions.*.settings.min_label' => ['nullable', 'string', 'max:60'],
            'questions.*.settings.max_label' => ['nullable', 'string', 'max:60'],
            'questions.*.settings.allow_multiple' => ['nullable', 'boolean'],
            'questions.*.settings.demographic_key' => ['nullable', 'string', Rule::in(['location', 'age_range'])],
            'questions.*.options' => ['nullable', 'array', 'max:20'],
            'questions.*.options.*.label' => ['required_with:questions.*.options', 'string', 'max:120'],
            'questions.*.options.*.position' => ['required_with:questions.*.options', 'integer', 'min:0'],
        ];
    }

    /**
     * @return array<int, \Closure(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $questions = $this->input('questions', []);

                foreach ($questions as $index => $question) {
                    $type = $question['type'] ?? null;
                    $options = $question['options'] ?? [];
                    $settings = $question['settings'] ?? [];

                    if (! is_string($type)) {
                        continue;
                    }

                    if (in_array($type, [SurveyQuestionType::MultipleChoice->value, SurveyQuestionType::Ranking->value], true) && count($options) < 2) {
                        $validator->errors()->add("questions.{$index}.options", 'This question type requires at least two options.');
                    }

                    if ($type === SurveyQuestionType::RatingScale->value) {
                        $min = $settings['min'] ?? null;
                        $max = $settings['max'] ?? null;

                        if (! is_int($min) || ! is_int($max) || $min >= $max) {
                            $validator->errors()->add("questions.{$index}.settings.max", 'Rating scales require a valid minimum and maximum range.');
                        }
                    }

                    if ($type === SurveyQuestionType::YesNo->value && ! empty($options)) {
                        $validator->errors()->add("questions.{$index}.options", 'Yes or no questions do not accept custom options.');
                    }
                }
            },
        ];
    }
}
