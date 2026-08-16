<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Postings — InternHub</title>
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
        <h1 class="app-heading">My Postings</h1>
        <p class="app-subtitle">Create and manage your company's internship listings.</p>

        <div id="page-alert" class="alert"></div>

        <button type="button" class="btn-submit toggle-form-btn" id="toggle-form-btn" style="width:auto; padding:0.85rem 1.5rem;">+ New Posting</button>

        <form id="posting-form" class="auth-card wide collapsible" novalidate>
            <input type="hidden" id="internship_id" value="">
            <fieldset>
                <legend id="form-legend">New Posting</legend>
                <div class="field-group">
                    <label for="title">Title</label>
                    <input type="text" id="title" name="title" maxlength="150" required>
                </div>
                <div class="field-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" required></textarea>
                </div>
                <div class="field-group">
                    <label for="requirements">Requirements (optional)</label>
                    <textarea id="requirements" name="requirements"></textarea>
                </div>
                <div class="field-group">
                    <label for="skills_required">Required Skills (optional)</label>
                    <input type="text" id="skills_required" name="skills_required" placeholder="e.g. PHP, Laravel, SQL">
                    <p class="field-hint">Comma-separated. Used to match and recommend this posting to students.</p>
                </div>
                <div class="field-group">
                    <label for="min_cgpa">Minimum CGPA (optional)</label>
                    <input type="number" id="min_cgpa" name="min_cgpa" min="0" max="4" step="0.01">
                </div>
                <div class="field-row">
                    <div class="field-group">
                        <label for="category">Category</label>
                        <select id="category" name="category">
                            <option value="">Select a category</option>
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
                            <option value="Onsite">Onsite</option>
                            <option value="Remote">Remote</option>
                            <option value="Hybrid">Hybrid</option>
                        </select>
                    </div>
                </div>
                <div class="field-row">
                    <div class="field-group">
                        <label for="city">City</label>
                        <input type="text" id="city" name="city">
                    </div>
                    <div class="field-group">
                        <label for="state">State</label>
                        <input type="text" id="state" name="state">
                    </div>
                </div>
                <div class="field-row">
                    <div class="field-group">
                        <label for="allowance">Monthly Allowance (RM)</label>
                        <input type="number" id="allowance" name="allowance" min="0" step="0.01">
                    </div>
                    <div class="field-group">
                        <label for="duration_months">Duration (months)</label>
                        <input type="number" id="duration_months" name="duration_months" min="1" max="60">
                    </div>
                </div>
                <div class="field-row">
                    <div class="field-group">
                        <label for="vacancies">Vacancies</label>
                        <input type="number" id="vacancies" name="vacancies" min="1" value="1">
                    </div>
                    <div class="field-group">
                        <label for="application_deadline">Application Deadline</label>
                        <input type="date" id="application_deadline" name="application_deadline">
                    </div>
                </div>
            </fieldset>
            <button type="submit" class="btn-submit" id="form-submit-btn">Create Posting</button>
        </form>

        <div id="postings" class="card-grid" style="margin-top:2rem;">
            <p class="list-card__empty">Loading your postings…</p>
        </div>
    </main>

    <script>
        const token = localStorage.getItem('internhub_token');
        const role = localStorage.getItem('internhub_role');
        if (!token) window.location.href = '{{ url('/login') }}';
        if (role && role !== 'Employer') window.location.href = '{{ url('/') }}';

        const authHeaders = { Accept: 'application/json', Authorization: 'Bearer ' + token };
        const postingsEl = document.getElementById('postings');
        const pageAlert = document.getElementById('page-alert');
        const form = document.getElementById('posting-form');
        const toggleBtn = document.getElementById('toggle-form-btn');

        function escapeHtml(value) {
            const div = document.createElement('div');
            div.textContent = value ?? '';
            return div.innerHTML;
        }

        function showAlert(message, type) {
            pageAlert.textContent = message;
            pageAlert.className = 'alert show alert-' + type;
            pageAlert.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }

        toggleBtn.addEventListener('click', () => {
            resetForm();
            form.classList.toggle('open');
        });

        function resetForm() {
            document.getElementById('internship_id').value = '';
            document.getElementById('form-legend').textContent = 'New Posting';
            document.getElementById('form-submit-btn').textContent = 'Create Posting';
            form.reset();
        }

        function fieldPayload() {
            return {
                title: document.getElementById('title').value,
                description: document.getElementById('description').value,
                requirements: document.getElementById('requirements').value || null,
                skills_required: document.getElementById('skills_required').value
                    ? document.getElementById('skills_required').value.split(',').map((v) => v.trim()).filter((v) => v !== '')
                    : [],
                min_cgpa: document.getElementById('min_cgpa').value || null,
                category: document.getElementById('category').value || null,
                work_mode: document.getElementById('work_mode').value,
                city: document.getElementById('city').value || null,
                state: document.getElementById('state').value || null,
                allowance: document.getElementById('allowance').value || null,
                duration_months: document.getElementById('duration_months').value || null,
                vacancies: document.getElementById('vacancies').value || 1,
                application_deadline: document.getElementById('application_deadline').value || null,
            };
        }

        form.addEventListener('submit', async (event) => {
            event.preventDefault();
            const id = document.getElementById('internship_id').value;
            const url = id ? `{{ url('/api/employer/internships') }}/${id}` : '{{ url('/api/employer/internships') }}';
            const method = id ? 'PUT' : 'POST';

            try {
                const response = await fetch(url, {
                    method,
                    headers: { ...authHeaders, 'Content-Type': 'application/json' },
                    body: JSON.stringify(fieldPayload()),
                });
                const data = await response.json();

                if (!response.ok) {
                    showAlert(data.errors ? Object.values(data.errors)[0][0] : data.message, 'error');
                    return;
                }

                showAlert(data.message, 'success');
                form.classList.remove('open');
                resetForm();
                loadPostings();
            } catch (err) {
                showAlert('Network error. Please try again.', 'error');
            }
        });

        function editPosting(item) {
            document.getElementById('internship_id').value = item.internship_id;
            document.getElementById('form-legend').textContent = 'Edit Posting';
            document.getElementById('form-submit-btn').textContent = 'Save Changes';
            document.getElementById('title').value = item.title || '';
            document.getElementById('description').value = item.description || '';
            document.getElementById('requirements').value = item.requirements || '';
            document.getElementById('skills_required').value = Array.isArray(item.skills_required) ? item.skills_required.join(', ') : '';
            document.getElementById('min_cgpa').value = item.min_cgpa || '';
            document.getElementById('category').value = item.category || '';
            document.getElementById('work_mode').value = item.work_mode || 'Onsite';
            document.getElementById('city').value = item.city || '';
            document.getElementById('state').value = item.state || '';
            document.getElementById('allowance').value = item.allowance || '';
            document.getElementById('duration_months').value = item.duration_months || '';
            document.getElementById('vacancies').value = item.vacancies || 1;
            document.getElementById('application_deadline').value = item.application_deadline ? item.application_deadline.substring(0, 10) : '';
            form.classList.add('open');
            form.scrollIntoView({ behavior: 'smooth' });
        }

        async function doAction(url, method, confirmMsg) {
            if (confirmMsg && !confirm(confirmMsg)) return;
            try {
                const response = await fetch(url, { method, headers: authHeaders });
                const data = await response.json();
                showAlert(data.message, response.ok ? 'success' : 'error');
                if (response.ok) loadPostings();
            } catch (err) {
                showAlert('Network error. Please try again.', 'error');
            }
        }

        function renderPostings(items) {
            postingsEl.innerHTML = '';

            if (!items.length) {
                postingsEl.innerHTML = '<p class="list-card__empty">You have not created any postings yet.</p>';
                return;
            }

            items.forEach((item) => {
                const card = document.createElement('div');
                card.className = 'list-card';

                const badgeMap = { Draft: 'badge-draft', Published: 'badge-published', Closed: 'badge-closed' };
                let actions = `<button type="button" class="btn-secondary" data-action="edit">Edit</button>`;

                if (item.status === 'Draft') {
                    actions += `<button type="button" class="btn-secondary" data-action="publish">Publish</button>`;
                    actions += `<button type="button" class="btn-danger" data-action="delete">Delete</button>`;
                } else if (item.status === 'Published') {
                    actions += `<button type="button" class="btn-secondary" data-action="close">Close</button>`;
                    actions += `<a class="btn-secondary card-link" style="text-decoration:none; display:inline-block;" href="{{ url('/employer/internships') }}/${item.internship_id}/applications">View Applicants</a>`;
                } else if (item.status === 'Closed') {
                    actions += `<button type="button" class="btn-secondary" data-action="publish">Reopen</button>`;
                    actions += `<a class="btn-secondary card-link" style="text-decoration:none; display:inline-block;" href="{{ url('/employer/internships') }}/${item.internship_id}/applications">View Applicants</a>`;
                }

                card.innerHTML = `
                    <h3>${escapeHtml(item.title)}</h3>
                    <span class="badge ${badgeMap[item.status]}">${item.status}</span>
                    <p class="list-card__meta">${escapeHtml(item.city || '')} ${escapeHtml(item.work_mode)} · Vacancies: ${item.vacancies}</p>
                    <p class="list-card__meta">${item.applications_count} application(s) · ${item.accepted_applications_count} accepted</p>
                    <div class="list-card__actions"></div>
                `;

                const actionsEl = card.querySelector('.list-card__actions');
                actionsEl.innerHTML = actions;

                actionsEl.querySelector('[data-action="edit"]').addEventListener('click', () => editPosting(item));

                const publishBtn = actionsEl.querySelector('[data-action="publish"]');
                if (publishBtn) publishBtn.addEventListener('click', () => doAction(`{{ url('/api/employer/internships') }}/${item.internship_id}/publish`, 'PATCH'));

                const closeBtn = actionsEl.querySelector('[data-action="close"]');
                if (closeBtn) closeBtn.addEventListener('click', () => doAction(`{{ url('/api/employer/internships') }}/${item.internship_id}/close`, 'PATCH', 'Close this posting? Students will no longer be able to apply.'));

                const deleteBtn = actionsEl.querySelector('[data-action="delete"]');
                if (deleteBtn) deleteBtn.addEventListener('click', () => doAction(`{{ url('/api/employer/internships') }}/${item.internship_id}`, 'DELETE', 'Delete this draft posting permanently?'));

                postingsEl.appendChild(card);
            });
        }

        async function loadPostings() {
            try {
                const response = await fetch('{{ url('/api/employer/internships') }}', { headers: authHeaders });
                const data = await response.json();
                renderPostings(data.data || []);
            } catch (err) {
                postingsEl.innerHTML = '<p class="list-card__empty">Could not load your postings.</p>';
            }
        }

        loadPostings();
    </script>
</body>
</html>
