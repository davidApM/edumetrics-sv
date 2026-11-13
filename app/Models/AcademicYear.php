<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AcademicYear extends Model
{
    protected $fillable = ['name', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function periods(): HasMany
    {
        return $this->hasMany(Period::class)->orderBy('period_order');
    }

    public function sections(): HasMany
    {
        return $this->hasMany(Section::class);
    }
}