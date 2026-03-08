<?php

namespace App\Http\Resources;

use App\Models\Survey;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SurveyBuilderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var Survey $survey */
        $survey = $this->resource;

        return [
            'id' => $survey->id,
            'public_id' => $survey->public_id,
            'title' => $survey->title,
            'description' => $survey->description,
            'status' => $survey->status->value,
            'published_at' => $survey->published_at?->toIso8601String(),
            'closed_at' => $survey->closed_at?->toIso8601String(),
            'share_path' => route('surveys.public.access.show', $survey->public_id, false),
            'questions' => $survey->questions
                ->sortBy('position')
                ->values()
                ->map(fn ($question): array => [
                    'id' => $question->id,
                    'type' => $question->type->value,
                    'title' => $question->title,
                    'description' => $question->description,
                    'is_required' => $question->is_required,
                    'position' => $question->position,
                    'settings' => $question->settings ?? [],
                    'options' => $question->options
                        ->sortBy('position')
                        ->values()
                        ->map(fn ($option): array => [
                            'id' => $option->id,
                            'label' => $option->label,
                            'position' => $option->position,
                        ])
                        ->all(),
                ])
                ->all(),
        ];
    }
}
