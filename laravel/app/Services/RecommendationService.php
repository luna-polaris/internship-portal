<?php

namespace App\Services;

use App\Models\Internship;
use App\Models\Recommendation;
use App\Models\Student;
use Illuminate\Support\Collection;

/**
 * Programme/faculty match and the CGPA floor are hard gates — a posting failing either is never
 * recommended. Skills, interests, and location only rank postings that already passed those gates.
 * Recomputation happens via StudentObserver/InternshipObserver, not on every read.
 */
class RecommendationService
{
    private const PROGRAMME_WEIGHT = 10;
    private const SKILL_WEIGHT = 3;
    private const INTEREST_WEIGHT = 2;
    private const LOCATION_WEIGHT = 2;
    private const ELIGIBILITY_WEIGHT = 1;

    /** Programme aliases and related internship terms used by the hard programme gate. */
    private const PROGRAMME_KEYWORD_GROUPS = [
        'it' => [
            'programmes' => ['it', 'information technology', 'computer science', 'computing', 'software engineering', 'information systems', 'data science', 'cybersecurity'],
            'jobs' => ['it', 'information technology', 'computer', 'computing', 'software', 'developer', 'programming', 'web', 'frontend', 'backend', 'full stack', 'data', 'analytics', 'database', 'sql', 'cloud', 'network', 'cybersecurity', 'security', 'devops', 'systems', 'digital'],
        ],
        'finance' => [
            'programmes' => ['finance', 'accounting', 'economics', 'banking', 'investment', 'actuarial'],
            'jobs' => ['finance', 'financial', 'accounting', 'accountant', 'audit', 'assurance', 'economics', 'banking', 'investment', 'valuation', 'wealth', 'risk', 'compliance', 'tax', 'actuarial'],
        ],
        'biotechnology' => [
            'programmes' => ['biotechnology', 'biology', 'biochemistry', 'bioinformatics', 'life sciences', 'biomedical', 'chemistry', 'bioprocess'],
            'jobs' => ['biotechnology', 'biology', 'biochemistry', 'bioinformatics', 'life sciences', 'biomedical', 'laboratory', 'lab', 'molecular', 'genomics', 'pcr', 'clinical trials', 'biomanufacturing', 'bioprocess', 'chemistry', 'research'],
        ],
        'engineering' => [
            'programmes' => ['engineering', 'mechanical engineering', 'civil engineering', 'electrical engineering', 'electronic engineering', 'chemical engineering', 'structural engineering', 'construction management', 'quantity surveying'],
            'jobs' => ['engineering', 'engineer', 'mechanical', 'civil', 'electrical', 'electronic', 'chemical', 'structural', 'construction', 'quantity surveying', 'design', 'cad', 'autocad', 'solidworks', 'infrastructure', 'site'],
        ],
        'healthcare' => [
            'programmes' => ['healthcare', 'medicine', 'medical', 'nursing', 'pharmacy', 'public health', 'health sciences', 'health information'],
            'jobs' => ['healthcare', 'health', 'medicine', 'medical', 'clinical', 'clinic', 'nursing', 'pharmacy', 'pharmacist', 'patient', 'hospital', 'medical records', 'public health'],
        ],
        'business' => [
            'programmes' => ['business', 'business administration', 'management', 'marketing', 'human resource', 'human resources'],
            'jobs' => ['business', 'administration', 'administrative', 'management', 'marketing', 'operations', 'project management', 'human resource', 'human resources', 'hr', 'customer', 'sales'],
        ],
    ];

    public function refreshForStudent(Student $student): Collection
    {
        $results = Internship::visible()->get()
            ->map(fn (Internship $internship) => $this->store($student, $internship))
            ->filter();

        Recommendation::where('student_id', $student->student_id)
            ->whereNotIn('internship_id', $results->pluck('internship_id'))
            ->delete();

        return $results->values();
    }

    public function refreshForInternship(Internship $internship): Collection
    {
        $results = Student::query()->get()
            ->map(fn (Student $student) => $this->store($student, $internship))
            ->filter()
            ->each->load('student');

        Recommendation::where('internship_id', $internship->internship_id)
            ->whereNotIn('student_id', $results->pluck('student_id'))
            ->delete();

        return $results->values();
    }

