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
    protected string $surveyTitle = 'Philippines 2028 Governance Pulse: Reform, Accountability, and Cost of Living';

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

        $owner->surveys()->where('title', $this->surveyTitle)->delete();

        $survey = $owner->surveys()->create([
            'title' => $this->surveyTitle,
            'description' => 'Public opinion snapshot on the 2028 presidency race, flood control fund accountability, and oil price shocks tied to global conflict and domestic planning.',
            'access_code_hash' => Hash::make('survey'),
            'access_code_ciphertext' => 'survey',
            'status' => SurveyStatus::Published,
            'published_at' => now()->subDays(9),
            'last_response_at' => now()->subHours(3),
        ]);

        $questions = $this->createSurveyWithCategories($survey);

        foreach ($this->generateResponses($questions, 52) as $index => $responseData) {
            $this->createResponse($survey, $questions, $responseData, $index);
        }
    }

    /**
     * @return array<string, SurveyQuestion>
     */
    protected function createSurveyWithCategories(Survey $survey): array
    {
        $definitions = [
            [
                'category' => [
                    'name' => 'Presidency 2028 in the Philippines',
                    'description' => 'Leadership readiness, anti-corruption reforms, and demographic profile of respondents.',
                    'position' => 0,
                ],
                'questions' => [
                    [
                        'key' => 'president_profile',
                        'type' => SurveyQuestionType::MultipleChoice,
                        'title' => 'Who are you most likely to support for president in 2028 if these names run?',
                        'description' => 'Choose one based on current public visibility and reform credibility.',
                        'is_required' => true,
                        'position' => 0,
                        'settings' => ['allow_multiple' => false],
                        'options' => [
                            'Risa Hontiveros (Akbayan)',
                            'Sara Duterte (Hugpong ng Pagbabago)',
                            'Imee Marcos (Nacionalista Party)',
                            'Bam Aquino (Liberal Party / reform bloc)',
                            'Undecided',
                        ],
                    ],
                    [
                        'key' => 'trust_reform_slate',
                        'type' => SurveyQuestionType::RatingScale,
                        'title' => 'How confident are you that a reform-focused slate can reduce corruption by 2028?',
                        'description' => '1 means very low confidence, 5 means very high confidence.',
                        'is_required' => true,
                        'position' => 1,
                        'settings' => [
                            'min' => 1,
                            'max' => 5,
                            'min_label' => 'Very low confidence',
                            'max_label' => 'Very high confidence',
                        ],
                        'options' => [],
                    ],
                    [
                        'key' => 'location',
                        'type' => SurveyQuestionType::OpenEnded,
                        'title' => 'Where do you currently live?',
                        'description' => 'Share your city, province, region, or country.',
                        'is_required' => false,
                        'position' => 2,
                        'settings' => [
                            'demographic_key' => 'location',
                        ],
                        'options' => [],
                    ],
                    [
                        'key' => 'age_range',
                        'type' => SurveyQuestionType::MultipleChoice,
                        'title' => 'What is your age range?',
                        'description' => 'Demographic grouping for trend breakdowns.',
                        'is_required' => false,
                        'position' => 3,
                        'settings' => [
                            'allow_multiple' => false,
                            'demographic_key' => 'age_range',
                        ],
                        'options' => [
                            '18-24',
                            '25-34',
                            '35-44',
                            '45-54',
                            '55+',
                            'Prefer not to say',
                        ],
                    ],
                    [
                        'key' => 'presidency_reform_comment',
                        'type' => SurveyQuestionType::OpenEnded,
                        'title' => 'What anti-corruption reform should the next president prioritize first?',
                        'description' => null,
                        'is_required' => true,
                        'position' => 4,
                        'settings' => [],
                        'options' => [],
                    ],
                    [
                        'key' => 'anti_dynasty_support',
                        'type' => SurveyQuestionType::YesNo,
                        'title' => 'Should the next president push for passage of a strict anti-political dynasty law to prevent family members from holding successive offices?',
                        'description' => null,
                        'is_required' => true,
                        'position' => 5,
                        'settings' => [],
                        'options' => [],
                    ],
                    [
                        'key' => 'presidential_transparency_priority',
                        'type' => SurveyQuestionType::MultipleChoice,
                        'title' => 'Which presidential transparency measure is most important to prevent abuse of power during the 2028-2034 term?',
                        'description' => null,
                        'is_required' => true,
                        'position' => 6,
                        'settings' => ['allow_multiple' => false],
                        'options' => [
                            'Full disclosure of all presidential assets, loans, and business interests before taking office',
                            'Public livestream of cabinet meetings and major presidential decisions with quarterly published reports',
                            'Independent inspector general office reporting directly to Congress, not the president',
                            'Mandatory lifestyle checks for all presidential appointees with asset reporting every 6 months',
                        ],
                    ],
                ],
            ],
            [
                'category' => [
                    'name' => 'Flood control funds issue in the Philippines',
                    'description' => 'Public priorities on flood resilience spending, oversight, and transparency.',
                    'position' => 1,
                ],
                'questions' => [
                    [
                        'key' => 'flood_funds_confidence',
                        'type' => SurveyQuestionType::RatingScale,
                        'title' => 'How much do you trust flood-control spending after reports that 15 contractors cornered around P100B?',
                        'description' => '1 means no trust, 5 means full trust.',
                        'is_required' => true,
                        'position' => 5,
                        'settings' => [
                            'min' => 1,
                            'max' => 5,
                            'min_label' => 'No trust',
                            'max_label' => 'Full trust',
                        ],
                        'options' => [],
                    ],
                    [
                        'key' => 'flood_audit_support',
                        'type' => SurveyQuestionType::YesNo,
                        'title' => 'Should all DPWH flood-control projects publish machine-readable quarterly audits (budget, contractor, progress, geo-tag)?',
                        'description' => null,
                        'is_required' => true,
                        'position' => 6,
                        'settings' => [],
                        'options' => [],
                    ],
                    [
                        'key' => 'flood_priority_rank',
                        'type' => SurveyQuestionType::Ranking,
                        'title' => 'Rank which flood-governance issue should be fixed first nationwide.',
                        'description' => 'Rank from most urgent to least urgent.',
                        'is_required' => true,
                        'position' => 7,
                        'settings' => [],
                        'options' => [
                            'Independent audit of projects tagged as ghost or substandard',
                            'Public release of all contractor-level flood-control contracts',
                            'Priority funding for repeatedly flooded LGUs',
                            'Drainage and river desiltation in high-risk districts',
                            'Community-based early warning and evacuation planning',
                        ],
                    ],
                    [
                        'key' => 'flood_reallocation',
                        'type' => SurveyQuestionType::MultipleChoice,
                        'title' => 'If funds similar to the vetoed P16.7B flood-control insertions are recovered, where should they go first?',
                        'description' => null,
                        'is_required' => true,
                        'position' => 8,
                        'settings' => ['allow_multiple' => false],
                        'options' => [
                            'Barangay-level flood mitigation and pumping stations',
                            'School and health facility resilience upgrades',
                            'Rescue boats, early warning systems, and evacuation centers',
                            'Independent anti-corruption monitoring and prosecution support',
                        ],
                    ],
                    [
                        'key' => 'flood_comment',
                        'type' => SurveyQuestionType::OpenEnded,
                        'title' => 'What is one policy change that would improve flood fund accountability?',
                        'description' => null,
                        'is_required' => true,
                        'position' => 9,
                        'settings' => [],
                        'options' => [],
                    ],
                ],
            ],
            [
                'category' => [
                    'name' => 'Uncontrolled oil price hike',
                    'description' => 'Sentiment on fuel costs, economic strain, and policy responses.',
                    'position' => 2,
                ],
                'questions' => [
                    [
                        'key' => 'oil_impact_rating',
                        'type' => SurveyQuestionType::RatingScale,
                        'title' => 'How severe is the impact of current oil-price spikes on your household budget?',
                        'description' => '1 means manageable, 5 means severe.',
                        'is_required' => true,
                        'position' => 10,
                        'settings' => [
                            'min' => 1,
                            'max' => 5,
                            'min_label' => 'Manageable',
                            'max_label' => 'Severe',
                        ],
                        'options' => [],
                    ],
                    [
                        'key' => 'oil_policy_priority',
                        'type' => SurveyQuestionType::MultipleChoice,
                        'title' => 'With oil disruption risks around the Strait of Hormuz, which policy should be prioritized first?',
                        'description' => null,
                        'is_required' => true,
                        'position' => 11,
                        'settings' => ['allow_multiple' => false],
                        'options' => [
                            'Targeted fuel and food subsidies for low-income commuters',
                            'Temporary suspension or reduction of fuel excise taxes',
                            'Accelerated renewables, grid upgrades, and mass transit expansion',
                            'No intervention',
                        ],
                    ],
                    [
                        'key' => 'oil_planning_yes_no',
                        'type' => SurveyQuestionType::YesNo,
                        'title' => 'Do you agree weak long-term energy planning worsened fuel inflation despite available global warning signals?',
                        'description' => null,
                        'is_required' => true,
                        'position' => 12,
                        'settings' => [],
                        'options' => [],
                    ],
                    [
                        'key' => 'oil_reform_rank',
                        'type' => SurveyQuestionType::Ranking,
                        'title' => 'Rank reforms to reduce future pump-price shocks (NCR benchmarks recently showed diesel around P133/L and RON91 around P91/L).',
                        'description' => 'Rank from highest to lowest priority.',
                        'is_required' => true,
                        'position' => 13,
                        'settings' => [],
                        'options' => [
                            'Expand renewable energy capacity',
                            'Modernize public transportation',
                            'Strategic fuel reserve planning',
                            'Transparent fuel pricing oversight',
                            'Anti-cartel enforcement',
                        ],
                    ],
                    [
                        'key' => 'oil_comment',
                        'type' => SurveyQuestionType::OpenEnded,
                        'title' => 'What concrete action should government take in the next 12 months to protect commuters?',
                        'description' => null,
                        'is_required' => true,
                        'position' => 14,
                        'settings' => [],
                        'options' => [],
                    ],
                ],
            ],
        ];

        $questions = [];

        foreach ($definitions as $definition) {
            $category = $survey->categories()->create($definition['category']);

            foreach ($definition['questions'] as $questionDefinition) {
                $question = $survey->questions()->create([
                    'survey_category_id' => $category->id,
                    'type' => $questionDefinition['type'],
                    'title' => $questionDefinition['title'],
                    'description' => $questionDefinition['description'],
                    'is_required' => $questionDefinition['is_required'],
                    'position' => $questionDefinition['position'],
                    'settings' => $questionDefinition['settings'],
                ]);

                foreach ($questionDefinition['options'] as $position => $label) {
                    $question->options()->create([
                        'label' => $label,
                        'position' => $position,
                    ]);
                }

                $questions[$questionDefinition['key']] = $question->fresh('options');
            }
        }

        return $questions;
    }

    /**
     * @param  array<string, SurveyQuestion>  $questions
     * @return array<int, array<string, mixed>>
     */
    protected function generateResponses(array $questions, int $count): array
    {
        $locationPool = array_values(array_merge(
            array_fill(0, 41, 'Batangas'),
            array_fill(0, 9, 'Naga'),
            array_fill(0, 2, 'Quezon'),
        ));

        $youngRespondentsCount = (int) floor($count * 0.8);
        $ageRangePool = array_values(array_merge(
            array_fill(0, $youngRespondentsCount, '18-24'),
            array_fill(0, $count - $youngRespondentsCount, '25-34'),
        ));

        $openEndedBuckets = [
            'presidency_reform_comment' => [
                'Pass a strict FOI law and require real-time disclosure of campaign donors and major contracts.',
                'Create an independent anti-corruption court with strict deadlines for graft and procurement cases.',
                'Enforce anti-dynasty and campaign-finance rules with full digital transparency.',
                'Protect whistleblowers and investigative journalists who report corruption networks.',
            ],
            'flood_comment' => [
                'Publish geotagged monthly progress for all DPWH flood-control projects and contractor variation orders.',
                'Require third-party engineering and COA-style performance audits before any project is marked complete.',
                'Blacklist contractors linked to ghost or repeatedly defective flood-control works.',
                'Create citizen oversight boards in flood-prone provinces to monitor implementation quality.',
            ],
            'oil_comment' => [
                'Scale up mass transit and electric public transport so commuters are less exposed to oil shocks.',
                'Deploy targeted fuel relief for public utility drivers and low-income commuters during spikes.',
                'Build a transparent strategic fuel reserve program with quarterly public reporting.',
                'Accelerate renewable and grid projects to reduce import dependence on volatile oil markets.',
            ],
        ];

        $responses = [];
        $bamSupportersCount = (int) round($count * 0.9);

        for ($index = 0; $index < $count; $index++) {
            $response = [
                'profile' => sprintf('Philippines respondent %d', $index + 1),
                'location' => $locationPool[$index],
                'age_range' => $ageRangePool[$index],
                'president_profile' => $index < $bamSupportersCount
                    ? 'Bam Aquino (Liberal Party / reform bloc)'
                    : 'Risa Hontiveros (Akbayan)',
                'trust_reform_slate' => $index % 5 === 0 ? 4 : 5,
                'flood_funds_confidence' => $index % 6 === 0 ? 2 : 1,
                'flood_audit_support' => true,
                'flood_priority_rank' => [
                    'Independent audit of projects tagged as ghost or substandard',
                    'Public release of all contractor-level flood-control contracts',
                    'Drainage and river desiltation in high-risk districts',
                    'Community-based early warning and evacuation planning',
                    'Priority funding for repeatedly flooded LGUs',
                ],
                'flood_reallocation' => 'Independent anti-corruption monitoring and prosecution support',
                'oil_impact_rating' => $index % 4 === 0 ? 4 : 5,
                'oil_policy_priority' => $index % 3 === 0
                    ? 'Accelerated renewables, grid upgrades, and mass transit expansion'
                    : 'Targeted fuel and food subsidies for low-income commuters',
                'oil_planning_yes_no' => true,
                'oil_reform_rank' => [
                    'Transparent fuel pricing oversight',
                    'Expand renewable energy capacity',
                    'Modernize public transportation',
                    'Strategic fuel reserve planning',
                    'Anti-cartel enforcement',
                ],
                'anti_dynasty_support' => true,
                'presidential_transparency_priority' => $index % 4 === 0
                    ? 'Independent inspector general office reporting directly to Congress, not the president'
                    : 'Full disclosure of all presidential assets, loans, and business interests before taking office',
            ];

            foreach ($openEndedBuckets as $key => $bucket) {
                $response[$key] = $bucket[$index % count($bucket)];
            }

            foreach ($questions as $key => $question) {
                if ($question->type === SurveyQuestionType::MultipleChoice && ! array_key_exists($key, $response)) {
                    $options = $question->options->pluck('label')->values()->all();
                    $response[$key] = $options[$index % count($options)];
                }

                if ($question->type === SurveyQuestionType::Ranking && ! array_key_exists($key, $response)) {
                    $options = $question->options->pluck('label')->values()->all();
                    $shift = $index % count($options);

                    $response[$key] = array_values(array_merge(
                        array_slice($options, $shift),
                        array_slice($options, 0, $shift),
                    ));
                }
            }

            $responses[] = $response;
        }

        return $responses;
    }

    /**
     * @param  array<string, SurveyQuestion>  $questions
     * @param  array<string, mixed>  $responseData
     */
    protected function createResponse(Survey $survey, array $questions, array $responseData, int $index): void
    {
        $response = $survey->responses()->create([
            'is_completed' => true,
            'access_code_verified_at' => now()->subHours(100 - $index),
            'submitted_at' => now()->subHours(100 - $index),
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
}
