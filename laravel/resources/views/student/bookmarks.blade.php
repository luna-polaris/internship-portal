<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookmarks — InternHub</title>
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
        <a href="{{ url('/internships') }}" class="back-home">Browse Internships</a>
    </header>

    <main class="app-main">
        <h1 class="app-heading">My Bookmarks</h1>
        <p class="app-subtitle">Internships you've saved to look at later.</p>

        <div id="page-alert" class="alert"></div>

        <div id="bookmarks" class="card-grid">
            <p class="list-card__empty">Loading your bookmarks…</p>
        </div>
    </main>

    <script>
        const token = localStorage.getItem('internhub_token');
        const role = localStorage.getItem('internhub_role');
        if (!token) window.location.href = '{{ url('/login') }}';
        if (role && role !== 'Student') window.location.href = '{{ url('/') }}';

        const authHeaders = { Accept: 'application/json', Authorization: 'Bearer ' + token };
        const listEl = document.getElementById('bookmarks');
        const pageAlert = document.getElementById('page-alert');

        function escapeHtml(value) {
            const div = document.createElement('div');
            div.textContent = value ?? '';
            return div.innerHTML;
        }

        function showAlert(message, type) {
            pageAlert.textContent = message;
            pageAlert.className = 'alert show alert-' + type;
        }

        async function removeBookmark(internshipId) {
            try {
                const response = await fetch(`{{ url('/api/student/internships') }}/${internshipId}/bookmark`, {
                    method: 'POST',
                    headers: authHeaders,
                });
                const data = await response.json();
                showAlert(data.message, response.ok ? 'success' : 'error');
                if (response.ok) load();
            } catch (err) {
                showAlert('Network error. Please try again.', 'error');
            }
        }

        function render(items) {
            listEl.innerHTML = '';

            if (!items.length) {
                listEl.innerHTML = '<p class="list-card__empty">You have not bookmarked any internships yet. <a href="{{ url('/internships') }}" style="color:var(--neon-chartreuse);">Browse open internships</a>.</p>';
                return;
            }

            items.forEach((bookmark) => {
                const internship = bookmark.internship;
                if (!internship) return;

                const company = internship.company ? internship.company.company_name : 'Unknown Company';
                const card = document.createElement('div');
                card.className = 'list-card';

                card.innerHTML = `
                    <a class="card-link" href="{{ url('/internships') }}/${internship.internship_id}">
                        <h3>${escapeHtml(internship.title)}</h3>
                    </a>
                    <p class="list-card__meta">${escapeHtml(company)}</p>
                    <div class="list-card__actions"></div>
                `;

                const actionsEl = card.querySelector('.list-card__actions');
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'btn-danger';
                btn.textContent = 'Remove';
                btn.addEventListener('click', () => removeBookmark(internship.internship_id));
                actionsEl.appendChild(btn);

                listEl.appendChild(card);
            });
        }

        async function load() {
            try {
                const response = await fetch('{{ url('/api/student/bookmarks') }}', { headers: authHeaders });
                const data = await response.json();
                render(data.data || []);
            } catch (err) {
                listEl.innerHTML = '<p class="list-card__empty">Could not load your bookmarks.</p>';
            }
        }

        load();
    </script>
</body>
</html>
