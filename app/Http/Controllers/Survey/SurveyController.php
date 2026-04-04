<?php

namespace App\Http\Controllers\Survey;

use App\Enums\SurveyQuestionType;
use App\Enums\SurveyStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Survey\StoreSurveyRequest;
use App\Http\Requests\Survey\UpdateSurveyRequest;
use App\Http\Resources\SurveyAnalyticsResource;
use App\Http\Resources\SurveyBuilderResource;
use App\Http\Resources\SurveySummaryResource;
use App\Models\Survey;
use App\Services\SurveyAnalyticsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

class SurveyController extends Controller
{
    protected const RECENT_SURVEYS_LIMIT = 6;

    public function __construct(private readonly SurveyAnalyticsService $analyticsService) {}

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Survey::class);

        $surveys = $this->surveyQuery($request)
            ->limit(self::RECENT_SURVEYS_LIMIT)
            ->get();

        return Inertia::render('Dashboard', [
            'surveys' => SurveySummaryResource::collection($surveys)->resolve($request),
            'stats' => $this->surveyStats($request),
            'questionTypes' => $this->questionTypes(),
        ]);
    }

    public function library(Request $request): Response
    {
        $this->authorize('viewAny', Survey::class);

        $surveys = $this->surveyQuery($request)->get();

        return Inertia::render('surveys/Index', [
            'surveys' => SurveySummaryResource::collection($surveys)->resolve($request),
            'stats' => $this->surveyStats($request),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Survey::class);

        return Inertia::render('surveys/Builder', [
            'mode' => 'create',
            'survey' => [
                'id' => null,
                'public_id' => null,
                'title' => '',
                'description' => '',
                'access_code' => null,
                'status' => SurveyStatus::Draft->value,
                'published_at' => null,
                'closed_at' => null,
                'share_path' => null,
                'categories' => [],
                'questions' => [],
            ],
            'questionTypes' => $this->questionTypes(),
        ]);
    }

    public function store(StoreSurveyRequest $request): RedirectResponse
    {
        $this->authorize('create', Survey::class);

        $survey = DB::transaction(function () use ($request): Survey {
            $survey = $request->user()->surveys()->create([
                'title' => $request->validated('title'),
                'description' => $request->validated('description'),
                'access_code_hash' => Hash::make($request->validated('access_code')),
                'access_code_ciphertext' => $request->validated('access_code'),
                'status' => SurveyStatus::Draft,
            ]);

            $this->syncQuestions(
                $survey,
                $request->validated('categories'),
                $request->validated('questions'),
            );

            return $survey->fresh(['categories', 'questions.category', 'questions.options']);
        });

        return to_route('surveys.show', $survey)->with('status', 'Survey created successfully.');
    }

    public function show(Survey $survey): Response
    {
        $this->authorize('view', $survey);

        $survey->load([
            'categories',
            'questions.options',
            'questions.category',
            'questions.answers',
            'responses.answers',
        ]);

        return Inertia::render('surveys/Analytics', [
            'survey' => (new SurveyBuilderResource($survey))->resolve(request()),
            'analytics' => (new SurveyAnalyticsResource($this->analyticsService->build($survey)))->resolve(request()),
        ]);
    }

    public function edit(Survey $survey): Response
    {
        $this->authorize('update', $survey);

        $survey->load(['categories', 'questions.category', 'questions.options']);

        return Inertia::render('surveys/Builder', [
            'mode' => 'edit',
            'survey' => (new SurveyBuilderResource($survey))->resolve(request()),
            'questionTypes' => $this->questionTypes(),
        ]);
    }

    public function update(UpdateSurveyRequest $request, Survey $survey): RedirectResponse
    {
        $this->authorize('update', $survey);

        DB::transaction(function () use ($request, $survey): void {
            $attributes = [
                'title' => $request->validated('title'),
                'description' => $request->validated('description'),
            ];

            if (filled($request->validated('access_code'))) {
                $attributes['access_code_hash'] = Hash::make($request->validated('access_code'));
                $attributes['access_code_ciphertext'] = $request->validated('access_code');
            }

            $survey->update($attributes);
            $this->syncQuestions(
                $survey,
                $request->validated('categories'),
                $request->validated('questions'),
            );
        });

        return to_route('surveys.edit', $survey)->with('status', 'Survey updated successfully.');
    }

    public function publish(Survey $survey): RedirectResponse
    {
        $this->authorize('publish', $survey);

        $survey->update([
            'status' => SurveyStatus::Published,
            'published_at' => now(),
            'closed_at' => null,
        ]);

        return to_route('surveys.show', $survey)->with('status', 'Survey published successfully.');
    }

    public function close(Survey $survey): RedirectResponse
    {
        $this->authorize('close', $survey);

        $survey->update([
            'status' => SurveyStatus::Closed,
            'closed_at' => now(),
        ]);

        return to_route('surveys.show', $survey)->with('status', 'Survey closed successfully.');
    }

    public function destroy(Survey $survey): RedirectResponse
    {
        $this->authorize('delete', $survey);

        $survey->delete();

        return to_route('dashboard')->with('status', 'Survey deleted successfully.');
    }

    /**
     * @param  array<int, array<string, mixed>>  $categories
     * @param  array<int, array<string, mixed>>  $questions
     */
    protected function syncQuestions(Survey $survey, array $categories, array $questions): void
    {
        $survey->questions()->delete();
        $survey->categories()->delete();

        $categoryMap = collect($categories)
            ->sortBy('position')
            ->values()
            ->mapWithKeys(function (array $categoryData) use ($survey): array {
                $category = $survey->categories()->create([
                    'name' => $categoryData['name'],
                    'description' => $categoryData['description'] ?? null,
                    'position' => $categoryData['position'],
                ]);

                return [$categoryData['client_key'] => $category];
            });

        collect($questions)
            ->sortBy('position')
            ->values()
            ->each(function (array $questionData) use ($survey, $categoryMap): void {
                $question = $survey->questions()->create([
                    'survey_category_id' => $categoryMap->get($questionData['category_client_key'])?->id,
                    'type' => $questionData['type'],
                    'title' => $questionData['title'],
                    'description' => $questionData['description'] ?? null,
                    'is_required' => $questionData['is_required'],
                    'position' => $questionData['position'],
                    'settings' => $questionData['settings'] ?? [],
                ]);

                collect($questionData['options'] ?? [])
                    ->sortBy('position')
                    ->values()
                    ->each(fn(array $optionData) => $question->options()->create([
                        'label' => $optionData['label'],
                        'position' => $optionData['position'],
                    ]));
            });
    }

    protected function surveyQuery(Request $request)
    {
        return $request->user()
            ->surveys()
            ->withCount([
                'questions',
                'responses',
                'responses as completed_responses_count' => fn($query) => $query->where('is_completed', true),
            ])
            ->latest('updated_at');
    }

    /**
     * @return array<string, int>
     */
    protected function surveyStats(Request $request): array
    {
        $surveys = $request->user()->surveys()->select(['id', 'status'])->withCount('responses')->get();

        return [
            'total_surveys' => $surveys->count(),
            'published_surveys' => $surveys->where('status', SurveyStatus::Published)->count(),
            'draft_surveys' => $surveys->where('status', SurveyStatus::Draft)->count(),
            'closed_surveys' => $surveys->where('status', SurveyStatus::Closed)->count(),
            'total_responses' => $surveys->sum('responses_count'),
        ];
    }

    /**
     * @return Collection<int, array<string, string>>
     */
    protected function questionTypes(): Collection
    {
        return collect([
            [
                'value' => SurveyQuestionType::OpenEnded->value,
                'label' => 'Open ended',
                'description' => 'Capture free-form written responses.',
            ],
            [
                'value' => SurveyQuestionType::YesNo->value,
                'label' => 'Yes or no',
                'description' => 'Ask for a fast binary decision.',
            ],
            [
                'value' => SurveyQuestionType::MultipleChoice->value,
                'label' => 'Multiple choice',
                'description' => 'Offer one or more predefined options.',
            ],
            [
                'value' => SurveyQuestionType::RatingScale->value,
                'label' => 'Rating scale',
                'description' => 'Quantify sentiment on a numeric scale.',
            ],
            [
                'value' => SurveyQuestionType::Ranking->value,
                'label' => 'Ranking',
                'description' => 'Let respondents order options by preference.',
            ],
        ]);
    }
}
