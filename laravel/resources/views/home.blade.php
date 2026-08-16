<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>InternHub — Internship Portal</title>
    <meta name="description" content="Explore internship opportunities across finance, biotechnology, IT, engineering, and healthcare in one modern portal.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Anton&family=Space+Mono:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('stylesheet/stylePublic.css') }}">
    <link rel="stylesheet" href="{{ asset('stylesheet/styleExtend.css') }}">
    <link rel="stylesheet" href="{{ asset('fontawesome/css/font-awesome.min.css') }}">

</head>
<body>

<div class="hero-stage">
    <!-- Global Header -->
    <header class="global-header">
        <a href="#" class="logo">Intern<span>—</span>Hub</a>
        <nav class="nav">
            <a href="#work">Internships</a>
            <a href="{{ url('/internships') }}">Browse Internships</a>
            <a href="#how">How It Works</a>
            <a href="#about">About</a>
            <a href="#contact">Contact</a>
            <span id="nav-auth-links">
                <a href="{{ url('/login') }}">Log In</a>
                <a href="{{ url('/register') }}" class="nav__cta">Register</a>
            </span>
            <div id="nav-user-widget" class="nav-user">
                <button type="button" class="nav-user__trigger" id="nav-user-trigger">
                    <span class="nav-user__avatar" id="nav-user-avatar"></span>
                    <span class="nav-user__name" id="nav-user-name"></span>
                </button>
                <div class="nav-user__dropdown">
                    <a href="{{ url('/employer/internships') }}" id="nav-employer-link" style="display:none;">My Postings</a>
                    <a href="{{ url('/student/applications') }}" id="nav-student-link" style="display:none;">My Applications</a>
                    <a href="{{ url('/student/bookmarks') }}" id="nav-student-bookmarks-link" style="display:none;">My Bookmarks</a>
                    <a href="{{ url('/admin/dashboard') }}" id="nav-admin-dashboard-link" style="display:none;">Admin Dashboard</a>
                    <a href="{{ url('/profile') }}">Settings</a>
                    <a href="{{ url('/evaluations/create') }}" id="nav-eval-write-link" style="display:none;">Evaluate An Intern</a>
                    <a href="{{ url('/admin/evaluations') }}" id="nav-eval-review-link" style="display:none;">Review Queue</a>
                    <a href="{{ url('/admin/criteria') }}" id="nav-eval-criteria-link" style="display:none;">Evaluation Criteria</a>
                    <a href="{{ url('/performance') }}" id="nav-eval-perf-link" style="display:none;">Performance</a>
                    <button type="button" id="nav-logout-btn">Log Out</button>
                </div>
            </div>
        </nav>
    </header>

    <!-- The Track -->
    <main class="track" role="region" aria-label="Internship Opportunities">

        <!-- Panel 01 -->
        <article class="panel">
            <div class="panel__bg">
                <img src="{{ asset('images/public01.jpg') }}"
                     alt="Finance and accounting workspace with charts and documents"
                     loading="eager">
            </div>
            <span class="panel__index">01 / 05</span>
            <h2 class="spine">Acc & Fin</h2>
            <div class="details">
                <p class="details__category">Accounting, Auditing, Finance</p>
                <h3 class="details__title">Finance &<br>Accounting</h3>
                <div class="details__meta" data-category="finance">
                    <div class="details__meta-item"> <!-- No. of companies registered in this section -->
                        <span class="details__meta-value" data-metric="companies">—</span>
                        <span class="details__meta-label">Companies</span>
                    </div>
                    <div class="details__meta-item"><!-- No. of jobs available -->
                        <span class="details__meta-value" data-metric="jobs">Soon</span>
                        <span class="details__meta-label">Jobs</span>
                    </div>

                </div>
                <p class="details__description">Gain hands-on experience in budgeting, reporting, and financial analysis while supporting real client accounts.</p>
                <a href="{{ url('/internships') }}?category=Finance" class="btn-view">
                    Apply Now
                    <svg viewBox="0 0 14 14" fill="none">
                        <path d="M1 7h12M8 2l5 5-5 5" stroke="currentColor" stroke-width="1.5" stroke-linecap="square"/>
                    </svg>
                </a>
            </div>
        </article>

        <!-- Panel 02 -->
        <article class="panel">
            <div class="panel__bg">
                <img src="{{ asset('images/public02.jpg') }}"
                     alt="Biotechnology lab environment with modern research equipment"
                     loading="lazy">
            </div>
            <span class="panel__index">02 / 05</span>
            <h2 class="spine">Bio</h2>
            <div class="details">
                <p class="details__category">Biology, Research, Lab Operations</p>
                <h3 class="details__title">Biotechnology</h3>
                <div class="details__meta" data-category="biotechnology">
                    <div class="details__meta-item"> <!-- No. of companies registered in this section -->
                        <span class="details__meta-value" data-metric="companies">—</span>
                        <span class="details__meta-label">Companies</span>
                    </div>
                    <div class="details__meta-item"><!-- No. of jobs available -->
                        <span class="details__meta-value" data-metric="jobs">Soon</span>
                        <span class="details__meta-label">Jobs</span>
                    </div>

                </div>
                <p class="details__description">Work with research teams on experiments, documentation, and innovation projects in a fast-moving science environment.</p>
                <a href="{{ url('/internships') }}?category=Biotechnology" class="btn-view">
                    Apply Now
                    <svg viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M1 7h12M8 2l5 5-5 5" stroke="currentColor" stroke-width="1.5" stroke-linecap="square"/>
                    </svg>
                </a>
            </div>
        </article>

        <!-- Panel 03 -->
        <article class="panel">
            <div class="panel__bg">
                <img src="{{ asset('images/public03.jpg') }}"
                     alt="Technology workspace with data dashboards and collaboration tools"
                     loading="lazy">
            </div>
            <span class="panel__index">03 / 05</span>
            <h2 class="spine">IT</h2>
            <div class="details">
                <p class="details__category">Software Development, Data, Cybersecurity</p>
                <h3 class="details__title">Information<br>Technology</h3>
                <div class="details__meta" data-category="it">
                    <div class="details__meta-item"> <!-- No. of companies registered in this section -->
                        <span class="details__meta-value" data-metric="companies">—</span>
                        <span class="details__meta-label">Companies</span>
                    </div>
                    <div class="details__meta-item"><!-- No. of jobs available -->
                        <span class="details__meta-value" data-metric="jobs">Soon</span>
                        <span class="details__meta-label">Jobs</span>
                    </div>
                </div>
                <p class="details__description">Build, test, and improve digital products while learning modern development practices from experienced mentors.</p>
                <a href="{{ url('/internships') }}?category=IT" class="btn-view">
                    Apply Now
                    <svg viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M1 7h12M8 2l5 5-5 5" stroke="currentColor" stroke-width="1.5" stroke-linecap="square"/>
                    </svg>
                </a>
            </div>
        </article>

        <!-- Panel 04 -->
        <article class="panel">
            <div class="panel__bg">
                <img src="{{ asset('images/public04.jpg') }}"
                     alt="Engineering project environment with design and planning visuals"
                     loading="lazy">
            </div>
            <span class="panel__index">04 / 05</span>
            <h2 class="spine">Engineering</h2>
            <div class="details">
                <p class="details__category">Mechanical, Civil, Product Design</p>
                <h3 class="details__title">Engineering</h3>
                <div class="details__meta" data-category="engineering">
                    <div class="details__meta-item"> <!-- No. of companies registered in this section -->
                        <span class="details__meta-value" data-metric="companies">—</span>
                        <span class="details__meta-label">Companies</span>
                    </div>
                    <div class="details__meta-item"><!-- No. of jobs available -->
                        <span class="details__meta-value" data-metric="jobs">Soon</span>
                        <span class="details__meta-label">Jobs</span>
                    </div>
                </div>
                <p class="details__description">Explore design systems, prototyping, and project planning through hands-on engineering assignments.</p>
                <a href="{{ url('/internships') }}?category=Engineering" class="btn-view">
                    Apply Now
                    <svg viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M1 7h12M8 2l5 5-5 5" stroke="currentColor" stroke-width="1.5" stroke-linecap="square"/>
                    </svg>
                </a>
            </div>
        </article>

        <!-- Panel 05 -->
        <article class="panel">
            <div class="panel__bg">
                <img src="{{ asset('images/public05.jpg') }}"
                     alt="Healthcare environment focused on patient care and operations"
                     loading="lazy">
            </div>
            <span class="panel__index">05 / 05</span>
            <h2 class="spine">Healthcare</h2>
            <div class="details">
                <p class="details__category">Clinical Support, Research, Administration</p>
                <h3 class="details__title">Healthcare</h3>
                <div class="details__meta" data-category="healthcare">
                    <div class="details__meta-item"> <!-- No. of companies registered in this section -->
                        <span class="details__meta-value" data-metric="companies">—</span>
                        <span class="details__meta-label">Companies</span>
                    </div>
                    <div class="details__meta-item"><!-- No. of jobs available -->
                        <span class="details__meta-value" data-metric="jobs">Soon</span>
                        <span class="details__meta-label">Jobs</span>
                    </div>
                </div>
                <p class="details__description">Support patient-facing operations and health innovation programs while learning how the sector works in practice.</p>
                <a href="{{ url('/internships') }}?category=Healthcare" class="btn-view">
                    Apply Now
                    <svg viewBox="0 0 14 14" fill="none">
                        <path d="M1 7h12M8 2l5 5-5 5" stroke="currentColor" stroke-width="1.5" stroke-linecap="square"/>
                    </svg>
                </a>
            </div>
        </article>

    </main>

    <!-- Global Footer -->
    <footer class="global-footer">
        <p class="footer__credit">Internship portal for students and employers &copy; 2026</p>
        <p class="footer__scroll-hint">Hover to Explore</p>
    </footer>
