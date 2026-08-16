<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Evaluation extends Model
{
    protected $table = 'evaluations';
    protected $primaryKey = 'evaluation_id';

    public const STATUS_DRAFT     = 'Draft';
    public const STATUS_SUBMITTED = 'Submitted';
    public const STATUS_APPROVED  = 'Approved';
    public const STATUS_REJECTED  = 'Rejected';

    protected $fillable = [
        'student_id', 'employer_id', 'application_id', 'period',
        'strengths', 'improvements', 'overall_comment',
        'total_score', 'status', 'submitted_at',
        'reviewed_by', 'reviewed_at', 'review_remark',
    ];

    protected function casts(): array
    {
        return [
            'total_score'  => 'decimal:2',
            'submitted_at' => 'datetime',
            'reviewed_at'  => 'datetime',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id', 'student_id');
    }

    public function employer(): BelongsTo
    {
        return $this->belongsTo(Employer::class, 'employer_id', 'employer_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by', 'user_id');
    }

    public function scores(): HasMany
    {
        return $this->hasMany(EvaluationScore::class, 'evaluation_id', 'evaluation_id');
    }

    /** Only Draft and Rejected evaluations can still be edited by the employer. */
    public function isEditable(): bool
    {
        return in_array($this->status, [self::STATUS_DRAFT, self::STATUS_REJECTED], true);
    }

    /** Students only ever see evaluations an Admin has approved. */
    public function scopeVisibleToStudent($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    /**
     * Weighted percentage out of 100:
     *   sum( (score / max_score) * weight )  over every scored criterion.
     * Stored on the row so dashboard queries stay cheap.
     */
    public function recalculateTotalScore(): float
    {
        $total = 0.0;

        foreach ($this->scores()->with('criterion')->get() as $score) {
            $criterion = $score->criterion;
            if (! $criterion || $criterion->max_score <= 0) {
                continue;
            }
            $total += ($score->score / $criterion->max_score) * (float) $criterion->weight;
        }

        $total = round($total, 2);
        $this->forceFill(['total_score' => $total])->save();

        return $total;
    }
}