    /** Score one pair and upsert (or clear) its recommendation row. Returns the row, or null if not a match. */
    private function store(Student $student, Internship $internship): ?Recommendation
    {
        [$score, $reasons] = $this->score($student, $internship);

        if ($score <= 0) {
            Recommendation::where('student_id', $student->student_id)
                ->where('internship_id', $internship->internship_id)
                ->delete();

            return null;
        }

        return Recommendation::updateOrCreate(
            ['student_id' => $student->student_id, 'internship_id' => $internship->internship_id],
            ['score' => $score, 'matched_reasons' => $reasons]
        );
    }

    /** @return array{0: float, 1: string[]} */
    private function score(Student $student, Internship $internship): array
    {
        // CGPA floor is a hard requirement, not a scoring factor.
        if ($internship->min_cgpa !== null && ($student->cgpa === null || (float) $student->cgpa < (float) $internship->min_cgpa)) {
            return [0.0, []];
        }

        $haystack = mb_strtolower(implode(' ', array_filter([
            $internship->category,
            $internship->title,
            $internship->description,
            $internship->requirements,
            ...($internship->skills_required ?? []),
        ])));

        // Hard gate: unrelated to the student's programme/faculty is never recommended, regardless of other matches.
        if (! $this->matchesProgramme($student, $haystack)) {
            return [0.0, []];
        }

        $score = self::PROGRAMME_WEIGHT;
        $reasons = ["Programme match: {$student->programme}"];

        $studentSkills = $this->normalized($student->skills);
        $requiredSkills = $this->normalized($internship->skills_required);
        foreach ($studentSkills->intersect($requiredSkills) as $skill) {
            $score += self::SKILL_WEIGHT;
            $reasons[] = "Skill match: {$skill}";
        }

        foreach ($this->normalized($student->interests) as $interest) {
            if ($interest !== '' && str_contains($haystack, $interest)) {
                $score += self::INTEREST_WEIGHT;
                $reasons[] = "Interest match: {$interest}";
            }
        }

        $preferredLocations = $this->normalized($student->preferred_locations);
        $internshipLocations = $this->normalized(array_filter([$internship->city, $internship->state]));
        if ($preferredLocations->intersect($internshipLocations)->isNotEmpty()) {
            $score += self::LOCATION_WEIGHT;
            $reasons[] = 'Preferred location match';
        }

        if ($internship->min_cgpa !== null) {
            $score += self::ELIGIBILITY_WEIGHT;
            $reasons[] = 'Meets CGPA requirement';
        }

        return [$score, $reasons];
    }

    private function matchesProgramme(Student $student, string $haystack): bool
    {
        $studentContext = mb_strtolower(trim(implode(' ', array_filter([
            $student->programme,
            $student->faculty,
        ]))));

        foreach (self::PROGRAMME_KEYWORD_GROUPS as $group) {
            if ($this->containsAnyKeyword($studentContext, $group['programmes'])
                && $this->containsAnyKeyword($haystack, $group['jobs'])) {
                return true;
            }
        }

        $keywords = collect(preg_split('/[\s,\/&-]+/', (string) $student->programme, -1, PREG_SPLIT_NO_EMPTY))
            ->merge(preg_split('/[\s,\/&-]+/', (string) $student->faculty, -1, PREG_SPLIT_NO_EMPTY))
            ->map(fn ($word) => mb_strtolower($word))
            ->filter(fn ($word) => mb_strlen($word) >= 4)
            ->unique();

        return $keywords->contains(fn ($word) => $this->containsKeyword($haystack, $word));
    }

    private function containsAnyKeyword(string $text, array $keywords): bool
    {
        return collect($keywords)->contains(
            fn ($keyword) => $this->containsKeyword($text, $keyword)
        );
    }

    /** Complete word/phrase matching prevents short aliases such as IT matching digital. */
    private function containsKeyword(string $text, string $keyword): bool
    {
        return preg_match(
            '/(?<![\p{L}\p{N}])'.preg_quote(mb_strtolower($keyword), '/').'(?![\p{L}\p{N}])/u',
            mb_strtolower($text)
        ) === 1;
    }

    private function normalized(?array $values): Collection
    {
        return collect($values ?? [])
            ->map(fn ($value) => mb_strtolower(trim((string) $value)))
            ->filter(fn ($value) => $value !== '')
            ->values();
    }
}
