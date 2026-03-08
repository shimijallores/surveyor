<?php

namespace App\Http\Requests\Survey;

use App\Enums\SurveyQuestionType;
use App\Models\Survey;
use App\Models\SurveyQuestion;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class SubmitSurveyResponseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        /** @var Survey $survey */
        $survey = $this->route('survey');

        return $survey->isPublished() && ! $survey->isClosed() && $this->session()->get($this->sessionKey($survey), false);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'answers' => ['required', 'array', 'min:1'],
            'answers.*' => ['nullable'],
        ];
    }

    /**
     * @return array<int, \Closure(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                /** @var Survey $survey */
                $survey = $this->route('survey');
                $answers = $this->input('answers', []);

                $survey->loadMissing('questions.options');

                foreach ($survey->questions as $question) {
                    $value = $answers[(string) $question->id] ?? null;

                    if ($question->is_required && ($value === null || $value === '' || $value === [])) {
                        $validator->errors()->add("answers.{$question->id}", 'This question is required.');

                        continue;
                    }

                    if ($value === null || $value === '' || $value === []) {
                        continue;
                    }

                    $this->validateQuestionValue($validator, $question, $value);
                }
            },
        ];
    }

    protected function validateQuestionValue(Validator $validator, SurveyQuestion $question, mixed $value): void
    {
        match ($question->type) {
            SurveyQuestionType::OpenEnded => $this->validateOpenEnded($validator, $question, $value),
            SurveyQuestionType::YesNo => $this->validateYesNo($validator, $question, $value),
            SurveyQuestionType::MultipleChoice => $this->validateMultipleChoice($validator, $question, $value),
            SurveyQuestionType::RatingScale => $this->validateRating($validator, $question, $value),
            SurveyQuestionType::Ranking => $this->validateRanking($validator, $question, $value),
        };
    }

    protected function validateOpenEnded(Validator $validator, SurveyQuestion $question, mixed $value): void
    {
        if (! is_string($value) || mb_strlen(trim($value)) === 0) {
            $validator->errors()->add("answers.{$question->id}", 'Please enter a text response.');
        }
    }

    protected function validateYesNo(Validator $validator, SurveyQuestion $question, mixed $value): void
    {
        if (! is_bool($value)) {
            $validator->errors()->add("answers.{$question->id}", 'Please choose yes or no.');
        }
    }

    protected function validateMultipleChoice(Validator $validator, SurveyQuestion $question, mixed $value): void
    {
        $allowedIds = $question->options->pluck('id')->all();
        $submitted = is_array($value) ? $value : [$value];

        foreach ($submitted as $optionId) {
            if (! in_array((int) $optionId, $allowedIds, true)) {
                $validator->errors()->add("answers.{$question->id}", 'Please choose a valid option.');

                return;
            }
        }
    }

    protected function validateRating(Validator $validator, SurveyQuestion $question, mixed $value): void
    {
        $min = (int) ($question->settings['min'] ?? 1);
        $max = (int) ($question->settings['max'] ?? 5);

        if (! is_numeric($value) || (int) $value < $min || (int) $value > $max) {
            $validator->errors()->add("answers.{$question->id}", 'Please choose a valid rating.');
        }
    }

    protected function validateRanking(Validator $validator, SurveyQuestion $question, mixed $value): void
    {
        if (! is_array($value)) {
            $validator->errors()->add("answers.{$question->id}", 'Please submit a ranking for all options.');

            return;
        }

        $allowedIds = $question->options->pluck('id')->all();
        $submitted = array_map('intval', $value);

        sort($allowedIds);
        sort($submitted);

        if ($allowedIds !== $submitted) {
            $validator->errors()->add("answers.{$question->id}", 'Please rank every option exactly once.');
        }
    }

    protected function sessionKey(Survey $survey): string
    {
        return sprintf('survey_access.%s', $survey->public_id);
    }
}
