<?php

namespace App\Http\Resources;

use App\Models\Survey;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SurveySummaryResource extends JsonResource
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
            'share_path' => route('surveys.public.access.show', $survey->public_id, false),
            'question_count' => $survey->questions_count ?? $survey->questions()->count(),
            'response_count' => $survey->responses_count ?? $survey->responses()->count(),
            'completed_count' => $survey->completed_responses_count ?? $survey->responses()->where('is_completed', true)->count(),
            'published_at' => $survey->published_at?->toIso8601String(),
            'closed_at' => $survey->closed_at?->toIso8601String(),
            'updated_at' => $survey->updated_at?->toIso8601String(),
        ];
    }
}
