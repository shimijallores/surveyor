<?php

namespace App\Http\Requests\Survey;

use App\Concerns\SurveyValidationRules;
use App\Models\Survey;
use Illuminate\Foundation\Http\FormRequest;

class UpdateSurveyRequest extends FormRequest
{
    use SurveyValidationRules;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        /** @var Survey $survey */
        $survey = $this->route('survey');

        return $this->user()?->can('update', $survey) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return $this->surveyRules(false);
    }
}
