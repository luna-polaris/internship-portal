<?php

namespace App\Models;

use App\Support\Filters\InternshipFilterRegistry;
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

    /** Published postings only, excluding ones past their deadline (still technically open, but shouldn't clutter search). */
    public function scopeVisible($query)
    {
        return $query->where('status', 'Published')
            ->where(function ($q) {
                $q->whereNull('application_deadline')->orWhere('application_deadline', '>=', now()->toDateString());
            });
    }

    /** Context: picks whichever filter strategies are relevant to the given input and applies them. */
    public function scopeFilter($query, array $filters)
    {
        foreach (InternshipFilterRegistry::all() as $strategy) {
            if ($strategy->supports($filters)) {
                $strategy->apply($query, $filters);
            }
        }

        return $query;
    }
}
