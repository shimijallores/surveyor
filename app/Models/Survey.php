<?php

namespace App\Models;

use App\Enums\SurveyStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Survey extends Model
{
    /** @use HasFactory<\Database\Factories\SurveyFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'public_id',
        'title',
        'description',
        'access_code_hash',
        'access_code_ciphertext',
        'status',
        'published_at',
        'closed_at',
        'last_response_at',
    ];

    protected static function booted(): void
    {
        static::creating(function (Survey $survey): void {
            if (blank($survey->public_id)) {
                $survey->public_id = (string) Str::ulid();
            }
        });
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => SurveyStatus::class,
            'access_code_ciphertext' => 'encrypted',
            'published_at' => 'immutable_datetime',
            'closed_at' => 'immutable_datetime',
            'last_response_at' => 'immutable_datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(SurveyQuestion::class)->orderBy('position');
    }

    public function responses(): HasMany
    {
        return $this->hasMany(SurveyResponse::class);
    }

    public function isPublished(): bool
    {
        return $this->status === SurveyStatus::Published;
    }

    public function isClosed(): bool
    {
        return $this->status === SurveyStatus::Closed;
    }
}
