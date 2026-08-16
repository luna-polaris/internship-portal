<?php

namespace App\Models;

use App\Support\ListSanitizer;
use Illuminate\Database\Eloquent\Model;

class Internship extends Model
{
    protected $primaryKey = 'internship_id';

    protected $fillable = [
        'title',
        'description',
        'requirements',
        'skills_required',
        'min_cgpa',
        'category',
        'work_mode',
        'city',
        'state',
        'allowance',
        'duration_months',
        'vacancies',
        'application_deadline',
        'status',
        'published_at',
    ];

    protected $casts = [
        'application_deadline' => 'date',
        'allowance' => 'decimal:2',
        'published_at' => 'datetime',
        'skills_required' => 'array',
        'min_cgpa' => 'decimal:2',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id', 'company_id');
    }

    public function applications()
    {
        return $this->hasMany(Application::class, 'internship_id', 'internship_id');
    }

    public function bookmarks()
    {
        return $this->hasMany(Bookmark::class, 'internship_id', 'internship_id');
    }

    public function recommendations()
    {
        return $this->hasMany(Recommendation::class, 'internship_id', 'internship_id');
    }

    public function setSkillsRequiredAttribute($value): void
    {
        $this->attributes['skills_required'] = ListSanitizer::toJson($value);
    }

    /**
     * What the public should ever see: live postings that haven't passed
     * their own deadline yet (a Published-but-expired posting is still
     * technically open until the employer closes it, but shouldn't
     * clutter search results).
     */
    public function scopeVisible($query)
    {
        return $query->where('status', 'Published')
            ->where(function ($q) {
                $q->whereNull('application_deadline')->orWhere('application_deadline', '>=', now()->toDateString());
            });
    }

    public function scopeFilter($query, array $filters)
    {
        return $query
            ->when($filters['q'] ?? null, fn ($q, $v) => $q->where(function ($q) use ($v) {
                $q->where('title', 'like', "%{$v}%")->orWhere('description', 'like', "%{$v}%");
            }))
            ->when($filters['category'] ?? null, fn ($q, $v) => $q->where('category', 'like', "%{$v}%"))
            ->when($filters['city'] ?? null, fn ($q, $v) => $q->where('city', 'like', "%{$v}%"))
            ->when($filters['state'] ?? null, fn ($q, $v) => $q->where('state', 'like', "%{$v}%"))
            ->when($filters['work_mode'] ?? null, fn ($q, $v) => $q->where('work_mode', $v))
            ->when($filters['min_allowance'] ?? null, fn ($q, $v) => $q->where('allowance', '>=', $v))
            ->when($filters['max_allowance'] ?? null, fn ($q, $v) => $q->where('allowance', '<=', $v));
    }
}
