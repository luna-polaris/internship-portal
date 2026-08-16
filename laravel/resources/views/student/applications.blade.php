<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Applications — InternHub</title>
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
        <h1 class="app-heading">My Applications</h1>
        <p class="app-subtitle">Track the status of internships you've applied to.</p>

        <div id="page-alert" class="alert"></div>

        <form id="reschedule-request-form" class="auth-card wide collapsible" novalidate>
            <input type="hidden" id="rr_interview_id" value="">
            <fieldset>
                <legend>Request a New Interview Time</legend>
                <p class="field-hint" style="margin-bottom:1rem;">This won't change your interview until the employer confirms it.</p>
                <div class="field-group">
                    <label for="rr_scheduled_at">Proposed Date &amp; Time</label>
                    <input type="datetime-local" id="rr_scheduled_at" required>
                </div>
                <div class="field-group">
                    <label for="rr_reason">Reason (optional)</label>
                    <textarea id="rr_reason" maxlength="1000"></textarea>
                </div>
            </fieldset>
            <button type="submit" class="btn-submit">Send Request</button>
            <button type="button" class="btn-secondary" id="rr-form-cancel">Cancel</button>
        </form>

        <div id="applications" class="card-grid">
            <p class="list-card__empty">Loading your applications…</p>
        </div>
    </main>

    <script>
        const token = localStorage.getItem('internhub_token');
        const role = localStorage.getItem('internhub_role');
        if (!token) window.location.href = '{{ url('/login') }}';
        if (role && role !== 'Student') window.location.href = '{{ url('/') }}';

        const authHeaders = { Accept: 'application/json', Authorization: 'Bearer ' + token };
        const listEl = document.getElementById('applications');
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

        async function withdraw(id) {
            if (!confirm('Withdraw this application?')) return;
            try {
                const response = await fetch(`{{ url('/api/student/applications') }}/${id}/withdraw`, {
                    method: 'PATCH',
                    headers: authHeaders,
                });
                const data = await response.json();
                showAlert(data.message, response.ok ? 'success' : 'error');
                if (response.ok) load();
            } catch (err) {
                showAlert('Network error. Please try again.', 'error');
            }
        }

        function formatDateTime(value) {
            if (!value) return '';
            // The API returns the naive datetime the employer typed, serialized with a
            // trailing Z — strip it so the browser doesn't reinterpret it as UTC and
            // shift it into the viewer's local timezone.
            return new Date(value.replace('Z', '')).toLocaleString(undefined, { dateStyle: 'medium', timeStyle: 'short' });
        }

        // --- Reschedule request ----------------------------------------------------
        const rrForm = document.getElementById('reschedule-request-form');

        function openRescheduleRequestForm(interviewId) {
            document.getElementById('rr_interview_id').value = interviewId;
            rrForm.reset();
            rrForm.classList.add('open');
            rrForm.scrollIntoView({ behavior: 'smooth' });
        }

        document.getElementById('rr-form-cancel').addEventListener('click', () => {
            rrForm.classList.remove('open');
            rrForm.reset();
        });

        rrForm.addEventListener('submit', async (event) => {
            event.preventDefault();
            const interviewId = document.getElementById('rr_interview_id').value;
            const payload = {
                requested_scheduled_at: document.getElementById('rr_scheduled_at').value.replace('T', ' ') + ':00',
                reason: document.getElementById('rr_reason').value || null,
            };

            try {
                const response = await fetch(`{{ url('/api/interviews') }}/${interviewId}/reschedule-request`, {
                    method: 'POST',
                    headers: { ...authHeaders, 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload),
                });
                const data = await response.json();

                if (!response.ok) {
                    showAlert(data.errors ? Object.values(data.errors)[0][0] : data.message, 'error');
                    return;
                }

                showAlert(data.message, 'success');
                rrForm.classList.remove('open');
                rrForm.reset();
                load();
            } catch (err) {
                showAlert('Network error. Please try again.', 'error');
            }
        });

        function renderInterviews(app, container) {
            if (!container) return;

            const interviews = (app.interviews || [])
                .filter((iv) => iv.status === 'Scheduled' || iv.status === 'Rescheduled')
                .sort((a, b) => new Date(a.scheduled_at) - new Date(b.scheduled_at));

            if (!interviews.length) return;

            const iv = interviews[0];
            const isOnline = iv.mode === 'Online';
            const where = isOnline
                ? (iv.meeting_link ? `<a href="${escapeHtml(iv.meeting_link)}" target="_blank" rel="noopener noreferrer" style="color:var(--neon-chartreuse);">${escapeHtml(iv.meeting_link)}</a>` : '')
                : escapeHtml(iv.location || '');

            container.innerHTML = `
                <p class="list-card__meta"><span class="badge badge-pending">Interview ${escapeHtml(iv.status)}</span> ${escapeHtml(formatDateTime(iv.scheduled_at))}</p>
                <p class="list-card__meta">${escapeHtml(iv.mode)} · ${where}</p>
                ${iv.remarks ? `<p class="list-card__meta">Note: ${escapeHtml(iv.remarks)}</p>` : ''}
                ${iv.requested_scheduled_at
                    ? `<p class="list-card__meta"><span class="badge badge-pending">Reschedule requested</span> You proposed ${escapeHtml(formatDateTime(iv.requested_scheduled_at))} — awaiting employer confirmation.</p>`
                    : ''}
                <div class="iv-request-actions"></div>
            `;

            if (!iv.requested_scheduled_at) {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'btn-secondary';
                btn.textContent = 'Request Reschedule';
                btn.addEventListener('click', () => openRescheduleRequestForm(iv.interview_id));
                container.querySelector('.iv-request-actions').appendChild(btn);
            }
        }

        function render(items) {
            listEl.innerHTML = '';

            if (!items.length) {
                listEl.innerHTML = '<p class="list-card__empty">You have not applied to any internships yet. <a href="{{ url('/internships') }}" style="color:var(--neon-chartreuse);">Browse open internships</a>.</p>';
                return;
            }

            const badgeMap = { Pending: 'badge-pending', Accepted: 'badge-accepted', Rejected: 'badge-rejected', Withdrawn: 'badge-withdrawn' };

            items.forEach((app) => {
                const internship = app.internship || {};
                const company = internship.company ? internship.company.company_name : 'Unknown Company';
                const card = document.createElement('div');
                card.className = 'list-card';

                card.innerHTML = `
                    <h3>${escapeHtml(internship.title || 'Internship no longer available')}</h3>
                    <p class="list-card__meta">${escapeHtml(company)}</p>
                    <span class="badge ${badgeMap[app.status]}">${app.status}</span>
                    ${app.status === 'Rejected' && app.employer_feedback ? `<p class="list-card__meta">Feedback: ${escapeHtml(app.employer_feedback)}</p>` : ''}
                    <div class="interview-info"></div>
                    <div class="list-card__actions"></div>
                `;

                if (app.status === 'Pending') {
                    const actionsEl = card.querySelector('.list-card__actions');
                    const btn = document.createElement('button');
                    btn.type = 'button';
                    btn.className = 'btn-danger';
                    btn.textContent = 'Withdraw';
                    btn.addEventListener('click', () => withdraw(app.application_id));
                    actionsEl.appendChild(btn);
                }

                renderInterviews(app, card.querySelector('.interview-info'));

                listEl.appendChild(card);
            });
        }

        async function load() {
            try {
                const response = await fetch('{{ url('/api/student/applications') }}', { headers: authHeaders });
                const data = await response.json();
                render(data.data || []);
            } catch (err) {
                listEl.innerHTML = '<p class="list-card__empty">Could not load your applications.</p>';
            }
        }

        load();
    </script>
</body>
</html>