</div>

    <!-- ============ CTA BANNER (layout: appendMainPage/index.html tm-section-2) ============ -->
    <section class="ext-cta" id="get-started">
        <div class="ext-container">
            <h2 class="ext-heading">Ready To Get Started?</h2>
            <p class="ext-subtext">Create a free account as a student looking for internships, or an employer looking to hire.</p>
            <a href="{{ url('/register') }}" class="ext-btn">Create Free Account</a>
            <p class="ext-btn-ghost">Already have an account? <a href="{{ url('/login') }}">Log in</a></p>
        </div>
    </section>

    <!-- ============ WHY INTERNHUB (layout: appendMainPage/index.html 3-article tm-section) ============ -->
    <section class="ext-section" id="why">
        <div class="ext-container" style="text-align:center;">
            <p class="ext-eyebrow" style="justify-content:center;">Why InternHub</p>
            <h2 class="ext-heading">Built For Students<br>And Employers Alike</h2>
        </div>
        <div class="ext-container ext-features-grid">
            <article>
                <div class="ext-feature-icon"><i class="fa fa-graduation-cap" aria-hidden="true"></i></div>
                <h3 class="ext-feature-title">Built For Students</h3>
                <p class="ext-feature-text">Create a verified academic profile with your matric number, programme, and CGPA, so employers see a credible application.</p>
            </article>
            <article>
                <div class="ext-feature-icon"><i class="fa fa-briefcase" aria-hidden="true"></i></div>
                <h3 class="ext-feature-title">Built For Employers</h3>
                <p class="ext-feature-text">Register your company, describe your industry and location, and reach students actively looking for real-world experience.</p>
            </article>
            <article>
                <div class="ext-feature-icon"><i class="fa fa-lock" aria-hidden="true"></i></div>
                <h3 class="ext-feature-title">Secure By Design</h3>
                <p class="ext-feature-text">Role-based access, hashed passwords, and a REST API secured with Laravel Sanctum keep every account protected.</p>
            </article>
        </div>
    </section>

    <!-- ============ FEATURED COMPANIES (layout: appendMainPage/index.html tm-section-4 carousel + sidebar) ============ -->
    <section class="ext-section ext-section--alt" id="explore">
        <div class="ext-container">
            <p class="ext-eyebrow">Live From The Directory</p>
            <h2 class="ext-heading">Featured Companies</h2>
            <p class="ext-subtext">Pulled straight from the REST API — these are real companies already registered on InternHub.</p>
        </div>
        <div class="ext-container ext-explore-layout">
            <div id="ext-company-carousel" class="ext-carousel">
                <p class="ext-explore-empty">Loading companies…</p>
            </div>
        </div>
    </section>

    <!-- ============ GET IN TOUCH (layout: appendMainPage/index.html tm-section-6 map + form) ============ -->
    <section class="ext-section" id="reach">
        <div class="ext-container">
            <p class="ext-eyebrow">Connect</p>
            <h2 class="ext-heading">Get In Touch</h2>
        </div>
        <div class="ext-container ext-contact-layout">
            <div id="ext-map">
                @if (config('services.google.maps_key'))
                    Loading map…
                @else
                    Google Maps is not configured (GOOGLE_MAPS_API_KEY is empty in .env).
                @endif
            </div>

            <div class="ext-contact-card">
                <p class="ext-subtext">Looking for internship opportunities or want to partner with our portal? Reach out and we will help you get started.</p>
                <div class="ext-contact-grid">
                    <div>
                        <p class="ext-contact-item-label">Email</p>
                        <p class="ext-contact-item-value"><a href="mailto:careers@internhub.edu.my">careers@internhub.edu.my</a></p>
                    </div>
                    <div>
                        <p class="ext-contact-item-label">Phone</p>
                        <p class="ext-contact-item-value"><a href="tel:+1234567890">+60 (123) 456-7890</a></p>
                    </div>
                    <div>
                        <p class="ext-contact-item-label">Support</p>
                        <p class="ext-contact-item-value"><a href="mailto:student-support@internhub.edu.my">student-support@internhub.edu.my</a></p>
                    </div>
                    <div>
                        <p class="ext-contact-item-label">Location</p>
                        <p class="ext-contact-item-value">Kuala Lumpur, Malaysia</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============ FOOTER (layout: appendMainPage/index.html copyright bar) ============ -->
    <footer class="ext-footer">
        <div class="ext-footer-links">
            <a href="{{ url('/') }}">Home</a>
            <a href="{{ url('/login') }}">Log In</a>
            <a href="{{ url('/register') }}">Register</a>
            <a href="#about">About</a>
            <a href="#reach">Contact</a>
        </div>
        <p>Copyright &copy; <span id="ext-current-year">2026</span> InternHub — Connecting Students &amp; Employers</p>
    </footer>

    <!-- Internships Modal -->
    <div id="work" class="modal-overlay">
        <a href="#" class="modal__close" aria-label="Close">&times;</a>
        <div class="modal__content">
            <p class="modal__label">Opportunities</p>
            <h2 class="modal__heading">Explore<br>Internships</h2>
            <p class="modal__text">Discover structured internship programs designed for students who want real-world experience, mentorship, and a strong start to their careers.</p>
            <hr class="modal__divider">
            <ul class="modal__project-list">
                <li>
                    <span class="modal__project-name">Finance & Accounting</span>
                    <span class="modal__project-year">2026 — Paid Internship</span>
                </li>
                <li>
                    <span class="modal__project-name">Biotechnology Research</span>
                    <span class="modal__project-year">2026 — Lab Internship</span>
                </li>
                <li>
                    <span class="modal__project-name">Software Development</span>
                    <span class="modal__project-year">2026 — Tech Internship</span>
                </li>
                <li>
                    <span class="modal__project-name">Engineering Projects</span>
                    <span class="modal__project-year">2026 — Design Internship</span>
                </li>
                <li>
                    <span class="modal__project-name">Healthcare Operations</span>
                    <span class="modal__project-year">2026 — Clinical Internship</span>
                </li>
            </ul>
        </div>
    </div>

    <!-- How It Works Modal -->
    <div id="how" class="modal-overlay">
        <a href="#" class="modal__close" aria-label="Close">&times;</a>
        <div class="modal__content">
            <p class="modal__label">Get Started</p>
            <h2 class="modal__heading">How It<br>Works</h2>
            <p class="modal__text">InternHub matches students and employers through a simple, three-step flow — backed by a REST API so your data works the same whether you're on the web app or a future mobile client.</p>
            <hr class="modal__divider">
            <ul class="modal__project-list">
                <li>
                    <span class="modal__project-name">1. Create your account</span>
                    <span class="modal__project-year">Student or Employer</span>
                </li>
                <li>
                    <span class="modal__project-name">2. Complete your profile</span>
                    <span class="modal__project-year">Academic or company details</span>
                </li>
                <li>
                    <span class="modal__project-name">3. Connect</span>
                    <span class="modal__project-year">Students apply, employers hire</span>
                </li>
            </ul>
            <hr class="modal__divider">
            <p class="modal__text">
                <a href="{{ url('/register') }}" style="color: var(--neon-chartreuse); font-weight: 700;">Register now</a>
                &nbsp;or&nbsp;
                <a href="{{ url('/login') }}" style="color: var(--off-white);">log in to your account</a>.
            </p>
        </div>
    </div>

    <!-- About Modal -->
    <div id="about" class="modal-overlay">
        <a href="#" class="modal__close" aria-label="Close">&times;</a>
        <div class="modal__content">
            <p class="modal__label">Portal</p>
            <h2 class="modal__heading">About<br>the Platform</h2>
            <p class="modal__text"><strong>InternHub</strong> connects ambitious students with internships that build practical skills, confidence, and career-ready experience.</p>
            <p class="modal__text">Our platform highlights opportunities across multiple industries so students can explore roles that match their interests and academic background.</p>
            <hr class="modal__divider">
            <p class="modal__text"><strong>Included areas</strong><br>Finance / Biotechnology / Information Technology / Engineering / Healthcare</p>
            <hr class="modal__divider">
            <p class="modal__text">Students can browse openings, learn about company expectations, and apply for the next step in their professional journey.</p>
        </div>
    </div>

    <!-- Contact Modal -->
    <div id="contact" class="modal-overlay">
        <a href="#" class="modal__close" aria-label="Close">&times;</a>
        <div class="modal__content">
            <p class="modal__label">Connect</p>
            <h2 class="modal__heading">Get In<br>Touch</h2>
            <p class="modal__text">Looking for internship opportunities or want to partner with our portal? Reach out and we will help you get started.</p>
            <hr class="modal__divider">
            <div class="modal__contact-grid">
                <div class="modal__contact-item">
                    <p class="modal__contact-label">Email</p>
                    <p class="modal__contact-value"><a href="mailto:careers@internhub.edu">careers@internhub.edu</a></p>
                </div>
                <div class="modal__contact-item">
                    <p class="modal__contact-label">Phone</p>
                    <p class="modal__contact-value"><a href="tel:+1234567890">+60 (123) 456-7890</a></p>
                </div>
                <div class="modal__contact-item">
                    <p class="modal__contact-label">Support</p>
                    <p class="modal__contact-value"><a href="#">student-support@internhub.edu</a></p>
                </div>
                <div class="modal__contact-item">
                    <p class="modal__contact-label">Location</p>
                    <p class="modal__contact-value">Nairobi, Kenya</p>
                </div>
            </div>
        </div>
    </div>
    <script src="{{ asset('scripts/scriptPublic.js') }}"></script>
    <script>
        // Pull live company counts per industry from the REST API and fill
        // in the "companies registered" figure on each panel.
        (function () {
            const categoryKeywords = {
                finance: ['finance', 'accounting', 'audit', 'bank'],
                biotechnology: ['bio', 'pharma', 'lab', 'science'],
                it: ['it', 'tech', 'software', 'data', 'cyber', 'information'],
                engineering: ['engineer', 'manufactur', 'construct', 'mechanical', 'civil'],
                healthcare: ['health', 'clinical', 'medical', 'hospital'],
            };

            fetch('{{ url('/api/companies') }}', { headers: { Accept: 'application/json' } })
                .then((res) => (res.ok ? res.json() : Promise.reject()))
                .then((json) => {
                    const companies = json.data || [];
                    const counts = { finance: 0, biotechnology: 0, it: 0, engineering: 0, healthcare: 0 };

                    companies.forEach((company) => {
                        const industry = (company.industry || '').toLowerCase();
                        Object.keys(categoryKeywords).forEach((category) => {
                            if (categoryKeywords[category].some((kw) => industry.includes(kw))) {
                                counts[category]++;
                            }
                        });
                    });

                    document.querySelectorAll('[data-category]').forEach((el) => {
                        const category = el.dataset.category;
                        const valueEl = el.querySelector('[data-metric="companies"]');
                        if (valueEl) {
                            valueEl.textContent = counts[category] ?? 0;
                        }
                    });

                    renderCompanyCarousel(companies);
                })
                .catch(() => {
                    // API unreachable — leave the placeholder dashes in place.
                    renderCompanyCarousel([]);
                });

            // Same idea for the "jobs available" figure — tally published
            // internships per category from the search API.
            fetch('{{ url('/api/internships') }}?per_page=100', { headers: { Accept: 'application/json' } })
                .then((res) => (res.ok ? res.json() : Promise.reject()))
                .then((json) => {
                    const internships = (json.data && json.data.data) || [];
                    const counts = { finance: 0, biotechnology: 0, it: 0, engineering: 0, healthcare: 0 };

                    internships.forEach((internship) => {
                        const haystack = [internship.category, internship.title, internship.description]
                            .filter(Boolean)
                            .join(' ')
                            .toLowerCase();
                        Object.keys(categoryKeywords).forEach((category) => {
                            if (categoryKeywords[category].some((kw) => haystack.includes(kw))) {
                                counts[category]++;
                            }
                        });
                    });

                    document.querySelectorAll('[data-category]').forEach((el) => {
                        const category = el.dataset.category;
                        const valueEl = el.querySelector('[data-metric="jobs"]');
                        if (valueEl) {
                            valueEl.textContent = counts[category] ?? 0;
                        }
                    });
                })
                .catch(() => {
                    // API unreachable — leave the "Soon" placeholder in place.
                });

            function renderCompanyCarousel(companies) {
                const carousel = document.getElementById('ext-company-carousel');
                if (!carousel) return;

                if (!companies.length) {
                    carousel.innerHTML = '<p class="ext-explore-empty">No companies registered yet — be the first to <a href="{{ url('/register') }}" style="color:var(--neon-chartreuse)">sign up as an employer</a>.</p>';
                    return;
                }

                carousel.innerHTML = '';
                companies.slice(0, 8).forEach((company) => {
                    const card = document.createElement('div');
                    card.className = 'ext-company-card';

                    const industry = document.createElement('p');
                    industry.className = 'ext-company-card__industry';
                    industry.textContent = company.industry || 'General';

                    const name = document.createElement('h4');
                    name.className = 'ext-company-card__name';
                    name.textContent = company.company_name;

                    const meta = document.createElement('p');
                    meta.className = 'ext-company-card__meta';
                    meta.textContent = [company.city, company.state].filter(Boolean).join(', ') || 'Location not listed';

                    card.append(industry, name, meta);
                    carousel.appendChild(card);
                });
            }
        })();

        document.getElementById('ext-current-year').textContent = new Date().getFullYear();

        // --- Google Map for the "Get In Touch" section ---
        function initExtMap() {
            const mapDiv = document.getElementById('ext-map');
            if (!mapDiv) return;
            mapDiv.textContent = '';

            new google.maps.Map(mapDiv, {
                center: { lat: 3.139, lng: 101.6869 }, // Kuala Lumpur — adjust to your real office location
                zoom: 12,
            });
        }

        @if (config('services.google.maps_key'))
            (function () {
                const script = document.createElement('script');
                script.src = 'https://maps.googleapis.com/maps/api/js?key={{ config('services.google.maps_key') }}&callback=initExtMap';
                script.async = true;
                document.head.appendChild(script);
            })();
        @endif

        // --- Nav: swap Login/Register for the logged-in user's avatar + name ---
        (function () {
            const token = localStorage.getItem('internhub_token');
            if (!token) return;

            fetch('{{ url('/api/me') }}', {
                headers: { Accept: 'application/json', Authorization: 'Bearer ' + token },
            })
                .then((res) => (res.ok ? res.json() : Promise.reject()))
                .then((json) => {
                    const user = json.data.user;
                    const displayName = user.full_name;

                    document.getElementById('nav-auth-links').classList.add('is-hidden');
                    document.getElementById('nav-user-widget').classList.add('is-visible');
                    document.getElementById('nav-user-name').textContent = displayName;

                    const role = localStorage.getItem('internhub_role');
                    const show = (id) => {
                        const el = document.getElementById(id);
                        if (el) el.style.display = 'block';
                    };

                    if (role === 'Employer') {
                        show('nav-employer-link');
                        show('nav-eval-write-link');
                        show('nav-eval-perf-link');
                    } else if (role === 'Student') {
                        show('nav-student-link');
                        show('nav-student-bookmarks-link');
                        show('nav-eval-perf-link');
                    } else if (role === 'Admin') {
                        show('nav-admin-dashboard-link');
                        show('nav-eval-review-link');
                        show('nav-eval-criteria-link');
                        show('nav-eval-perf-link');
                    }

                    const avatarEl = document.getElementById('nav-user-avatar');
                    if (user.profile_picture_url) {
                        avatarEl.style.backgroundImage = `url(${user.profile_picture_url})`;
                    } else {
                        avatarEl.textContent = displayName.trim().charAt(0).toUpperCase();
                    }
                })
                .catch(() => {
                    // Token invalid/expired — drop it and leave the default Login/Register nav.
                    localStorage.removeItem('internhub_token');
                    localStorage.removeItem('internhub_role');
                });

            document.getElementById('nav-logout-btn').addEventListener('click', async () => {
                try {
                    await fetch('{{ url('/api/logout') }}', {
                        method: 'POST',
                        headers: { Accept: 'application/json', Authorization: 'Bearer ' + token },
                    });
                } catch (err) {
                    // Ignore — clear local state regardless of whether the request reached the server.
                }
                localStorage.removeItem('internhub_token');
                localStorage.removeItem('internhub_role');
                window.location.href = '{{ url('/') }}';
            });
        })();
    </script>
</body>
</html>
