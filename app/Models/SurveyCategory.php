<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SurveyCategory extends Model
{
    /** @use HasFactory<\Database\Factories\SurveyCategoryFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'survey_id',
        'name',
        'description',
        'position',
    ];

    public function survey(): BelongsTo
    {
        return $this->belongsTo(Survey::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(SurveyQuestion::class, 'survey_category_id')->orderBy('position');
    }
}
