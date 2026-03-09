<?php

use App\Enums\SurveyStatus;
use App\Models\Survey;
use Database\Seeders\ExampleSurveySeeder;

test('example survey seeder creates philippines-themed surveys with fake responses', function () {
    $this->artisan('db:seed', ['--class' => ExampleSurveySeeder::class])->assertExitCode(0);

    $surveys = Survey::query()
        ->with(['questions.options', 'responses.answers'])
        ->whereIn('title', [
            'Philippines Presidential Pulse 2028',
            'Philippine Food Research Study 2026',
        ])
        ->get();

    expect($surveys)->toHaveCount(2);

    $presidentialSurvey = $surveys->firstWhere('title', 'Philippines Presidential Pulse 2028');
    $foodSurvey = $surveys->firstWhere('title', 'Philippine Food Research Study 2026');

    expect($presidentialSurvey)->not->toBeNull();
    expect($presidentialSurvey?->status)->toBe(SurveyStatus::Published);
    expect($presidentialSurvey?->questions)->toHaveCount(6);
    expect($presidentialSurvey?->responses)->toHaveCount(12);
    expect($presidentialSurvey?->responses->first()?->answers)->toHaveCount(6);

    expect($foodSurvey)->not->toBeNull();
    expect($foodSurvey?->status)->toBe(SurveyStatus::Published);
    expect($foodSurvey?->questions)->toHaveCount(6);
    expect($foodSurvey?->responses)->toHaveCount(10);
    expect($foodSurvey?->responses->first()?->answers)->toHaveCount(6);

    $this->artisan('db:seed', ['--class' => ExampleSurveySeeder::class])->assertExitCode(0);

    expect(
        Survey::query()->whereIn('title', [
            'Philippines Presidential Pulse 2028',
            'Philippine Food Research Study 2026',
        ])->count(),
    )->toBe(2);
});
