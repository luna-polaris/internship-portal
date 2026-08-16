<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Internship Details — InternHub</title>
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
        <a href="{{ url('/internships') }}" class="back-home">&larr; Back to Search</a>
    </header>

    <main class="app-main">
        <div id="detail-alert" class="alert"></div>
        <div id="detail-content">
            <p class="list-card__empty">Loading…</p>
        </div>
    </main>

    <script>
        const internshipId = {{ (int) $internship }};
        const token = localStorage.getItem('internhub_token');
        const role = localStorage.getItem('internhub_role');
        const detailContent = document.getElementById('detail-content');
        const detailAlert = document.getElementById('detail-alert');

        function escapeHtml(value) {
            const div = document.createElement('div');
            div.textContent = value ?? '';
            return div.innerHTML;
        }

        function showAlert(message, type) {
            detailAlert.textContent = message;
            detailAlert.className = 'alert show alert-' + type;
        }

        function bookmarkButtonHtml() {
            if (!token || role !== 'Student') return '';
            return `<button type="button" class="btn-secondary" id="bookmark-btn" style="margin-top:1rem;">Loading…</button>`;
        }

        async function bindBookmarkButton() {
            const btn = document.getElementById('bookmark-btn');
            if (!btn) return;

            let bookmarked = false;
            try {
                const response = await fetch('{{ url('/api/student/bookmarks') }}', {
                    headers: { Authorization: 'Bearer ' + token },
                });
                const data = await response.json();
                bookmarked = (data.data || []).some((b) => b.internship_id === internshipId);
            } catch (err) {
                // Assume not bookmarked if this fails — button still works on click.
            }
            btn.textContent = bookmarked ? '★ Bookmarked' : '☆ Bookmark';

            btn.addEventListener('click', async () => {
                btn.disabled = true;
                try {
                    const response = await fetch(`{{ url('/api/student/internships') }}/${internshipId}/bookmark`, {
                        method: 'POST',
                        headers: { Authorization: 'Bearer ' + token },
                    });
                    const data = await response.json();
                    if (response.ok) {
                        btn.textContent = data.bookmarked ? '★ Bookmarked' : '☆ Bookmark';
                    }
                } catch (err) {
                    // Ignore — button just stays in its previous state.
                }
                btn.disabled = false;
            });
        }

        function applyFormHtml() {
            if (role === 'Employer' || role === 'Admin') {
                return '';
            }
            if (!token || role !== 'Student') {
                return `<p class="app-subtitle"><a href="{{ url('/login') }}">Log in as a student</a> to apply for this internship.</p>`;
            }
            return `
                <form id="apply-form" class="auth-card wide" style="margin-top:2rem;" novalidate>
                    <fieldset>
                        <legend>Apply for this Internship</legend>
                        <div class="field-group">
                            <label for="cover_letter">Cover Letter (optional)</label>
                            <textarea id="cover_letter" name="cover_letter" maxlength="2000" placeholder="Tell the employer why you're a good fit"></textarea>
                        </div>
                    </fieldset>
                    <button type="submit" class="btn-submit">Submit Application</button>
                </form>
            `;
        }

        function bindApplyForm() {
            const form = document.getElementById('apply-form');
            if (!form) return;

            form.addEventListener('submit', async (event) => {
                event.preventDefault();
                const submitBtn = form.querySelector('button[type="submit"]');
                submitBtn.disabled = true;

                try {
                    const response = await fetch(`{{ url('/api/student/internships') }}/${internshipId}/apply`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            Accept: 'application/json',
                            Authorization: 'Bearer ' + token,
                        },
                        body: JSON.stringify({ cover_letter: document.getElementById('cover_letter').value }),
                    });
                    const data = await response.json();

                    if (!response.ok) {
                        showAlert(data.message || 'Could not submit application.', 'error');
                        submitBtn.disabled = false;
                        return;
                    }

                    showAlert('Application submitted! You can track its status on your "My Applications" page.', 'success');
                    form.remove();
                } catch (err) {
                    showAlert('Network error. Please try again.', 'error');
                    submitBtn.disabled = false;
                }
            });
        }

        fetch(`{{ url('/api/internships') }}/${internshipId}`)
            .then((res) => (res.ok ? res.json() : Promise.reject()))
            .then((json) => {
                const item = json.data;
                const company = item.company || {};
                const location = [item.city, item.state].filter(Boolean).join(', ') || 'Location not listed';
                const allowance = item.allowance ? `RM ${Number(item.allowance)}/month` : 'Unpaid / not specified';
                const deadline = item.application_deadline ? new Date(item.application_deadline).toLocaleDateString() : 'No fixed deadline';

                detailContent.innerHTML = `
                    <h1 class="app-heading">${escapeHtml(item.title)}</h1>
                    <p class="app-subtitle">${escapeHtml(company.company_name)} — ${escapeHtml(location)}</p>
                    ${bookmarkButtonHtml()}
                    <div class="list-card" style="margin-bottom:2rem;">
                        <p class="list-card__meta">Work Mode: ${escapeHtml(item.work_mode)}</p>
                        <p class="list-card__meta">Allowance: ${allowance}</p>
                        <p class="list-card__meta">Vacancies: ${escapeHtml(String(item.vacancies))}</p>
                        <p class="list-card__meta">Application Deadline: ${escapeHtml(deadline)}</p>
                    </div>
                    <div class="auth-card wide">
                        <fieldset>
                            <legend>Description</legend>
                            <p style="white-space:pre-wrap;">${escapeHtml(item.description)}</p>
                        </fieldset>
                        ${item.requirements ? `<fieldset><legend>Requirements</legend><p style="white-space:pre-wrap;">${escapeHtml(item.requirements)}</p></fieldset>` : ''}
                        <fieldset>
                            <legend>About ${escapeHtml(company.company_name)}</legend>
                            <p>${escapeHtml(company.description || 'No company description provided.')}</p>
                        </fieldset>
                    </div>
                    ${applyFormHtml()}
                `;
                bindApplyForm();
                bindBookmarkButton();
            })
            .catch(() => {
                detailContent.innerHTML = '<p class="list-card__empty">This internship posting could not be found — it may be unpublished, closed, or expired.</p>';
            });
    </script>
</body>
</html>
