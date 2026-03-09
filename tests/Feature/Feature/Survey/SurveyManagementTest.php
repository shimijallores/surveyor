<?php

use App\Enums\SurveyQuestionType;
use App\Enums\SurveyStatus;
use App\Models\Survey;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Inertia\Testing\AssertableInertia as Assert;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\delete;
use function Pest\Laravel\get;
use function Pest\Laravel\post;
use function Pest\Laravel\put;

function surveyPayload(array $overrides = []): array
{
    return array_replace_recursive([
        'title' => 'Quarterly product feedback',
        'description' => 'Tell us how the release feels in real customer workflows.',
        'access_code' => 'launch-2026',
        'questions' => [
            [
                'type' => 'open_ended',
                'title' => 'What should we improve next?',
                'description' => 'Share the biggest friction point you noticed.',
                'is_required' => true,
                'position' => 0,
                'settings' => [],
                'options' => [],
            ],
            [
                'type' => 'rating_scale',
                'title' => 'How satisfied are you with the experience?',
                'description' => null,
                'is_required' => true,
                'position' => 1,
                'settings' => [
                    'min' => 1,
                    'max' => 5,
                    'min_label' => 'Poor',
                    'max_label' => 'Excellent',
                ],
                'options' => [],
            ],
        ],
    ], $overrides);
}

test('authenticated users can view their survey dashboard', function () {
    $user = User::factory()->create();
    $survey = Survey::factory()->for($user)->create(['title' => 'Team pulse']);

    actingAs($user);

    $response = get(route('dashboard'));

    $response
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('Dashboard')
                ->where('surveys.0.title', 'Team pulse')
                ->where('surveys.0.status', SurveyStatus::Draft->value)
                ->where('surveys.0.share_path', null)
                ->where('stats.total_surveys', 1),
        );
});

test('dashboard only includes recent surveys while library includes all surveys', function () {
    $user = User::factory()->create();

    foreach (range(1, 8) as $index) {
        Survey::factory()->for($user)->create([
            'title' => "Survey {$index}",
            'updated_at' => now()->addMinutes($index),
        ]);
    }

    actingAs($user);

    $dashboardResponse = get(route('dashboard'));
    $libraryResponse = get(route('surveys.index'));

    $dashboardResponse
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('Dashboard')
                ->has('surveys', 6)
                ->where('surveys.0.title', 'Survey 8')
                ->where('surveys.5.title', 'Survey 3')
                ->where('stats.total_surveys', 8),
        );

    $libraryResponse
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('surveys/Index')
                ->has('surveys', 8)
                ->where('surveys.0.title', 'Survey 8')
                ->where('surveys.7.title', 'Survey 1')
                ->where('stats.total_surveys', 8),
        );
});

test('authenticated users can create a survey', function () {
    $user = User::factory()->create();

    actingAs($user);

    $response = post(route('surveys.store'), surveyPayload());

    $survey = Survey::query()->first();

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('surveys.show', $survey));

    expect($survey)->not->toBeNull();
    expect($survey->title)->toBe('Quarterly product feedback');
    expect($survey->status)->toBe(SurveyStatus::Draft);
    expect(Hash::check('launch-2026', $survey->access_code_hash))->toBeTrue();
    expect($survey->questions()->count())->toBe(2);
});

test('survey creation preserves demographic question metadata', function () {
    $user = User::factory()->create();

    actingAs($user);

    post(route('surveys.store'), surveyPayload([
        'questions' => [
            [
                'type' => 'open_ended',
                'title' => 'Where do you currently live?',
                'description' => 'Share your city, region, or country.',
                'is_required' => false,
                'position' => 0,
                'settings' => [
                    'demographic_key' => 'location',
                ],
                'options' => [],
            ],
            [
                'type' => 'multiple_choice',
                'title' => 'What is your age range?',
                'description' => null,
                'is_required' => false,
                'position' => 1,
                'settings' => [
                    'allow_multiple' => false,
                    'demographic_key' => 'age_range',
                ],
                'options' => [
                    ['label' => '18-24', 'position' => 0],
                    ['label' => '25-34', 'position' => 1],
                ],
            ],
        ],
    ]));

    $survey = Survey::query()->with('questions')->firstOrFail();
    $locationQuestion = $survey->questions->firstWhere('title', 'Where do you currently live?');
    $ageQuestion = $survey->questions->firstWhere('title', 'What is your age range?');

    expect($locationQuestion)->not->toBeNull();
    expect($locationQuestion?->settings)->toMatchArray([
        'demographic_key' => 'location',
    ]);
    expect($ageQuestion)->not->toBeNull();
    expect($ageQuestion?->settings)->toMatchArray([
        'allow_multiple' => false,
        'demographic_key' => 'age_range',
    ]);
});

