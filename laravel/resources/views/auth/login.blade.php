<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — InternHub</title>
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
        <div class="auth-card">
            <h1>Log In</h1>
            <p class="auth-subtitle">Access your InternHub account.</p>

            <div id="alert-box" class="alert @if(session('verified')) show alert-success @elseif(session('verification_failed')) show alert-error @endif">@if(session('verified')){{ session('verified') }}@elseif(session('verification_failed')){{ session('verification_failed') }}@endif</div>

            <div class="mode-switch" role="tablist" aria-label="Login as">
                <button type="button" id="tab-user" aria-selected="true" data-mode="user">Student / Employer</button>
                <button type="button" id="tab-admin" aria-selected="false" data-mode="admin">Admin</button>
            </div>

            <form id="user-login-form" novalidate>
                <div class="field-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="field-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <button type="submit" class="btn-submit">Log In</button>
            </form>

            <!-- Kept as a separate form/endpoint on purpose — see AuthController::adminLogin. -->
            <form id="admin-login-form" class="role-fields" novalidate>
                <div class="field-group">
                    <label for="username">Admin Username</label>
                    <input type="text" id="username" name="username" autocomplete="off">
                </div>
                <div class="field-group">
                    <label for="admin_password">Password</label>
                    <input type="password" id="admin_password" name="password">
                </div>
                <button type="submit" class="btn-submit">Admin Log In</button>
            </form>

            <p class="auth-footer-link">Don't have an account? <a href="{{ url('/register') }}">Register</a></p>
        </div>
    </main>

    <script>
        const tabs = document.querySelectorAll('.mode-switch button');
        const userForm = document.getElementById('user-login-form');
        const adminForm = document.getElementById('admin-login-form');

        tabs.forEach((tab) => {
            tab.addEventListener('click', () => {
                tabs.forEach((t) => t.setAttribute('aria-selected', 'false'));
                tab.setAttribute('aria-selected', 'true');

                const isAdmin = tab.dataset.mode === 'admin';
                userForm.classList.toggle('active', !isAdmin);
                adminForm.classList.toggle('active', isAdmin);
                userForm.style.display = isAdmin ? 'none' : 'block';
                adminForm.style.display = isAdmin ? 'block' : 'none';
            });
        });
        adminForm.style.display = 'none';

        const alertBox = document.getElementById('alert-box');

        function showAlert(message, type) {
            alertBox.textContent = message;
            alertBox.className = 'alert show alert-' + type;
        }

        async function submitLogin(url, payload, redirectTo) {
            try {
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify(payload),
                });

                const data = await response.json();

                if (!response.ok) {
                    showAlert(data.message || 'Login failed.', 'error');
                    return;
                }

                localStorage.setItem('internhub_token', data.token);
                localStorage.setItem('internhub_role', data.role);
                showAlert('Login successful. Redirecting...', 'success');
                setTimeout(() => { window.location.href = redirectTo; }, 1000);
            } catch (err) {
                showAlert('Network error. Please try again.', 'error');
            }
        }

        userForm.addEventListener('submit', (event) => {
            event.preventDefault();
            alertBox.className = 'alert';
            submitLogin('{{ url('/api/login') }}', {
                email: document.getElementById('email').value,
                password: document.getElementById('password').value,
            }, '{{ url('/') }}');
        });

        adminForm.addEventListener('submit', (event) => {
            event.preventDefault();
            alertBox.className = 'alert';
            submitLogin('{{ url('/api/admin/login') }}', {
                username: document.getElementById('username').value,
                password: document.getElementById('admin_password').value,
            }, '{{ url('/') }}');
        });
    </script>

    <script src="{{ asset('scripts/passwordToggle.js') }}"></script>
</body>
</html>
