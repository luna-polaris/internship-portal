<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Profile — InternHub</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Anton&family=Space+Mono:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('stylesheet/stylePublic.css') }}">
    <link rel="stylesheet" href="{{ asset('stylesheet/styleAuth.css') }}">
</head>
<body class="auth-body">

    <header class="auth-header">
        <a href="{{ url('/') }}" class="logo">Intern<span>—</span>Hub</a>
        <a href="{{ url('/') }}" class="back-home">&larr; Back to Home</a>
    </header>

    <main class="auth-main">
        <div class="auth-card wide">
            <h1>Manage Profile</h1>
            <p class="auth-subtitle">Update your profile picture, full name, email, or password.</p>

            <div id="page-alert" class="alert"></div>

            <fieldset>
                <legend>Profile Picture</legend>
                <div style="display:flex; align-items:center; gap:24px; margin-bottom:1rem;">
                    <div id="avatar-preview" style="width:80px; height:80px; border-radius:50%; background:var(--steel); background-size:cover; background-position:center; display:flex; align-items:center; justify-content:center; font-family:var(--font-spine); font-size:1.8rem; color:var(--off-white); flex-shrink:0;"></div>
                    <div style="flex:1;">
                        <input type="file" id="avatar-input" accept="image/png,image/jpeg,image/webp">
                        <p class="field-hint">JPG, PNG, or WEBP. Max 2MB.</p>
                    </div>
                </div>
                <button type="button" class="btn-submit" id="avatar-submit">Upload Picture</button>
            </fieldset>

            <fieldset id="resume-section" style="display:none;">
                <legend>Resume</legend>
                <p id="resume-current" class="field-hint" style="margin-bottom:0.75rem;"></p>
                <input type="file" id="resume-input" accept=".pdf,.doc,.docx">
                <p class="field-hint">PDF, DOC, or DOCX. Max 5MB. Required before applying to internships.</p>
                <button type="button" class="btn-submit" id="resume-submit" style="margin-top:1rem;">Upload Resume</button>
            </fieldset>

            <form id="student-profile-form" novalidate style="display:none;">
                <fieldset>
                    <legend>Academic &amp; Recommendation Preferences</legend>
                    <p class="field-hint" style="margin-bottom:1rem;">This information powers your Recommended Internships list on the Browse page.</p>
                    <div class="field-row">
                        <div class="field-group">
                            <label for="programme">Programme</label>
                            <input type="text" id="programme" name="programme" maxlength="100">
                        </div>
                        <div class="field-group">
                            <label for="faculty">Faculty</label>
                            <input type="text" id="faculty" name="faculty" maxlength="100">
                        </div>
                    </div>
                    <div class="field-row">
                        <div class="field-group">
                            <label for="cgpa">CGPA</label>
                            <input type="number" id="cgpa" name="cgpa" min="0" max="4" step="0.01">
                        </div>
                        <div class="field-group">
                            <label for="graduation_year">Graduation Year</label>
                            <input type="number" id="graduation_year" name="graduation_year" min="2000" max="2100">
                        </div>
                    </div>
                    <div class="field-group">
                        <label for="skills">Skills</label>
                        <input type="text" id="skills" name="skills" placeholder="e.g. PHP, Laravel, SQL">
                        <p class="field-hint">Comma-separated.</p>
                    </div>
                    <div class="field-group">
                        <label for="interests">Interests</label>
                        <input type="text" id="interests" name="interests" placeholder="e.g. Web Development, Finance">
                        <p class="field-hint">Comma-separated.</p>
                    </div>
                    <div class="field-group">
                        <label for="preferred_locations">Preferred Locations</label>
                        <input type="text" id="preferred_locations" name="preferred_locations" placeholder="e.g. Kuala Lumpur, Selangor">
                        <p class="field-hint">Comma-separated.</p>
                    </div>
                </fieldset>
                <button type="submit" class="btn-submit">Save Preferences</button>
            </form>

            <form id="fullname-form" novalidate>
                <fieldset>
                    <legend>Full Name</legend>
                    <div class="field-group">
                        <label for="full_name">Full Name</label>
                        <input type="text" id="full_name" name="full_name" maxlength="100" required>
                    </div>
                </fieldset>
                <button type="submit" class="btn-submit">Save Full Name</button>
            </form>

            <form id="email-form" novalidate>
                <fieldset>
                    <legend>Email Address</legend>
                    <div class="field-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    <div class="field-group">
                        <label for="email_current_password">Current Password</label>
                        <input type="password" id="email_current_password" name="current_password" required>
                        <p class="field-hint">Required to confirm this change.</p>
                    </div>
                </fieldset>
                <button type="submit" class="btn-submit">Save Email</button>
            </form>

            <form id="password-form" novalidate>
                <fieldset>
                    <legend>Change Password</legend>

                    <div class="field-group">
                        <label for="old_password">Current Password</label>
                        <input type="password" id="old_password" name="old_password" required>
                        <p class="field-hint">Required &mdash; confirms it&rsquo;s really you before the password is changed.</p>
                    </div>

                    <div class="field-row">
                        <div class="field-group">
                            <label for="new_password">New Password</label>
                            <input type="password" id="new_password" name="new_password" required minlength="12">
                        </div>
                        <div class="field-group">
                            <label for="new_password_confirmation">Confirm New Password</label>
                            <input type="password" id="new_password_confirmation" name="new_password_confirmation" required minlength="12">
                        </div>
                    </div>

                    <p class="field-hint">
                        New password must be at least 12 characters and include an uppercase
                        letter, a lowercase letter, a number, and a special character.
                    </p>
                </fieldset>
                <button type="submit" class="btn-submit">Change Password</button>
            </form>
        </div>
    </main>

    <script>
        const token = localStorage.getItem('internhub_token');
        if (!token) {
            window.location.href = '{{ url('/login') }}';
        }

        const authHeaders = { Accept: 'application/json', Authorization: 'Bearer ' + token };
        const pageAlert = document.getElementById('page-alert');

        function showAlert(message, type) {
            pageAlert.textContent = message;
            pageAlert.className = 'alert show alert-' + type;
            pageAlert.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }

        function applyUser(profile) {
            const user = profile.user;
            document.getElementById('full_name').value = user.full_name || '';
            document.getElementById('email').value = user.email || '';

            const preview = document.getElementById('avatar-preview');
            const displayName = user.full_name || '?';
            if (user.profile_picture_url) {
                preview.style.backgroundImage = `url(${user.profile_picture_url})`;
                preview.textContent = '';
            } else {
                preview.style.backgroundImage = 'none';
                preview.textContent = displayName.trim().charAt(0).toUpperCase();
            }

            if (localStorage.getItem('internhub_role') === 'Student') {
                document.getElementById('resume-section').style.display = 'block';
                const resumeCurrent = document.getElementById('resume-current');
                resumeCurrent.innerHTML = profile.resume
                    ? `Current resume: <a href="{{ url('/storage') }}/${profile.resume}" target="_blank" style="color:var(--neon-chartreuse);">View uploaded resume</a>`
                    : 'No resume uploaded yet.';
            }
        }

        function listToText(values) {
            return Array.isArray(values) ? values.join(', ') : '';
        }

        function textToList(value) {
            return value.split(',').map((v) => v.trim()).filter((v) => v !== '');
        }

        function applyStudentProfile(student) {
            document.getElementById('student-profile-form').style.display = 'block';
            document.getElementById('programme').value = student.programme || '';
            document.getElementById('faculty').value = student.faculty || '';
            document.getElementById('cgpa').value = student.cgpa ?? '';
            document.getElementById('graduation_year').value = student.graduation_year || '';
            document.getElementById('skills').value = listToText(student.skills);
            document.getElementById('interests').value = listToText(student.interests);
            document.getElementById('preferred_locations').value = listToText(student.preferred_locations);
        }

        fetch('{{ url('/api/me') }}', { headers: authHeaders })
            .then((res) => (res.ok ? res.json() : Promise.reject()))
            .then((json) => applyUser(json.data))
            .catch(() => showAlert('Could not load your profile. Try logging in again.', 'error'));

        if (localStorage.getItem('internhub_role') === 'Student') {
            fetch('{{ url('/api/student/profile') }}', { headers: authHeaders })
                .then((res) => (res.ok ? res.json() : Promise.reject()))
                .then((json) => applyStudentProfile(json.data))
                .catch(() => {});
        }

        async function submitJson(url, method, body) {
            const response = await fetch(url, {
                method,
                headers: { ...authHeaders, 'Content-Type': 'application/json' },
                body: JSON.stringify(body),
            });
            const data = await response.json();
            return { ok: response.ok, data };
        }

        document.getElementById('fullname-form').addEventListener('submit', async (event) => {
            event.preventDefault();
            const { ok, data } = await submitJson('{{ url('/api/me') }}', 'PUT', {
                full_name: document.getElementById('full_name').value,
            });
            showAlert(ok ? data.message : (data.errors ? Object.values(data.errors)[0][0] : data.message), ok ? 'success' : 'error');
        });

        document.getElementById('student-profile-form').addEventListener('submit', async (event) => {
            event.preventDefault();
            const { ok, data } = await submitJson('{{ url('/api/student/profile') }}', 'PUT', {
                programme: document.getElementById('programme').value || null,
                faculty: document.getElementById('faculty').value || null,
                cgpa: document.getElementById('cgpa').value || null,
                graduation_year: document.getElementById('graduation_year').value || null,
                skills: textToList(document.getElementById('skills').value),
                interests: textToList(document.getElementById('interests').value),
                preferred_locations: textToList(document.getElementById('preferred_locations').value),
            });
            showAlert(ok ? data.message : (data.errors ? Object.values(data.errors)[0][0] : data.message), ok ? 'success' : 'error');
        });

        document.getElementById('email-form').addEventListener('submit', async (event) => {
            event.preventDefault();
            const { ok, data } = await submitJson('{{ url('/api/me/email') }}', 'PUT', {
                email: document.getElementById('email').value,
                current_password: document.getElementById('email_current_password').value,
            });
            showAlert(ok ? data.message : (data.errors ? Object.values(data.errors)[0][0] : data.message), ok ? 'success' : 'error');
            if (ok) document.getElementById('email_current_password').value = '';
        });

        document.getElementById('password-form').addEventListener('submit', async (event) => {
            event.preventDefault();
            const { ok, data } = await submitJson('{{ url('/api/password/change') }}', 'POST', {
                old_password: document.getElementById('old_password').value,
                new_password: document.getElementById('new_password').value,
                new_password_confirmation: document.getElementById('new_password_confirmation').value,
            });
            showAlert(ok ? data.message : (data.errors ? Object.values(data.errors)[0][0] : data.message), ok ? 'success' : 'error');
            if (ok) document.getElementById('password-form').reset();
        });

        document.getElementById('avatar-submit').addEventListener('click', async () => {
            const fileInput = document.getElementById('avatar-input');
            if (!fileInput.files.length) {
                showAlert('Choose an image file first.', 'error');
                return;
            }

            const formData = new FormData();
            formData.append('avatar', fileInput.files[0]);

            try {
                const response = await fetch('{{ url('/api/me/avatar') }}', {
                    method: 'POST',
                    headers: authHeaders,
                    body: formData,
                });
                const data = await response.json();

                if (!response.ok) {
                    showAlert(data.errors ? Object.values(data.errors)[0][0] : data.message, 'error');
                    return;
                }

                const preview = document.getElementById('avatar-preview');
                preview.style.backgroundImage = `url(${data.profile_picture_url})`;
                preview.textContent = '';
                showAlert(data.message, 'success');
            } catch (err) {
                showAlert('Network error. Please try again.', 'error');
            }
        });

        document.getElementById('resume-submit').addEventListener('click', async () => {
            const fileInput = document.getElementById('resume-input');
            if (!fileInput.files.length) {
                showAlert('Choose a resume file first.', 'error');
                return;
            }

            const formData = new FormData();
            formData.append('resume', fileInput.files[0]);

            try {
                const response = await fetch('{{ url('/api/student/resume') }}', {
                    method: 'POST',
                    headers: authHeaders,
                    body: formData,
                });
                const data = await response.json();

                if (!response.ok) {
                    showAlert(data.errors ? Object.values(data.errors)[0][0] : data.message, 'error');
                    return;
                }

                document.getElementById('resume-current').innerHTML = `Current resume: <a href="{{ url('/storage') }}/${data.path}" target="_blank" style="color:var(--neon-chartreuse);">View uploaded resume</a>`;
                showAlert(data.message, 'success');
            } catch (err) {
                showAlert('Network error. Please try again.', 'error');
            }
        });
    </script>

    <script src="{{ asset('scripts/passwordToggle.js') }}"></script>
</body>
</html>