test('survey owners can update publish and close a survey', function () {
    $user = User::factory()->create();

    actingAs($user);

    post(route('surveys.store'), surveyPayload());
    $survey = Survey::query()->firstOrFail();

    $updatedPayload = surveyPayload();
    $updatedPayload['title'] = 'Updated customer pulse';
    $updatedPayload['access_code'] = 'rotated-code';
    $updatedPayload['questions'] = [
        [
            'type' => 'multiple_choice',
            'title' => 'Which feature matters most?',
            'description' => null,
            'is_required' => true,
            'position' => 0,
            'settings' => [
                'allow_multiple' => true,
            ],
            'options' => [
                ['label' => 'Automation', 'position' => 0],
                ['label' => 'Analytics', 'position' => 1],
            ],
        ],
    ];

    $updateResponse = put(route('surveys.update', $survey), $updatedPayload);

    $publishResponse = post(route('surveys.publish', $survey));
    $closeResponse = post(route('surveys.close', $survey));

    $updateResponse
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('surveys.edit', $survey));

    $publishResponse
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('surveys.show', $survey));

    $closeResponse
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('surveys.show', $survey));

    $survey->refresh();

    expect($survey->title)->toBe('Updated customer pulse');
    expect(Hash::check('rotated-code', $survey->access_code_hash))->toBeTrue();
    expect($survey->status)->toBe(SurveyStatus::Closed);
    expect($survey->published_at)->not->toBeNull();
    expect($survey->closed_at)->not->toBeNull();
    expect($survey->questions()->count())->toBe(1);
    expect($survey->questions()->first()?->options()->count())->toBe(2);
});

test('users cannot access another users survey analytics', function () {
    $owner = User::factory()->create();
    $intruder = User::factory()->create();
    $survey = Survey::factory()->for($owner)->create();

    actingAs($intruder);

    $response = get(route('surveys.show', $survey));

    $response->assertForbidden();
});

test('survey owners receive plain analytics props', function () {
    $owner = User::factory()->create();
    $survey = Survey::factory()->for($owner)->create([
        'title' => 'Analytics payload',
        'status' => SurveyStatus::Draft,
    ]);

    actingAs($owner);

    $response = get(route('surveys.show', $survey));

    $response
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('surveys/Analytics')
                ->where('survey.title', 'Analytics payload')
                ->where('survey.status', SurveyStatus::Draft->value)
                ->where('survey.share_path', null)
                ->where('analytics.summary.response_count', 0)
                ->where('analytics.summary.question_count', 0)
                ->has('analytics.summary.question_response_segments', 0)
                ->has('analytics.questions', 0),
        );
});

test('survey analytics include chart data and demographic metadata', function () {
    $owner = User::factory()->create();
    $survey = Survey::factory()->for($owner)->create([
        'title' => 'Community pulse',
        'status' => SurveyStatus::Published,
    ]);

    $locationQuestion = $survey->questions()->create([
        'type' => SurveyQuestionType::OpenEnded,
        'title' => 'Where do you currently live?',
        'description' => 'Share your city, region, or country.',
        'is_required' => false,
        'position' => 0,
        'settings' => [
            'demographic_key' => 'location',
        ],
    ]);

    $ageQuestion = $survey->questions()->create([
        'type' => SurveyQuestionType::MultipleChoice,
        'title' => 'What is your age range?',
        'description' => null,
        'is_required' => false,
        'position' => 1,
        'settings' => [
            'allow_multiple' => false,
            'demographic_key' => 'age_range',
        ],
    ]);

    $ageQuestion->options()->createMany([
        ['label' => '18-24', 'position' => 0],
        ['label' => '25-34', 'position' => 1],
    ]);

    $firstResponse = $survey->responses()->create([
        'is_completed' => true,
        'access_code_verified_at' => now(),
        'submitted_at' => now(),
    ]);

    $secondResponse = $survey->responses()->create([
        'is_completed' => true,
        'access_code_verified_at' => now(),
        'submitted_at' => now(),
    ]);

    $firstResponse->answers()->createMany([
        [
            'survey_question_id' => $locationQuestion->id,
            'text_value' => 'Manila',
        ],
        [
            'survey_question_id' => $ageQuestion->id,
            'json_value' => ['option_ids' => [$ageQuestion->options[0]->id]],
        ],
    ]);

    $secondResponse->answers()->createMany([
        [
            'survey_question_id' => $locationQuestion->id,
            'text_value' => 'Manila',
        ],
        [
            'survey_question_id' => $ageQuestion->id,
            'json_value' => ['option_ids' => [$ageQuestion->options[1]->id]],
        ],
    ]);

    actingAs($owner);

    get(route('surveys.show', $survey))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('surveys/Analytics')
                ->where('analytics.summary.response_count', 2)
                ->where('analytics.summary.completed_count', 2)
                ->where('analytics.summary.question_response_segments.0.label', 'Where do you currently live?')
                ->where('analytics.summary.question_response_segments.0.count', 2)
                ->where('analytics.questions.0.demographic_key', 'location')
                ->where('analytics.questions.0.response_count', 2)
                ->where('analytics.questions.0.segments.0.label', 'Manila')
                ->where('analytics.questions.0.segments.0.count', 2)
                ->where('analytics.questions.1.demographic_key', 'age_range')
                ->where('analytics.questions.1.response_count', 2)
                ->where('analytics.questions.1.segments.0.label', '18-24')
                ->where('analytics.questions.1.segments.0.count', 1)
                ->where('analytics.questions.1.segments.1.label', '25-34')
                ->where('analytics.questions.1.segments.1.count', 1),
        );
});

test('survey owners can delete a survey', function () {
    $owner = User::factory()->create();
    $survey = Survey::factory()->for($owner)->create();

    actingAs($owner);

    $response = delete(route('surveys.destroy', $survey));

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('dashboard'));

    expect(Survey::query()->find($survey->id))->toBeNull();
});
