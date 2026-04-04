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
            'access_code' => $survey->access_code_ciphertext,
            'status' => $survey->status->value,
            'published_at' => $survey->published_at?->toIso8601String(),
            'closed_at' => $survey->closed_at?->toIso8601String(),
            'share_path' => $survey->isPublished()
                ? route('surveys.public.access.show', $survey->public_id, false)
                : null,
            'categories' => $survey->categories
                ->sortBy('position')
                ->values()
                ->map(fn($category): array => [
                    'id' => $category->id,
                    'name' => $category->name,
                    'description' => $category->description,
                    'position' => $category->position,
                    'client_key' => sprintf('category-%d', $category->id),
                ])
                ->all(),
            'questions' => $survey->questions
                ->sortBy('position')
                ->values()
                ->map(fn($question): array => [
                    'id' => $question->id,
                    'type' => $question->type->value,
                    'title' => $question->title,
                    'description' => $question->description,
                    'is_required' => $question->is_required,
                    'position' => $question->position,
                    'category_client_key' => $question->category
                        ? sprintf('category-%d', $question->category->id)
                        : null,
                    'category' => $question->category
                        ? [
                            'id' => $question->category->id,
                            'name' => $question->category->name,
                            'position' => $question->category->position,
                        ]
                        : null,
                    'settings' => $question->settings ?? [],
                    'options' => $question->options
                        ->sortBy('position')
                        ->values()
                        ->map(fn($option): array => [
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
