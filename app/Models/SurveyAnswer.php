<?php

namespace App\Models;

use App\Concerns\HasTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SurveyAnswer extends Model
{
    use HasTenant;

    protected $fillable = [
        'tenant_id', 'survey_response_id', 'survey_question_id',
        'value_text', 'value_number', 'value_options',
    ];

    protected function casts(): array
    {
        return [
            'value_options' => 'array',
            'value_number' => 'integer',
        ];
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(SurveyQuestion::class);
    }

    public function response(): BelongsTo
    {
        return $this->belongsTo(SurveyResponse::class);
    }
}
