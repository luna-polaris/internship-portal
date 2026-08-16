<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EvaluationCriterion extends Model
{
    protected $table = 'evaluation_criteria';
    protected $primaryKey = 'criterion_id';

    protected $fillable = [
        'name', 'description', 'category', 'max_score', 'weight', 'is_active', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'max_score' => 'integer',
            'weight'    => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function scores(): HasMany
    {
        return $this->hasMany(EvaluationScore::class, 'criterion_id', 'criterion_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'user_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /** Total weight of all active criteria — should be 100 for a valid rubric. */
    public static function activeWeightTotal(): float
    {
        return (float) static::active()->sum('weight');
    }
}