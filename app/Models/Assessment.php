<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Assessment extends Model
{
    protected $fillable = ['subject_id', 'period_id', 'section_id', 'title', 'weight_percentage', 'due_date'];

    protected function casts(): array
    {
        return [
            'weight_percentage' => 'decimal:2',
            'due_date' => 'date',
        ];
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function period(): BelongsTo
    {
        return $this->belongsTo(Period::class);
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    public function scores(): HasMany
    {
        return $this->hasMany(StudentScore::class);
    }
}