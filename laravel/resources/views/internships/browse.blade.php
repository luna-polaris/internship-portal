<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse Internships — InternHub</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Anton&family=Space+Mono:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('stylesheet/stylePublic.css') }}">
    <link rel="stylesheet" href="{{ asset('stylesheet/styleAuth.css') }}">
    <link rel="stylesheet" href="{{ asset('stylesheet/styleApp.css') }}">
</head>
<body class="auth-body">

    <header class="auth-header">
        <a href="{{ url('/') }}" class="logo">Intern<span>—</span>Hub</a>
        <a href="{{ url('/') }}" class="back-home">&larr; Back to Home</a>
    </header>

    <main class="app-main">
        <h1 class="app-heading">Browse Internships</h1>
        <p class="app-subtitle">Search open internship postings from registered companies.</p>

        <section id="recommended-section" style="display:none;">
            <h2 class="app-heading" style="font-size:1.3rem;">Recommended For You</h2>
            <div id="recommended-results" class="card-grid">
                <p class="list-card__empty">Loading recommendations…</p>
            </div>
        </section>

        <form id="filter-form" class="filter-bar" novalidate>
            <div class="field-group">
                <label for="q">Keyword</label>
                <input type="text" id="q" name="q" placeholder="Job title or description">
            </div>
            <div class="field-group">
                <label for="city">City</label>
                <input type="text" id="city" name="city">
            </div>
            <div class="field-group">
                <label for="category">Category</label>
                <select id="category" name="category">
                    <option value="">Any</option>
                    <option value="Finance">Finance & Accounting</option>
                    <option value="Biotechnology">Biotechnology</option>
                    <option value="IT">Information Technology</option>
                    <option value="Engineering">Engineering</option>
                    <option value="Healthcare">Healthcare</option>
                </select>
            </div>
            <div class="field-group">
                <label for="work_mode">Work Mode</label>
                <select id="work_mode" name="work_mode">
                    <option value="">Any</option>
                    <option value="Onsite">Onsite</option>
                    <option value="Remote">Remote</option>
                    <option value="Hybrid">Hybrid</option>
                </select>
            </div>
            <div class="field-group">
                <label for="min_allowance">Min Allowance</label>
                <input type="number" id="min_allowance" name="min_allowance" min="0">
            </div>
            <div class="field-group">
                <label for="sort">Sort By</label>
                <select id="sort" name="sort">
                    <option value="">Newest</option>
                    <option value="deadline">Deadline Soonest</option>
                    <option value="allowance">Highest Allowance</option>
                </select>
            </div>
            <button type="submit" class="btn-submit">Search</button>
        </form>

        <div id="results" class="card-grid">
            <p class="list-card__empty">Loading internships…</p>
        </div>

        <div id="pagination" class="pagination"></div>
    </main>

    <script>
        const resultsEl = document.getElementById('results');
        const paginationEl = document.getElementById('pagination');
        const form = document.getElementById('filter-form');
        const token = localStorage.getItem('internhub_token');
        const role = localStorage.getItem('internhub_role');
        let bookmarkedIds = new Set();

        function escapeHtml(value) {
            const div = document.createElement('div');
            div.textContent = value ?? '';
            return div.innerHTML;
        }

        function applyFiltersFromUrl() {
            const params = new URLSearchParams(window.location.search);
            if (params.has('q')) document.getElementById('q').value = params.get('q');
            if (params.has('city')) document.getElementById('city').value = params.get('city');
            if (params.has('category')) document.getElementById('category').value = params.get('category');
            if (params.has('work_mode')) document.getElementById('work_mode').value = params.get('work_mode');
            if (params.has('min_allowance')) document.getElementById('min_allowance').value = params.get('min_allowance');
            if (params.has('sort')) document.getElementById('sort').value = params.get('sort');
        }

        async function loadBookmarkedIds() {
            if (!token || role !== 'Student') return;
            try {
                const response = await fetch('{{ url('/api/student/bookmarks') }}', {
                    headers: { Authorization: 'Bearer ' + token },
                });
                const data = await response.json();
                bookmarkedIds = new Set((data.data || []).map((b) => b.internship_id));
            } catch (err) {
                // Bookmark state just won't be pre-filled — buttons still work on click.
            }
        }

        async function toggleBookmark(id, btn) {
            btn.disabled = true;
            try {
                const response = await fetch(`{{ url('/api/student/internships') }}/${id}/bookmark`, {
                    method: 'POST',
                    headers: { Authorization: 'Bearer ' + token },
                });
                const data = await response.json();
                if (response.ok) {
                    if (data.bookmarked) {
                        bookmarkedIds.add(id);
                        btn.textContent = '★ Bookmarked';
                    } else {
                        bookmarkedIds.delete(id);
                        btn.textContent = '☆ Bookmark';
                    }
                }
            } catch (err) {
                // Ignore — button just stays in its previous state.
            }
            btn.disabled = false;
        }

        function buildCard(item) {
            const card = document.createElement('div');
            card.className = 'list-card';

            const company = escapeHtml(item.company ? item.company.company_name : 'Unknown Company');
            const location = escapeHtml([item.city, item.state].filter(Boolean).join(', ') || 'Location not listed');
            const allowance = item.allowance ? `RM ${Number(item.allowance)}/month` : 'Unpaid / not specified';

            card.innerHTML = `
                <a class="card-link" href="{{ url('/internships') }}/${item.internship_id}">
                    <h3>${escapeHtml(item.title)}</h3>
                </a>
                <p class="list-card__meta">${company} — ${location}</p>
                <p class="list-card__meta">${escapeHtml(item.work_mode)} · ${allowance}</p>
                <span class="badge badge-published">Published</span>
                <div class="list-card__actions"></div>
            `;

            if (token && role === 'Student') {
                const isBookmarked = bookmarkedIds.has(item.internship_id);
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'btn-secondary';
                btn.textContent = isBookmarked ? '★ Bookmarked' : '☆ Bookmark';
                btn.addEventListener('click', () => toggleBookmark(item.internship_id, btn));
                card.querySelector('.list-card__actions').appendChild(btn);
            }

            return card;
        }

        function renderResults(payload) {
            const items = payload.data.data || [];
            resultsEl.innerHTML = '';

            if (!items.length) {
                resultsEl.innerHTML = '<p class="list-card__empty">No internships match your search.</p>';
                paginationEl.innerHTML = '';
                return;
            }

            items.forEach((item) => resultsEl.appendChild(buildCard(item)));

            renderPagination(payload.data);
        }

        async function loadRecommended() {
            const section = document.getElementById('recommended-section');
            const recommendedEl = document.getElementById('recommended-results');

            if (!token || role !== 'Student') return;
            section.style.display = 'block';

            try {
                const response = await fetch('{{ url('/api/student/internships/recommended') }}', {
                    headers: { Authorization: 'Bearer ' + token },
                });
                const payload = await response.json();
                const items = payload.data || [];

                recommendedEl.innerHTML = '';
                if (!items.length) {
                    recommendedEl.innerHTML = '<p class="list-card__empty">No recommendations yet — check back once more internships are posted.</p>';
                    return;
                }

                items.forEach((item) => recommendedEl.appendChild(buildCard(item)));
            } catch (err) {
                section.style.display = 'none';
            }
        }

        function renderPagination(page) {
            paginationEl.innerHTML = '';
            if (!page.last_page || page.last_page <= 1) return;

            for (let i = 1; i <= page.last_page; i++) {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'btn-secondary';
                btn.textContent = i;
                if (i === page.current_page) btn.disabled = true;
                btn.addEventListener('click', () => search(i));
                paginationEl.appendChild(btn);
            }
        }

        async function search(page = 1) {
            const params = new URLSearchParams();
            const q = document.getElementById('q').value.trim();
            const city = document.getElementById('city').value.trim();
            const category = document.getElementById('category').value.trim();
            const workMode = document.getElementById('work_mode').value;
            const minAllowance = document.getElementById('min_allowance').value;
            const sort = document.getElementById('sort').value;

            if (q) params.set('q', q);
            if (city) params.set('city', city);
            if (category) params.set('category', category);
            if (workMode) params.set('work_mode', workMode);
            if (minAllowance) params.set('min_allowance', minAllowance);
            if (sort) params.set('sort', sort);
            params.set('page', page);

            resultsEl.innerHTML = '<p class="list-card__empty">Loading internships…</p>';

            try {
                const response = await fetch('{{ url('/api/internships') }}?' + params.toString());
                const payload = await response.json();
                renderResults(payload);
            } catch (err) {
                resultsEl.innerHTML = '<p class="list-card__empty">Could not load internships. Please try again.</p>';
            }
        }

        form.addEventListener('submit', (event) => {
            event.preventDefault();
            search(1);
        });

        applyFiltersFromUrl();
        loadBookmarkedIds().then(() => {
            search(1);
            loadRecommended();
        });
    </script>
</body>
</html>
