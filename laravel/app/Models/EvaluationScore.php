<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EvaluationScore extends Model
{
    protected $table = 'evaluation_scores';
    protected $primaryKey = 'score_id';

    protected $fillable = ['evaluation_id', 'criterion_id', 'score', 'remark'];

    protected function casts(): array
    {
        return ['score' => 'decimal:2'];
    }

    public function evaluation(): BelongsTo
    {
        return $this->belongsTo(Evaluation::class, 'evaluation_id', 'evaluation_id');
    }

    public function criterion(): BelongsTo
    {
        return $this->belongsTo(EvaluationCriterion::class, 'criterion_id', 'criterion_id');
    }
}