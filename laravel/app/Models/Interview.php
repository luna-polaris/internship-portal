<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Interview extends Model
{
    protected $primaryKey = 'interview_id';

    public const STATUS_SCHEDULED = 'Scheduled';
    public const STATUS_RESCHEDULED = 'Rescheduled';
    public const STATUS_COMPLETED = 'Completed';
    public const STATUS_CANCELLED = 'Cancelled';

    protected $fillable = [
        'application_id',
        'scheduled_at',
        'mode',
        'location',
        'meeting_link',
        'status',
        'remarks',
        'cancelled_reason',
        'created_by',
        'requested_scheduled_at',
        'requested_reason',
        'requested_by',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'requested_scheduled_at' => 'datetime',
    ];

    public function application()
    {
        return $this->belongsTo(Application::class, 'application_id', 'application_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'user_id');
    }

    public function requestedBy()
    {
        return $this->belongsTo(User::class, 'requested_by', 'user_id');
    }

    public function hasPendingRescheduleRequest(): bool
    {
        return $this->requested_scheduled_at !== null;
    }

    public function logs()
    {
        return $this->hasMany(InterviewLog::class, 'interview_id', 'interview_id')->latest('created_at');
    }

    public function scopeUpcoming($query)
    {
        return $query->whereIn('status', [self::STATUS_SCHEDULED, self::STATUS_RESCHEDULED])
            ->where('scheduled_at', '>=', now());
    }
}
