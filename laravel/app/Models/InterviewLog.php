<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InterviewLog extends Model
{
    public $timestamps = false;
    protected $primaryKey = 'log_id';

    protected $fillable = [
        'interview_id',
        'action',
        'performed_by',
        'details',
    ];

    protected $casts = [
        'details' => 'array',
        'created_at' => 'datetime',
    ];

    public function interview()
    {
        return $this->belongsTo(Interview::class, 'interview_id', 'interview_id');
    }

    public function performedBy()
    {
        return $this->belongsTo(User::class, 'performed_by', 'user_id');
    }
}
