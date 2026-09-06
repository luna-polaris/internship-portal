<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password — InternHub</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Anton&family=Space+Mono:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('stylesheet/stylePublic.css') }}">
    <link rel="stylesheet" href="{{ asset('stylesheet/styleAuth.css') }}">
</head>
<body class="auth-body">
    <header class="auth-header">
        <a href="{{ url('/') }}" class="logo">Intern<span>—</span>Hub</a>
        <a href="{{ url('/login') }}" class="back-home">&larr; Back to Login</a>
    </header>

    <main class="auth-main">
        <div class="auth-card">
            <h1>Reset Password</h1>
            <p class="auth-subtitle">Choose a new password for your account.</p>
            <div id="alert-box" class="alert"></div>

            <form id="reset-password-form" novalidate>
                <div class="field-group">
                    <label for="password">New Password</label>
                    <input type="password" id="password" required minlength="12" autocomplete="new-password">
                </div>
                <div class="field-group">
                    <label for="password_confirmation">Confirm New Password</label>
                    <input type="password" id="password_confirmation" required minlength="12" autocomplete="new-password">
                </div>
                <button type="submit" class="btn-submit">Reset Password</button>
            </form>
        </div>
    </main>

    <script>
        const form = document.getElementById('reset-password-form');
        const alertBox = document.getElementById('alert-box');
        const token = new URLSearchParams(window.location.search).get('token') || '';

        function showAlert(message, type) {
            alertBox.textContent = message;
            alertBox.className = 'alert show alert-' + type;
        }

        form.addEventListener('submit', async (event) => {
            event.preventDefault();

            const password = document.getElementById('password').value;
            const confirmation = document.getElementById('password_confirmation').value;

            if (!token) {
                showAlert('This reset link is missing its token.', 'error');
                return;
            }

            if (password !== confirmation) {
                showAlert('Passwords do not match.', 'error');
                return;
            }

            try {
                const response = await fetch('{{ url('/api/password/reset') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        token,
                        password,
                        password_confirmation: confirmation,
                    }),
                });

                const data = await response.json();

                if (!response.ok) {
                    showAlert(data.message || 'Unable to reset your password.', 'error');
                    return;
                }

                showAlert(data.message || 'Password reset. You can now log in.', 'success');
                form.reset();
            } catch (err) {
                showAlert('Network error. Please try again.', 'error');
            }
        });
    </script>
</body>
</html>