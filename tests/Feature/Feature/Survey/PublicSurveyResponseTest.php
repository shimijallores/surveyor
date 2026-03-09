<?php

use App\Enums\SurveyQuestionType;
use App\Enums\SurveyStatus;
use App\Models\Survey;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Inertia\Testing\AssertableInertia as Assert;

use function Pest\Laravel\get;
use function Pest\Laravel\post;

function publishedSurvey(): Survey
{
    $survey = Survey::factory()
        ->for(User::factory())
        ->published()
        ->create([
            'access_code_hash' => Hash::make('secret-123'),
            'status' => SurveyStatus::Published,
        ]);

    $openEnded = $survey->questions()->create([
        'type' => SurveyQuestionType::OpenEnded,
        'title' => 'Tell us your favorite detail',
        'description' => null,
        'is_required' => true,
        'position' => 0,
        'settings' => [],
    ]);

    $choice = $survey->questions()->create([
        'type' => SurveyQuestionType::MultipleChoice,
        'title' => 'Which capabilities stand out?',
        'description' => null,
        'is_required' => true,
        'position' => 1,
        'settings' => ['allow_multiple' => true],
    ]);

    $choice->options()->createMany([
        ['label' => 'Automation', 'position' => 0],
        ['label' => 'Reporting', 'position' => 1],
    ]);

    $rating = $survey->questions()->create([
        'type' => SurveyQuestionType::RatingScale,
        'title' => 'Rate the overall experience',
        'description' => null,
        'is_required' => true,
        'position' => 2,
        'settings' => [
            'min' => 1,
            'max' => 5,
            'min_label' => 'Low',
            'max_label' => 'High',
        ],
    ]);

    $ranking = $survey->questions()->create([
        'type' => SurveyQuestionType::Ranking,
        'title' => 'Rank these priorities',
        'description' => null,
        'is_required' => true,
        'position' => 3,
        'settings' => [],
    ]);

    $ranking->options()->createMany([
        ['label' => 'Speed', 'position' => 0],
        ['label' => 'Clarity', 'position' => 1],
        ['label' => 'Depth', 'position' => 2],
    ]);

    return $survey->fresh('questions.options');
}

test('participants must enter the correct access code before responding', function () {
    $survey = publishedSurvey();

    $response = post(route('surveys.public.access.verify', $survey->public_id), [
        'access_code' => 'wrong-code',
    ]);

    $response
        ->assertSessionHasErrors('access_code')
        ->assertRedirect();
});

test('draft surveys are not accessible on public routes', function () {
    $survey = Survey::factory()
        ->for(User::factory())
        ->create([
            'access_code_hash' => Hash::make('secret-123'),
            'status' => SurveyStatus::Draft,
        ]);

    get(route('surveys.public.access.show', $survey->public_id))->assertNotFound();

    post(route('surveys.public.access.verify', $survey->public_id), [
        'access_code' => 'secret-123',
    ])->assertNotFound();
});

test('participants receive plain props on the public access page', function () {
    $survey = publishedSurvey();

    $response = get(route('surveys.public.access.show', $survey->public_id));

    $response
        ->assertOk()
        ->assertInertia(
            fn(Assert $page) => $page
                ->component('surveys/PublicAccess')
                ->where('survey.public_id', $survey->public_id)
                ->where('survey.status', SurveyStatus::Published->value)
                ->where('survey.questions.0.title', 'Tell us your favorite detail')
                ->where('canRespond', false),
        );
});

test('participants receive plain props on the public response page after unlock', function () {
    $survey = publishedSurvey();

    post(route('surveys.public.access.verify', $survey->public_id), [
        'access_code' => 'secret-123',
    ]);

    $response = get(route('surveys.public.respond', $survey->public_id));

    $response
        ->assertOk()
        ->assertInertia(
            fn(Assert $page) => $page
                ->component('surveys/PublicRespond')
                ->where('survey.public_id', $survey->public_id)
                ->where('inviteMessage', "You've been invited as a participant for this survey. Answer each question once, then submit when you're ready.")
                ->where('survey.questions.0.title', 'Tell us your favorite detail')
                ->has('survey.questions', 4),
        );
});

test('participants can unlock and submit a survey response', function () {
    $survey = publishedSurvey();
    $choiceQuestion = $survey->questions->firstWhere('type', SurveyQuestionType::MultipleChoice);
    $rankingQuestion = $survey->questions->firstWhere('type', SurveyQuestionType::Ranking);

    $unlockResponse = post(route('surveys.public.access.verify', $survey->public_id), [
        'access_code' => 'secret-123',
    ]);

    $submitResponse = post(route('surveys.public.submit', $survey->public_id), [
        'answers' => [
            (string) $survey->questions[0]->id => 'The visual hierarchy feels clean and modern.',
            (string) $choiceQuestion->id => $choiceQuestion->options->pluck('id')->all(),
            (string) $survey->questions[2]->id => 5,
            (string) $rankingQuestion->id => $rankingQuestion->options->pluck('id')->all(),
        ],
    ]);

    $unlockResponse->assertRedirect(route('surveys.public.respond', $survey->public_id));

    $submitResponse
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('surveys.public.access.show', $survey->public_id));

    $survey->refresh();

    expect($survey->responses()->count())->toBe(1);
    expect($survey->responses()->first()?->answers()->count())->toBe(4);
    expect($survey->last_response_at)->not->toBeNull();
});
