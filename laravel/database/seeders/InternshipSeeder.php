<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Employer;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Demo data: one employer/company per homepage category, each with a
 * couple of Published postings, so /internships isn't empty out of the box.
 */
class InternshipSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'category' => 'Finance',
                'company' => ['company_name' => 'Meridian Capital Partners', 'industry' => 'Finance', 'city' => 'Kuala Lumpur', 'state' => 'Federal Territory', 'description' => 'A boutique investment and advisory firm serving corporate clients across Southeast Asia.'],
                'employer' => ['email' => 'hr@meridiancapital.demo', 'full_name' => 'Meridian Capital HR'],
                'jobs' => [
                    ['title' => 'Finance Intern — Financial Reporting', 'description' => 'Assist the finance team with monthly reporting, reconciliations, and variance analysis.', 'requirements' => 'Pursuing a degree in Finance, Accounting, or related field. Strong Excel skills.', 'work_mode' => 'Onsite', 'allowance' => 900, 'duration_months' => 3, 'vacancies' => 2],
                    ['title' => 'Audit & Assurance Intern', 'description' => 'Support the audit team on client engagements, including fieldwork and documentation.', 'requirements' => 'Accounting major, detail-oriented, available for a minimum 3-month placement.', 'work_mode' => 'Onsite', 'allowance' => 850, 'duration_months' => 4, 'vacancies' => 3],
                ],
            ],
            [
                'category' => 'Biotechnology',
                'company' => ['company_name' => 'Helix BioResearch Sdn Bhd', 'industry' => 'Biotechnology', 'city' => 'Petaling Jaya', 'state' => 'Selangor', 'description' => 'A biotech research lab focused on molecular diagnostics and applied genomics.'],
                'employer' => ['email' => 'careers@helixbio.demo', 'full_name' => 'Helix BioResearch HR'],
                'jobs' => [
                    ['title' => 'Lab Research Intern — Molecular Biology', 'description' => 'Work alongside our research scientists on PCR-based diagnostic assay development.', 'requirements' => 'Biology, Biotechnology, or Biochemistry background. Lab safety training a plus.', 'work_mode' => 'Onsite', 'allowance' => 700, 'duration_months' => 6, 'vacancies' => 2],
                    ['title' => 'Bioinformatics Intern', 'description' => 'Analyze sequencing data and help maintain internal data pipelines.', 'requirements' => 'Comfortable with Python or R, interest in computational biology.', 'work_mode' => 'Hybrid', 'allowance' => 800, 'duration_months' => 4, 'vacancies' => 1],
                ],
            ],
            [
                'category' => 'IT',
                'company' => ['company_name' => 'Nimbus Software Solutions', 'industry' => 'Information Technology', 'city' => 'Cyberjaya', 'state' => 'Selangor', 'description' => 'A software house building web and cloud products for regional clients.'],
                'employer' => ['email' => 'talent@nimbussoftware.demo', 'full_name' => 'Nimbus Software HR'],
                'jobs' => [
                    ['title' => 'Software Engineering Intern', 'description' => 'Build and ship features on our internal tooling using Laravel and Vue.', 'requirements' => 'Comfortable with at least one backend language, familiar with Git.', 'work_mode' => 'Remote', 'allowance' => 1000, 'duration_months' => 4, 'vacancies' => 3],
                    ['title' => 'Data Analytics Intern', 'description' => 'Build dashboards and reports for internal stakeholders using SQL and BI tools.', 'requirements' => 'SQL proficiency, coursework in data/statistics preferred.', 'work_mode' => 'Hybrid', 'allowance' => 950, 'duration_months' => 3, 'vacancies' => 2],
                ],
            ],
            [
                'category' => 'Engineering',
                'company' => ['company_name' => 'Ironclad Engineering Sdn Bhd', 'industry' => 'Engineering', 'city' => 'Shah Alam', 'state' => 'Selangor', 'description' => 'An engineering consultancy delivering mechanical and civil projects for industrial clients.'],
                'employer' => ['email' => 'jobs@ironcladeng.demo', 'full_name' => 'Ironclad Engineering HR'],
                'jobs' => [
                    ['title' => 'Mechanical Design Intern', 'description' => 'Assist engineers with CAD drafting and design validation for client projects.', 'requirements' => 'Mechanical Engineering student, familiar with SolidWorks or AutoCAD.', 'work_mode' => 'Onsite', 'allowance' => 800, 'duration_months' => 5, 'vacancies' => 2],
                    ['title' => 'Civil Engineering Site Intern', 'description' => 'Support site supervisors with progress tracking and quality inspections.', 'requirements' => 'Civil Engineering student, willing to work on-site.', 'work_mode' => 'Onsite', 'allowance' => 750, 'duration_months' => 6, 'vacancies' => 2],
                ],
            ],
            [
                'category' => 'Healthcare',
                'company' => ['company_name' => 'Wellspring Health Group', 'industry' => 'Healthcare', 'city' => 'Subang Jaya', 'state' => 'Selangor', 'description' => 'A private healthcare group operating clinics and outpatient centres.'],
                'employer' => ['email' => 'hr@wellspringhealth.demo', 'full_name' => 'Wellspring Health HR'],
                'jobs' => [
                    ['title' => 'Clinical Operations Intern', 'description' => 'Support day-to-day clinic operations, patient scheduling, and records handling.', 'requirements' => 'Healthcare, Nursing, or Public Health student. Good communication skills.', 'work_mode' => 'Onsite', 'allowance' => 700, 'duration_months' => 3, 'vacancies' => 2],
                    ['title' => 'Health Administration Intern', 'description' => 'Assist the administration team with reporting and compliance documentation.', 'requirements' => 'Healthcare Management or Business student, organized and detail-oriented.', 'work_mode' => 'Onsite', 'allowance' => 650, 'duration_months' => 3, 'vacancies' => 1],
                ],
            ],

            // A second company per category, spread across more states so the
            // location filters have something meaningful to work with.
            [
                'category' => 'Finance',
                'company' => ['company_name' => 'Aurelia Wealth Advisors', 'industry' => 'Finance', 'city' => 'Kuala Lumpur', 'state' => 'Federal Territory', 'description' => 'A wealth management firm advising high-net-worth individuals and family offices.'],
                'employer' => ['email' => 'careers@aureliawealth.demo', 'full_name' => 'Aurelia Wealth HR'],
                'jobs' => [
                    ['title' => 'Investment Research Intern', 'description' => 'Research listed equities and prepare summary notes for the advisory team.', 'requirements' => 'Finance or Economics student. Strong written English and Excel modelling.', 'work_mode' => 'Hybrid', 'allowance' => 1000, 'duration_months' => 4, 'vacancies' => 2],
                    ['title' => 'Corporate Finance Intern', 'description' => 'Support deal teams with valuation workings and pitch material preparation.', 'requirements' => 'Coursework in corporate finance or valuation. Comfortable with PowerPoint.', 'work_mode' => 'Onsite', 'allowance' => 1100, 'duration_months' => 6, 'vacancies' => 1],
                    ['title' => 'Risk & Compliance Intern', 'description' => 'Help maintain compliance registers and assist with internal control reviews.', 'requirements' => 'Finance, Law, or Business student. Detail-oriented and discreet.', 'work_mode' => 'Onsite', 'allowance' => 900, 'duration_months' => 3, 'vacancies' => 2],
                ],
            ],
            [
                'category' => 'Biotechnology',
                'company' => ['company_name' => 'Verdant Life Sciences', 'industry' => 'Biotechnology', 'city' => 'Bangi', 'state' => 'Selangor', 'description' => 'A life sciences company developing plant-derived compounds for nutraceutical use.'],
                'employer' => ['email' => 'hr@verdantlife.demo', 'full_name' => 'Verdant Life Sciences HR'],
                'jobs' => [
                    ['title' => 'Quality Control Lab Intern', 'description' => 'Run routine QC assays on incoming raw materials and finished batches.', 'requirements' => 'Chemistry or Biotechnology student. Careful lab technique essential.', 'work_mode' => 'Onsite', 'allowance' => 750, 'duration_months' => 5, 'vacancies' => 2],
                    ['title' => 'Clinical Trials Support Intern', 'description' => 'Assist with trial documentation, data entry, and participant scheduling.', 'requirements' => 'Life Sciences or Pharmacy student. Meticulous record keeping.', 'work_mode' => 'Hybrid', 'allowance' => 850, 'duration_months' => 4, 'vacancies' => 1],
                    ['title' => 'Biomanufacturing Intern', 'description' => 'Shadow the production team on fermentation and downstream processing runs.', 'requirements' => 'Bioprocess or Chemical Engineering background preferred.', 'work_mode' => 'Onsite', 'allowance' => 800, 'duration_months' => 6, 'vacancies' => 2],
                ],
            ],
            [
                'category' => 'IT',
                'company' => ['company_name' => 'Quantum Byte Technologies', 'industry' => 'Information Technology', 'city' => 'George Town', 'state' => 'Penang', 'description' => 'A product engineering firm building fintech and logistics platforms.'],
                'employer' => ['email' => 'talent@quantumbyte.demo', 'full_name' => 'Quantum Byte HR'],
                'jobs' => [
                    ['title' => 'Frontend Developer Intern', 'description' => 'Build responsive interfaces and design-system components for client products.', 'requirements' => 'Solid HTML/CSS/JavaScript. React or Vue experience is a plus.', 'work_mode' => 'Remote', 'allowance' => 1000, 'duration_months' => 4, 'vacancies' => 3],
                    ['title' => 'Cybersecurity Analyst Intern', 'description' => 'Assist with vulnerability scanning, log review, and security documentation.', 'requirements' => 'Interest in security, networking fundamentals, Linux familiarity.', 'work_mode' => 'Hybrid', 'allowance' => 1050, 'duration_months' => 6, 'vacancies' => 1],
                    ['title' => 'Cloud Infrastructure Intern', 'description' => 'Help maintain CI/CD pipelines and containerised deployments.', 'requirements' => 'Exposure to Docker or any cloud provider. Scripting ability.', 'work_mode' => 'Remote', 'allowance' => 1100, 'duration_months' => 5, 'vacancies' => 2],
                ],
            ],
            [
                'category' => 'Engineering',
                'company' => ['company_name' => 'Apex Structural Consultants', 'industry' => 'Engineering', 'city' => 'Johor Bahru', 'state' => 'Johor', 'description' => 'A structural engineering consultancy working on commercial and infrastructure projects.'],
                'employer' => ['email' => 'jobs@apexstructural.demo', 'full_name' => 'Apex Structural HR'],
                'jobs' => [
                    ['title' => 'Structural Drafting Intern', 'description' => 'Produce and revise structural drawings under the supervision of senior drafters.', 'requirements' => 'Civil or Structural Engineering student. AutoCAD proficiency required.', 'work_mode' => 'Onsite', 'allowance' => 800, 'duration_months' => 4, 'vacancies' => 2],
                    ['title' => 'Project Management Intern', 'description' => 'Track project schedules, minutes, and contractor deliverables.', 'requirements' => 'Engineering or Construction Management student. Organised communicator.', 'work_mode' => 'Hybrid', 'allowance' => 850, 'duration_months' => 5, 'vacancies' => 1],
                    ['title' => 'Quantity Surveying Intern', 'description' => 'Assist with cost estimates, BQ preparation, and progress claim checking.', 'requirements' => 'Quantity Surveying student. Comfortable with spreadsheets.', 'work_mode' => 'Onsite', 'allowance' => 780, 'duration_months' => 6, 'vacancies' => 2],
                ],
            ],
            [
                'category' => 'Healthcare',
                'company' => ['company_name' => 'Nova Medical Centre', 'industry' => 'Healthcare', 'city' => 'Ipoh', 'state' => 'Perak', 'description' => 'A multi-disciplinary medical centre offering specialist outpatient services.'],
                'employer' => ['email' => 'recruit@novamedical.demo', 'full_name' => 'Nova Medical Centre HR'],
                'jobs' => [
                    ['title' => 'Pharmacy Assistant Intern', 'description' => 'Support pharmacists with dispensing workflows and stock reconciliation.', 'requirements' => 'Pharmacy or Life Sciences student. Accuracy under pressure.', 'work_mode' => 'Onsite', 'allowance' => 700, 'duration_months' => 3, 'vacancies' => 2],
                    ['title' => 'Medical Records Intern', 'description' => 'Digitise and index patient records in line with confidentiality policy.', 'requirements' => 'Health Information or Business student. Strong attention to detail.', 'work_mode' => 'Onsite', 'allowance' => 650, 'duration_months' => 4, 'vacancies' => 1],
                    ['title' => 'Patient Experience Intern', 'description' => 'Gather patient feedback and help the team act on service improvements.', 'requirements' => 'Public Health, Psychology, or Business student. Empathetic communicator.', 'work_mode' => 'Hybrid', 'allowance' => 720, 'duration_months' => 3, 'vacancies' => 2],
                ],
            ],
        ];

        foreach ($categories as $entry) {
            if (Company::where('company_name', $entry['company']['company_name'])->exists()) {
                $this->command->info("{$entry['company']['company_name']} already seeded, skipping.");

                continue;
            }

            $user = User::create([
                'full_name' => $entry['employer']['full_name'],
                'email' => $entry['employer']['email'],
                'password' => 'password123',
                'role' => 'Employer',
                'status' => 'Active',
            ]);

            $employer = Employer::create(['user_id' => $user->user_id]);

            $company = Company::create($entry['company'] + ['employer_id' => $employer->employer_id]);

            foreach ($entry['jobs'] as $job) {
                $company->internships()->create($job + [
                    'category' => $entry['category'],
                    'city' => $entry['company']['city'],
                    'state' => $entry['company']['state'],
                    'application_deadline' => now()->addMonths(2)->toDateString(),
                    'status' => 'Published',
                    'published_at' => now(),
                ]);
            }

            $this->command->info("Seeded {$entry['company']['company_name']} with " . count($entry['jobs']) . ' internships.');
        }
    }
}
