<?php

namespace App\Http\Controllers\Survey;

use App\Enums\SurveyQuestionType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Survey\SubmitSurveyResponseRequest;
use App\Http\Requests\Survey\VerifySurveyAccessCodeRequest;
use App\Http\Resources\PublicSurveyResource;
use App\Models\Survey;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

class PublicSurveyController extends Controller
{
    public function access(Request $request, Survey $survey): Response
    {
        $survey->load('questions.options');

        return Inertia::render('surveys/PublicAccess', [
            'survey' => (new PublicSurveyResource($survey))->resolve($request),
            'canRespond' => $request->session()->get($this->sessionKey($survey), false),
            'status' => $request->session()->get('status'),
        ]);
    }

    public function verify(VerifySurveyAccessCodeRequest $request, Survey $survey): RedirectResponse
    {
        if (! $survey->isPublished() || $survey->isClosed()) {
            abort(404);
        }

        if (! Hash::check($request->validated('access_code'), $survey->access_code_hash)) {
            return back()->withErrors([
                'access_code' => 'The access code is incorrect.',
            ]);
        }

        $request->session()->put($this->sessionKey($survey), true);

        return to_route('surveys.public.respond', $survey->public_id);
    }

    public function respond(Request $request, Survey $survey): Response|RedirectResponse
    {
        if (! $request->session()->get($this->sessionKey($survey), false)) {
            return to_route('surveys.public.access.show', $survey->public_id);
        }

        if (! $survey->isPublished() || $survey->isClosed()) {
            abort(404);
        }

        $survey->load('questions.options');

        return Inertia::render('surveys/PublicRespond', [
            'survey' => (new PublicSurveyResource($survey))->resolve($request),
            'inviteMessage' => "You've been invited as a participant for this survey. Answer each question once, then submit when you're ready.",
        ]);
    }

    public function submit(SubmitSurveyResponseRequest $request, Survey $survey): RedirectResponse
    {
        DB::transaction(function () use ($request, $survey): void {
            $survey->load('questions.options');

            $response = $survey->responses()->create([
                'is_completed' => true,
                'access_code_verified_at' => now(),
                'submitted_at' => now(),
                'metadata' => [
                    'user_agent' => $request->userAgent(),
                ],
            ]);

            foreach ($survey->questions as $question) {
                $value = $request->validated('answers')[(string) $question->id] ?? null;

                if ($value === null || $value === '' || $value === []) {
                    continue;
                }

                $payload = [
                    'text_value' => null,
                    'numeric_value' => null,
                    'boolean_value' => null,
                    'json_value' => null,
                ];

                match ($question->type) {
                    SurveyQuestionType::OpenEnded => $payload['text_value'] = trim((string) $value),
                    SurveyQuestionType::YesNo => $payload['boolean_value'] = (bool) $value,
                    SurveyQuestionType::MultipleChoice => $payload['json_value'] = [
                        'option_ids' => array_map('intval', is_array($value) ? $value : [$value]),
                    ],
                    SurveyQuestionType::RatingScale => $payload['numeric_value'] = (int) $value,
                    SurveyQuestionType::Ranking => $payload['json_value'] = [
                        'ranked_option_ids' => array_map('intval', $value),
                    ],
                };

                $response->answers()->create([
                    'survey_question_id' => $question->id,
                    ...$payload,
                ]);
            }

            $survey->update([
                'last_response_at' => now(),
            ]);
        });

        return to_route('surveys.public.access.show', $survey->public_id)->with('status', 'response-submitted');
    }

    protected function sessionKey(Survey $survey): string
    {
        return sprintf('survey_access.%s', $survey->public_id);
    }
}
