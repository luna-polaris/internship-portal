<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Applicants — InternHub</title>
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
        <a href="{{ url('/employer/internships') }}" class="back-home">&larr; My Postings</a>
    </header>

    <main class="app-main">
        <h1 class="app-heading" id="page-heading">Applicants</h1>
        <p class="app-subtitle">Review and respond to applications for this posting.</p>

        <div id="page-alert" class="alert"></div>

        <form id="interview-form" class="auth-card wide collapsible" novalidate>
            <input type="hidden" id="iv_application_id" value="">
            <input type="hidden" id="iv_interview_id" value="">
            <fieldset>
                <legend id="iv-form-legend">Schedule Interview</legend>
                <div class="field-row">
                    <div class="field-group">
                        <label for="iv_scheduled_at">Date &amp; Time</label>
                        <input type="datetime-local" id="iv_scheduled_at" required>
                    </div>
                    <div class="field-group">
                        <label for="iv_mode">Mode</label>
                        <select id="iv_mode">
                            <option value="Onsite">Onsite</option>
                            <option value="Online">Online</option>
                        </select>
                    </div>
                </div>
                <div class="field-group" id="iv_location_group">
                    <label for="iv_location">Venue</label>
                    <input type="text" id="iv_location" maxlength="255" placeholder="e.g. Level 5, Menara ABC">
                </div>
                <div class="field-group" id="iv_link_group" style="display:none;">
                    <label for="iv_meeting_link">Meeting Link</label>
                    <input type="url" id="iv_meeting_link" maxlength="255" placeholder="https://...">
                </div>
                <div class="field-group">
                    <label for="iv_remarks">Remarks (optional)</label>
                    <textarea id="iv_remarks" maxlength="2000"></textarea>
                </div>
            </fieldset>
            <button type="submit" class="btn-submit" id="iv-form-submit">Schedule Interview</button>
            <button type="button" class="btn-secondary" id="iv-form-cancel">Cancel</button>
        </form>

        <div id="applications" class="card-grid">
            <p class="list-card__empty">Loading applicants…</p>
        </div>
    </main>

    <script>
        const internshipId = {{ (int) $internship }};
        const token = localStorage.getItem('internhub_token');
        const role = localStorage.getItem('internhub_role');
        if (!token) window.location.href = '{{ url('/login') }}';
        if (role && role !== 'Employer') window.location.href = '{{ url('/') }}';

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

        // --- Interview scheduling ------------------------------------------------
        const ivForm = document.getElementById('interview-form');
        const ivMode = document.getElementById('iv_mode');
        const ivLocationGroup = document.getElementById('iv_location_group');
        const ivLinkGroup = document.getElementById('iv_link_group');

        ivMode.addEventListener('change', () => {
            const isOnline = ivMode.value === 'Online';
            ivLocationGroup.style.display = isOnline ? 'none' : 'block';
            ivLinkGroup.style.display = isOnline ? 'block' : 'none';
        });

        function openScheduleForm(applicationId) {
            document.getElementById('iv_application_id').value = applicationId;
            document.getElementById('iv_interview_id').value = '';
            document.getElementById('iv-form-legend').textContent = 'Schedule Interview';
            document.getElementById('iv-form-submit').textContent = 'Schedule Interview';
            ivForm.reset();
            ivMode.dispatchEvent(new Event('change'));
            ivForm.classList.add('open');
            ivForm.scrollIntoView({ behavior: 'smooth' });
        }

        function openRescheduleForm(interview) {
            document.getElementById('iv_application_id').value = interview.application_id;
            document.getElementById('iv_interview_id').value = interview.interview_id;
            document.getElementById('iv-form-legend').textContent = 'Reschedule Interview';
            document.getElementById('iv-form-submit').textContent = 'Save Changes';
            document.getElementById('iv_scheduled_at').value = interview.scheduled_at ? interview.scheduled_at.slice(0, 16) : '';
            document.getElementById('iv_mode').value = interview.mode || 'Onsite';
            document.getElementById('iv_location').value = interview.location || '';
            document.getElementById('iv_meeting_link').value = interview.meeting_link || '';
            document.getElementById('iv_remarks').value = interview.remarks || '';
            ivMode.dispatchEvent(new Event('change'));
            ivForm.classList.add('open');
            ivForm.scrollIntoView({ behavior: 'smooth' });
        }

        document.getElementById('iv-form-cancel').addEventListener('click', () => {
            ivForm.classList.remove('open');
            ivForm.reset();
        });

        ivForm.addEventListener('submit', async (event) => {
            event.preventDefault();

            const applicationId = document.getElementById('iv_application_id').value;
            const interviewId = document.getElementById('iv_interview_id').value;
            const payload = {
                scheduled_at: document.getElementById('iv_scheduled_at').value.replace('T', ' ') + ':00',
                mode: ivMode.value,
                location: ivMode.value === 'Onsite' ? document.getElementById('iv_location').value : null,
                meeting_link: ivMode.value === 'Online' ? document.getElementById('iv_meeting_link').value : null,
                remarks: document.getElementById('iv_remarks').value || null,
            };

            const url = interviewId
                ? `{{ url('/api/interviews') }}/${interviewId}/reschedule`
                : `{{ url('/api/applications') }}/${applicationId}/interviews`;
            const method = interviewId ? 'PATCH' : 'POST';

            try {
                const response = await fetch(url, {
                    method,
                    headers: { ...authHeaders, 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload),
                });
                const data = await response.json();

                if (!response.ok) {
                    showAlert(data.errors ? Object.values(data.errors)[0][0] : data.message, 'error');
                    return;
                }

                showAlert(data.message, 'success');
                ivForm.classList.remove('open');
                ivForm.reset();
                load();
            } catch (err) {
                showAlert('Network error. Please try again.', 'error');
            }
        });

        async function cancelInterview(interviewId) {
            const reason = prompt('Reason for cancelling (optional):', '');
            if (reason === null) return;
            try {
                const response = await fetch(`{{ url('/api/interviews') }}/${interviewId}/cancel`, {
                    method: 'PATCH',
                    headers: { ...authHeaders, 'Content-Type': 'application/json' },
                    body: JSON.stringify({ reason: reason || null }),
                });
                const data = await response.json();
                showAlert(data.message, response.ok ? 'success' : 'error');
                if (response.ok) load();
            } catch (err) {
                showAlert('Network error. Please try again.', 'error');
            }
        }

        async function completeInterview(interviewId) {
            if (!confirm('Mark this interview as completed?')) return;
            try {
                const response = await fetch(`{{ url('/api/interviews') }}/${interviewId}/complete`, {
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

        async function approveRescheduleRequest(interviewId) {
            if (!confirm('Approve the student\'s proposed time? This will update the interview schedule.')) return;
            try {
                const response = await fetch(`{{ url('/api/interviews') }}/${interviewId}/reschedule-request/approve`, {
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

        async function declineRescheduleRequest(interviewId) {
            const reason = prompt('Reason for declining (optional):', '');
            if (reason === null) return;
            try {
                const response = await fetch(`{{ url('/api/interviews') }}/${interviewId}/reschedule-request/decline`, {
                    method: 'PATCH',
                    headers: { ...authHeaders, 'Content-Type': 'application/json' },
                    body: JSON.stringify({ reason: reason || null }),
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

        function renderInterviews(app, container) {
            const interviews = (app.interviews || []).slice().sort((a, b) => new Date(b.scheduled_at) - new Date(a.scheduled_at));

            if (!interviews.length) {
                if (['Pending', 'Accepted'].includes(app.status)) {
                    const btn = document.createElement('button');
                    btn.type = 'button';
                    btn.className = 'btn-secondary';
                    btn.textContent = 'Schedule Interview';
                    btn.addEventListener('click', () => openScheduleForm(app.application_id));
                    container.appendChild(btn);
                }
                return;
            }

            const statusBadge = { Scheduled: 'badge-pending', Rescheduled: 'badge-pending', Completed: 'badge-accepted', Cancelled: 'badge-rejected' };

            interviews.forEach((iv) => {
                const isOnline = iv.mode === 'Online';
                const where = isOnline
                    ? (iv.meeting_link ? `<a href="${escapeHtml(iv.meeting_link)}" target="_blank" rel="noopener noreferrer" style="color:var(--neon-chartreuse);">${escapeHtml(iv.meeting_link)}</a>` : '')
                    : escapeHtml(iv.location || '');

                const box = document.createElement('div');
                box.style.cssText = 'margin-top:0.75rem; padding-top:0.75rem; border-top:1px solid var(--steel);';
                box.innerHTML = `
                    <p class="list-card__meta"><span class="badge ${statusBadge[iv.status] || ''}">${escapeHtml(iv.status)}</span> Interview — ${escapeHtml(formatDateTime(iv.scheduled_at))}</p>
                    <p class="list-card__meta">${escapeHtml(iv.mode)} · ${where}</p>
                    ${iv.status === 'Cancelled' && iv.cancelled_reason ? `<p class="list-card__meta">Reason: ${escapeHtml(iv.cancelled_reason)}</p>` : ''}
                    ${iv.requested_scheduled_at
                        ? `<p class="list-card__meta"><span class="badge badge-pending">Reschedule requested</span> Student proposed ${escapeHtml(formatDateTime(iv.requested_scheduled_at))}${iv.requested_reason ? ' — ' + escapeHtml(iv.requested_reason) : ''}</p>`
                        : ''}
                    <div class="iv-actions"></div>
                `;

                if (['Scheduled', 'Rescheduled'].includes(iv.status)) {
                    const actions = box.querySelector('.iv-actions');

                    if (iv.requested_scheduled_at) {
                        const approveBtn = document.createElement('button');
                        approveBtn.type = 'button';
                        approveBtn.className = 'btn-secondary';
                        approveBtn.textContent = 'Approve Request';
                        approveBtn.addEventListener('click', () => approveRescheduleRequest(iv.interview_id));

                        const declineBtn = document.createElement('button');
                        declineBtn.type = 'button';
                        declineBtn.className = 'btn-danger';
                        declineBtn.textContent = 'Decline Request';
                        declineBtn.addEventListener('click', () => declineRescheduleRequest(iv.interview_id));

                        actions.append(approveBtn, declineBtn);
                    }

                    const rescheduleBtn = document.createElement('button');
                    rescheduleBtn.type = 'button';
                    rescheduleBtn.className = 'btn-secondary';
                    rescheduleBtn.textContent = 'Reschedule';
                    rescheduleBtn.addEventListener('click', () => openRescheduleForm(iv));

                    const completeBtn = document.createElement('button');
                    completeBtn.type = 'button';
                    completeBtn.className = 'btn-secondary';
                    completeBtn.textContent = 'Mark Completed';
                    completeBtn.addEventListener('click', () => completeInterview(iv.interview_id));

                    const cancelBtn = document.createElement('button');
                    cancelBtn.type = 'button';
                    cancelBtn.className = 'btn-danger';
                    cancelBtn.textContent = 'Cancel';
                    cancelBtn.addEventListener('click', () => cancelInterview(iv.interview_id));

                    actions.append(rescheduleBtn, completeBtn, cancelBtn);
                }

                container.appendChild(box);
            });
        }

        async function accept(id) {
            try {
                const response = await fetch(`{{ url('/api/employer/applications') }}/${id}/accept`, {
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

        async function reject(id, feedback) {
            try {
                const response = await fetch(`{{ url('/api/employer/applications') }}/${id}/reject`, {
                    method: 'PATCH',
                    headers: { ...authHeaders, 'Content-Type': 'application/json' },
                    body: JSON.stringify({ feedback: feedback || null }),
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
                listEl.innerHTML = '<p class="list-card__empty">No one has applied to this posting yet.</p>';
                return;
            }

            const badgeMap = { Pending: 'badge-pending', Accepted: 'badge-accepted', Rejected: 'badge-rejected', Withdrawn: 'badge-withdrawn' };

            items.forEach((app) => {
                const student = app.student || {};
                const user = student.user || {};
                const resumeUrl = student.resume ? `{{ url('/storage') }}/${student.resume}` : null;

                const card = document.createElement('div');
                card.className = 'list-card';

                card.innerHTML = `
                    <h3>${escapeHtml(user.full_name || 'Unknown Student')}</h3>
                    <p class="list-card__meta">Matric No: ${escapeHtml(student.matric_no || '—')}</p>
                    <span class="badge ${badgeMap[app.status]}">${app.status}</span>
                    ${app.cover_letter ? `<p class="list-card__meta" style="white-space:pre-wrap;">"${escapeHtml(app.cover_letter)}"</p>` : ''}
                    ${resumeUrl ? `<p class="list-card__meta"><a href="${resumeUrl}" target="_blank" style="color:var(--neon-chartreuse);">View Resume</a></p>` : '<p class="list-card__meta">No resume on file.</p>'}
                    <div class="list-card__actions"></div>
                `;

                if (app.status === 'Pending') {
                    const actionsEl = card.querySelector('.list-card__actions');

                    const acceptBtn = document.createElement('button');
                    acceptBtn.type = 'button';
                    acceptBtn.className = 'btn-secondary';
                    acceptBtn.textContent = 'Accept';
                    acceptBtn.addEventListener('click', () => accept(app.application_id));

                    const rejectBtn = document.createElement('button');
                    rejectBtn.type = 'button';
                    rejectBtn.className = 'btn-danger';
                    rejectBtn.textContent = 'Reject';
                    rejectBtn.addEventListener('click', () => {
                        const feedback = prompt('Optional feedback for the student (leave blank to skip):', '');
                        if (feedback !== null) reject(app.application_id, feedback);
                    });

                    actionsEl.append(acceptBtn, rejectBtn);
                }

                renderInterviews(app, card);

                listEl.appendChild(card);
            });
        }

        async function load() {
            try {
                const response = await fetch(`{{ url('/api/employer/internships') }}/${internshipId}/applications`, { headers: authHeaders });
                const data = await response.json();

                if (!response.ok) {
                    listEl.innerHTML = `<p class="list-card__empty">${escapeHtml(data.message)}</p>`;
                    return;
                }

                render(data.data || []);
            } catch (err) {
                listEl.innerHTML = '<p class="list-card__empty">Could not load applicants.</p>';
            }
        }

        load();
    </script>
</body>
</html>
