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

            <!-- Profile picture -->
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

            <!-- Full Name -->
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

            <!-- Email -->
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

            <!-- Password -->
            <form id="password-form" novalidate>
                <fieldset>
                    <legend>Change Password</legend>
                    <div class="field-row">
                        <div class="field-group">
                            <label for="old_password">Current Password</label>
                            <input type="password" id="old_password" name="old_password" required>
                        </div>
                        <div class="field-group">
                            <label for="new_password">New Password</label>
                            <input type="password" id="new_password" name="new_password" required minlength="8">
                        </div>
                    </div>
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

        function applyUser(user) {
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
        }

        fetch('{{ url('/api/me') }}', { headers: authHeaders })
            .then((res) => (res.ok ? res.json() : Promise.reject()))
            .then((json) => applyUser(json.data.user))
            .catch(() => showAlert('Could not load your profile. Try logging in again.', 'error'));

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
    </script>
</body>
</html>
