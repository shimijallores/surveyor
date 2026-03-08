<?php

namespace App\Http\Requests\Survey;

use App\Models\Survey;
use Illuminate\Foundation\Http\FormRequest;

class VerifySurveyAccessCodeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        /** @var Survey $survey */
        $survey = $this->route('survey');

        return ! $survey->isClosed();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'access_code' => ['required', 'string', 'min:4', 'max:64'],
        ];
    }
}
