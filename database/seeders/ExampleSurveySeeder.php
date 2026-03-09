<?php

namespace Database\Seeders;

use App\Enums\SurveyQuestionType;
use App\Enums\SurveyStatus;
use App\Models\Survey;
use App\Models\SurveyQuestion;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ExampleSurveySeeder extends Seeder
{
    /**
     * @var list<string>
     */
    protected array $exampleTitles = [
        'Philippines Presidential Pulse 2028',
        'Philippine Food Research Study 2026',
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $owner = User::query()->firstOrCreate(
            ['email' => 'admin@surveyor.com'],
            [
                'name' => 'admin',
                'password' => 'password',
            ],
        );

        $owner->surveys()->whereIn('title', $this->exampleTitles)->delete();

        $this->seedPresidentialSurvey($owner);
        $this->seedFoodResearchSurvey($owner);
    }

    protected function seedPresidentialSurvey(User $owner): void
    {
        $survey = $owner->surveys()->create([
            'title' => 'Philippines Presidential Pulse 2028',
            'description' => 'A fictional nationwide pulse survey exploring voter priorities, regional concerns, and candidate preference ahead of a mock presidential race.',
            'access_code_hash' => Hash::make('halalan-2028'),
            'status' => SurveyStatus::Published,
            'published_at' => now()->subDays(10),
            'last_response_at' => now()->subHours(4),
        ]);

        $questions = $this->createQuestions($survey, [
            [
                'key' => 'candidate',
                'type' => SurveyQuestionType::MultipleChoice,
                'title' => 'Which fictional candidate are you most likely to vote for?',
                'description' => 'Pick the mock candidate you currently lean toward.',
                'is_required' => true,
                'position' => 0,
                'settings' => ['allow_multiple' => false],
                'options' => [
                    'Alicia Mercado',
                    'Ramon de la Cruz',
                    'Teresa Valdez',
                    'Undecided',
                ],
            ],
            [
                'key' => 'priority',
                'type' => SurveyQuestionType::Ranking,
                'title' => 'Rank the national issues you want the next president to prioritize.',
                'description' => 'Place the most urgent issue first.',
                'is_required' => true,
                'position' => 1,
                'settings' => [],
                'options' => [
                    'Jobs and wages',
                    'Food prices',
                    'Anti-corruption',
                    'Public healthcare',
                    'West Philippine Sea policy',
                ],
            ],
            [
                'key' => 'approval',
                'type' => SurveyQuestionType::YesNo,
                'title' => 'Do you approve of the current national direction?',
                'description' => null,
                'is_required' => true,
                'position' => 2,
                'settings' => [],
                'options' => [],
            ],
            [
                'key' => 'location',
                'type' => SurveyQuestionType::MultipleChoice,
                'title' => 'Which part of the Philippines do you live in?',
                'description' => 'This is used only for grouped sample reporting.',
                'is_required' => false,
                'position' => 3,
                'settings' => [
                    'allow_multiple' => false,
                    'demographic_key' => 'location',
                ],
                'options' => [
                    'National Capital Region',
                    'North Luzon',
                    'South Luzon',
                    'Visayas',
                    'Mindanao',
                ],
            ],
            [
                'key' => 'age_range',
                'type' => SurveyQuestionType::MultipleChoice,
                'title' => 'What is your age range?',
                'description' => null,
                'is_required' => false,
                'position' => 4,
                'settings' => [
                    'allow_multiple' => false,
                    'demographic_key' => 'age_range',
                ],
                'options' => [
                    '18-24',
                    '25-34',
                    '35-44',
                    '45-54',
                    '55 and above',
                ],
            ],
            [
                'key' => 'comment',
                'type' => SurveyQuestionType::OpenEnded,
                'title' => 'What is one concrete change you want from the next president?',
                'description' => 'Short answers are fine.',
                'is_required' => false,
                'position' => 5,
                'settings' => [],
                'options' => [],
            ],
        ]);

        foreach ($this->presidentialResponses() as $index => $responseData) {
            $this->createResponse($survey, $questions, $responseData, $index);
        }
    }

    protected function seedFoodResearchSurvey(User $owner): void
    {
        $survey = $owner->surveys()->create([
            'title' => 'Philippine Food Research Study 2026',
            'description' => 'A sample research survey about Filipino dishes, ingredient access, and priorities for documenting regional food traditions.',
            'access_code_hash' => Hash::make('pagkain-2026'),
            'status' => SurveyStatus::Published,
            'published_at' => now()->subDays(6),
            'last_response_at' => now()->subHours(2),
        ]);

        $questions = $this->createQuestions($survey, [
            [
                'key' => 'dish',
                'type' => SurveyQuestionType::MultipleChoice,
                'title' => 'Which Filipino dish best represents home cooking for you?',
                'description' => 'Choose the dish that feels most familiar at home.',
                'is_required' => true,
                'position' => 0,
                'settings' => ['allow_multiple' => false],
                'options' => [
                    'Adobo',
                    'Sinigang',
                    'Tinola',
                    'Kare-kare',
                    'Laing',
                ],
            ],
            [
                'key' => 'frequency',
                'type' => SurveyQuestionType::RatingScale,
                'title' => 'How often do you eat regional Filipino dishes in a typical month?',
                'description' => '1 means rarely and 5 means very often.',
                'is_required' => true,
                'position' => 1,
                'settings' => [
                    'min' => 1,
                    'max' => 5,
                    'min_label' => 'Rarely',
                    'max_label' => 'Very often',
                ],
                'options' => [],
            ],
            [
                'key' => 'ingredient',
                'type' => SurveyQuestionType::OpenEnded,
                'title' => 'Which ingredient is hardest to find or afford when cooking Filipino food?',
                'description' => null,
                'is_required' => false,
                'position' => 2,
                'settings' => [],
                'options' => [],
            ],
            [
                'key' => 'research_focus',
                'type' => SurveyQuestionType::Ranking,
                'title' => 'Rank the food research priorities you care about most.',
                'description' => 'Put the highest-priority topic first.',
                'is_required' => true,
                'position' => 3,
                'settings' => [],
                'options' => [
                    'Nutrition and health',
                    'Ingredient affordability',
                    'Regional preservation',
                    'Food tourism potential',
                    'Sustainable sourcing',
                ],
            ],
            [
                'key' => 'location',
                'type' => SurveyQuestionType::MultipleChoice,
                'title' => 'Where in the Philippines do you currently live?',
                'description' => null,
                'is_required' => false,
                'position' => 4,
                'settings' => [
                    'allow_multiple' => false,
                    'demographic_key' => 'location',
                ],
                'options' => [
                    'National Capital Region',
                    'Luzon outside NCR',
                    'Visayas',
                    'Mindanao',
                ],
            ],
            [
                'key' => 'age_range',
                'type' => SurveyQuestionType::MultipleChoice,
                'title' => 'What is your age range?',
                'description' => null,
                'is_required' => false,
                'position' => 5,
                'settings' => [
                    'allow_multiple' => false,
                    'demographic_key' => 'age_range',
                ],
                'options' => [
                    '18-24',
                    '25-34',
                    '35-44',
                    '45-54',
                    '55 and above',
                ],
            ],
        ]);

        foreach ($this->foodResearchResponses() as $index => $responseData) {
            $this->createResponse($survey, $questions, $responseData, $index);
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $definitions
     * @return array<string, SurveyQuestion>
     */
    protected function createQuestions(Survey $survey, array $definitions): array
    {
        $questions = [];

        foreach ($definitions as $definition) {
            $question = $survey->questions()->create([
                'type' => $definition['type'],
                'title' => $definition['title'],
                'description' => $definition['description'],
                'is_required' => $definition['is_required'],
                'position' => $definition['position'],
                'settings' => $definition['settings'],
            ]);

            foreach ($definition['options'] as $position => $label) {
                $question->options()->create([
                    'label' => $label,
                    'position' => $position,
                ]);
            }

            $questions[$definition['key']] = $question->fresh('options');
        }

        return $questions;
    }

    /**
     * @param  array<string, SurveyQuestion>  $questions
     * @param  array<string, mixed>  $responseData
     */
    protected function createResponse(Survey $survey, array $questions, array $responseData, int $index): void
    {
        $response = $survey->responses()->create([
            'is_completed' => true,
            'access_code_verified_at' => now()->subHours(24 - $index),
            'submitted_at' => now()->subHours(24 - $index),
            'metadata' => [
                'seeded' => true,
                'profile' => $responseData['profile'],
            ],
        ]);

        foreach ($questions as $key => $question) {
            $value = $responseData[$key] ?? null;

            if ($value === null || $value === '' || $value === []) {
                continue;
            }

            $payload = [
                'survey_question_id' => $question->id,
                'text_value' => null,
                'numeric_value' => null,
                'boolean_value' => null,
                'json_value' => null,
            ];

            switch ($question->type) {
                case SurveyQuestionType::OpenEnded:
                    $payload['text_value'] = (string) $value;
                    break;
                case SurveyQuestionType::YesNo:
                    $payload['boolean_value'] = (bool) $value;
                    break;
                case SurveyQuestionType::MultipleChoice:
                    $selectedLabels = is_array($value) ? $value : [$value];
                    $payload['json_value'] = [
                        'option_ids' => $question->options
                            ->whereIn('label', $selectedLabels)
                            ->pluck('id')
                            ->map(fn($id): int => (int) $id)
                            ->values()
                            ->all(),
                    ];
                    break;
                case SurveyQuestionType::RatingScale:
                    $payload['numeric_value'] = (int) $value;
                    break;
                case SurveyQuestionType::Ranking:
                    $payload['json_value'] = [
                        'ranked_option_ids' => collect($value)
                            ->map(fn(string $label): int => (int) $question->options->firstWhere('label', $label)?->id)
                            ->filter()
                            ->values()
                            ->all(),
                    ];
                    break;
            }

            $response->answers()->create($payload);
        }

        $survey->forceFill(['last_response_at' => $response->submitted_at])->save();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function presidentialResponses(): array
    {
        return [
            [
                'profile' => 'Quezon City commuter, 26',
                'candidate' => 'Alicia Mercado',
                'priority' => ['Food prices', 'Jobs and wages', 'Public healthcare', 'Anti-corruption', 'West Philippine Sea policy'],
                'approval' => false,
                'location' => 'National Capital Region',
                'age_range' => '25-34',
                'comment' => 'Lower rice prices and more stable commute policies.',
            ],
            [
                'profile' => 'Cebu small business owner, 41',
                'candidate' => 'Ramon de la Cruz',
                'priority' => ['Jobs and wages', 'Food prices', 'Anti-corruption', 'Public healthcare', 'West Philippine Sea policy'],
                'approval' => true,
                'location' => 'Visayas',
                'age_range' => '35-44',
                'comment' => 'Support small enterprises outside Metro Manila.',
            ],
            [
                'profile' => 'Davao teacher, 33',
                'candidate' => 'Teresa Valdez',
                'priority' => ['Public healthcare', 'Jobs and wages', 'Food prices', 'Anti-corruption', 'West Philippine Sea policy'],
                'approval' => true,
                'location' => 'Mindanao',
                'age_range' => '25-34',
                'comment' => 'Public schools and rural clinics need more support.',
            ],
            [
                'profile' => 'Baguio first-time voter, 21',
                'candidate' => 'Undecided',
                'priority' => ['Jobs and wages', 'Anti-corruption', 'Food prices', 'Public healthcare', 'West Philippine Sea policy'],
                'approval' => false,
                'location' => 'North Luzon',
                'age_range' => '18-24',
                'comment' => 'I want a clearer plan for youth employment.',
            ],
            [
                'profile' => 'Laguna factory supervisor, 38',
                'candidate' => 'Alicia Mercado',
                'priority' => ['Food prices', 'Jobs and wages', 'Anti-corruption', 'Public healthcare', 'West Philippine Sea policy'],
                'approval' => false,
                'location' => 'South Luzon',
                'age_range' => '35-44',
                'comment' => 'Everyday grocery prices matter more than slogans.',
            ],
            [
                'profile' => 'Iloilo nurse, 29',
                'candidate' => 'Teresa Valdez',
                'priority' => ['Public healthcare', 'Food prices', 'Jobs and wages', 'Anti-corruption', 'West Philippine Sea policy'],
                'approval' => true,
                'location' => 'Visayas',
                'age_range' => '25-34',
                'comment' => 'Hospitals outside the capital need better staffing.',
            ],
            [
                'profile' => 'Cagayan farmer, 54',
                'candidate' => 'Ramon de la Cruz',
                'priority' => ['Food prices', 'Jobs and wages', 'West Philippine Sea policy', 'Anti-corruption', 'Public healthcare'],
                'approval' => true,
                'location' => 'North Luzon',
                'age_range' => '45-54',
                'comment' => 'Farm inputs should be more affordable and predictable.',
            ],
            [
                'profile' => 'General Santos fisherfolk family member, 47',
                'candidate' => 'Alicia Mercado',
                'priority' => ['West Philippine Sea policy', 'Food prices', 'Jobs and wages', 'Anti-corruption', 'Public healthcare'],
                'approval' => false,
                'location' => 'Mindanao',
                'age_range' => '45-54',
                'comment' => 'Protect fishing livelihoods and keep fuel affordable.',
            ],
            [
                'profile' => 'Pasig BPO worker, 31',
                'candidate' => 'Alicia Mercado',
                'priority' => ['Jobs and wages', 'Food prices', 'Anti-corruption', 'Public healthcare', 'West Philippine Sea policy'],
                'approval' => false,
                'location' => 'National Capital Region',
                'age_range' => '25-34',
                'comment' => 'Wages should catch up with rent and transport costs.',
            ],
            [
                'profile' => 'Batangas jeepney operator, 52',
                'candidate' => 'Ramon de la Cruz',
                'priority' => ['Food prices', 'Jobs and wages', 'Public healthcare', 'Anti-corruption', 'West Philippine Sea policy'],
                'approval' => true,
                'location' => 'South Luzon',
                'age_range' => '45-54',
                'comment' => 'Transport workers need a realistic modernization plan.',
            ],
            [
                'profile' => 'Tacloban university staff, 36',
                'candidate' => 'Teresa Valdez',
                'priority' => ['Anti-corruption', 'Public healthcare', 'Jobs and wages', 'Food prices', 'West Philippine Sea policy'],
                'approval' => false,
                'location' => 'Visayas',
                'age_range' => '35-44',
                'comment' => 'Less corruption would make every other program work better.',
            ],
            [
                'profile' => 'Marawi community organizer, 58',
                'candidate' => 'Undecided',
                'priority' => ['Public healthcare', 'Jobs and wages', 'Food prices', 'West Philippine Sea policy', 'Anti-corruption'],
                'approval' => true,
                'location' => 'Mindanao',
                'age_range' => '55 and above',
                'comment' => 'Communities outside major cities need consistent support.',
            ],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function foodResearchResponses(): array
    {
        return [
            [
                'profile' => 'Manila home cook, 24',
                'dish' => 'Sinigang',
                'frequency' => 4,
                'ingredient' => 'Tamarind when fresh fruit prices rise.',
                'research_focus' => ['Ingredient affordability', 'Nutrition and health', 'Regional preservation', 'Sustainable sourcing', 'Food tourism potential'],
                'location' => 'National Capital Region',
                'age_range' => '18-24',
            ],
            [
                'profile' => 'Pampanga caterer, 39',
                'dish' => 'Kare-kare',
                'frequency' => 5,
                'ingredient' => 'Peanut and annatto quality varies too much.',
                'research_focus' => ['Regional preservation', 'Ingredient affordability', 'Food tourism potential', 'Nutrition and health', 'Sustainable sourcing'],
                'location' => 'Luzon outside NCR',
                'age_range' => '35-44',
            ],
            [
                'profile' => 'Cebu office worker, 28',
                'dish' => 'Adobo',
                'frequency' => 3,
                'ingredient' => 'Coconut vinegar costs more than before.',
                'research_focus' => ['Ingredient affordability', 'Sustainable sourcing', 'Nutrition and health', 'Regional preservation', 'Food tourism potential'],
                'location' => 'Visayas',
                'age_range' => '25-34',
            ],
            [
                'profile' => 'Davao market vendor, 44',
                'dish' => 'Tinola',
                'frequency' => 4,
                'ingredient' => 'Native chicken is harder to source consistently.',
                'research_focus' => ['Ingredient affordability', 'Regional preservation', 'Sustainable sourcing', 'Nutrition and health', 'Food tourism potential'],
                'location' => 'Mindanao',
                'age_range' => '35-44',
            ],
            [
                'profile' => 'Bacolod culinary student, 22',
                'dish' => 'Adobo',
                'frequency' => 4,
                'ingredient' => 'Good local vinegar for class projects.',
                'research_focus' => ['Regional preservation', 'Food tourism potential', 'Nutrition and health', 'Ingredient affordability', 'Sustainable sourcing'],
                'location' => 'Visayas',
                'age_range' => '18-24',
            ],
            [
                'profile' => 'Quezon province parent, 48',
                'dish' => 'Sinigang',
                'frequency' => 5,
                'ingredient' => 'Fresh vegetables during storm season.',
                'research_focus' => ['Ingredient affordability', 'Nutrition and health', 'Sustainable sourcing', 'Regional preservation', 'Food tourism potential'],
                'location' => 'Luzon outside NCR',
                'age_range' => '45-54',
            ],
            [
                'profile' => 'Makati restaurant manager, 34',
                'dish' => 'Kare-kare',
                'frequency' => 3,
                'ingredient' => 'Oxtail is expensive for small restaurant testing.',
                'research_focus' => ['Ingredient affordability', 'Food tourism potential', 'Regional preservation', 'Nutrition and health', 'Sustainable sourcing'],
                'location' => 'National Capital Region',
                'age_range' => '25-34',
            ],
            [
                'profile' => 'Bicol teacher, 40',
                'dish' => 'Laing',
                'frequency' => 4,
                'ingredient' => 'Quality gabi leaves during the rainy months.',
                'research_focus' => ['Regional preservation', 'Ingredient affordability', 'Nutrition and health', 'Sustainable sourcing', 'Food tourism potential'],
                'location' => 'Luzon outside NCR',
                'age_range' => '35-44',
            ],
            [
                'profile' => 'Iligan engineer, 31',
                'dish' => 'Tinola',
                'frequency' => 2,
                'ingredient' => 'Ginger prices spike too often.',
                'research_focus' => ['Ingredient affordability', 'Sustainable sourcing', 'Nutrition and health', 'Food tourism potential', 'Regional preservation'],
                'location' => 'Mindanao',
                'age_range' => '25-34',
            ],
            [
                'profile' => 'Taguig graduate student, 27',
                'dish' => 'Adobo',
                'frequency' => 3,
                'ingredient' => 'Locally produced soy sauce with consistent quality.',
                'research_focus' => ['Nutrition and health', 'Ingredient affordability', 'Regional preservation', 'Sustainable sourcing', 'Food tourism potential'],
                'location' => 'National Capital Region',
                'age_range' => '25-34',
            ],
        ];
    }
}
