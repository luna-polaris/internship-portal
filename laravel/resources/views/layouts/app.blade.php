{{--
  Shared shell for the Feedback & Performance Evaluation pages.
  Reuses the public stylesheets and the header markup from home.blade.php so
  these pages sit inside the same visual system as the rest of the portal.

  Pages using it provide:  @section('title')  @section('page_title')
                           @section('page_subtitle')  @section('content')
--}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'InternHub')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Anton&family=Space+Mono:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('stylesheet/stylePublic.css') }}">
    <link rel="stylesheet" href="{{ asset('stylesheet/styleExtend.css') }}">
    <link rel="stylesheet" href="{{ asset('stylesheet/styleEvaluations.css') }}">
    <link rel="stylesheet" href="{{ asset('fontawesome/css/font-awesome.min.css') }}">
</head>
<body class="eval-body">

<header class="global-header">
    <a href="{{ url('/') }}" class="logo">Intern<span>—</span>Hub</a>
    <nav class="nav">
        <a href="{{ url('/internships') }}">Browse Internships</a>
        <a href="{{ url('/notifications') }}" id="nav-alerts-link" style="display:none;">
            Alerts <span id="nav-alerts-badge" class="eval-badge" hidden>0</span>
        </a>
        <span id="nav-auth-links">
            <a href="{{ url('/login') }}">Log In</a>
            <a href="{{ url('/register') }}" class="nav__cta">Register</a>
        </span>
        <div id="nav-user-widget" class="nav-user">
            <button type="button" class="nav-user__trigger" id="nav-user-trigger">
                <span class="nav-user__avatar" id="nav-user-avatar"></span>
                <span class="nav-user__name" id="nav-user-name"></span>
            </button>
            <div class="nav-user__dropdown">
                <a href="{{ url('/employer/internships') }}" id="nav-employer-link" style="display:none;">My Postings</a>
                <a href="{{ url('/student/applications') }}" id="nav-student-link" style="display:none;">My Applications</a>
                <a href="{{ url('/student/bookmarks') }}" id="nav-student-bookmarks-link" style="display:none;">My Bookmarks</a>

                <a href="{{ url('/admin/dashboard') }}" id="nav-admin-dashboard-link" style="display:none;">Admin Dashboard</a>

                {{-- Module 3.2 — shown only to the role that can act on each page --}}
                <a href="{{ url('/evaluations/create') }}" id="nav-eval-write-link" style="display:none;">Evaluate An Intern</a>
                <a href="{{ url('/admin/evaluations') }}" id="nav-eval-review-link" style="display:none;">Review Queue</a>
                <a href="{{ url('/admin/criteria') }}" id="nav-eval-criteria-link" style="display:none;">Evaluation Criteria</a>
                <a href="{{ url('/evaluations') }}" id="nav-eval-list-link" style="display:none;">Evaluations</a>
                <a href="{{ url('/performance') }}" id="nav-eval-perf-link" style="display:none;">Performance</a>

                <a href="{{ url('/profile') }}">Settings</a>
                <button type="button" id="nav-logout-btn">Log Out</button>
            </div>
        </div>
    </nav>
</header>

<main class="eval-main">
    <header class="eval-pagehead">
        <h1 class="eval-title">@yield('page_title')</h1>
        <p class="eval-lede">@yield('page_subtitle')</p>
    </header>

    <div id="flash" class="eval-flash"></div>

    @yield('content')
</main>

<footer class="ext-footer">
    <div class="ext-footer-links">
        <a href="{{ url('/') }}">Home</a>
        <a href="{{ url('/internships') }}">Internships</a>
        <a href="{{ url('/profile') }}">Settings</a>
    </div>
    <p>Copyright &copy; <span id="eval-year"></span> InternHub — Connecting Students &amp; Employers</p>
</footer>

<script>
document.getElementById('eval-year').textContent = new Date().getFullYear();

/* Token key matches the one login.blade.php writes. */
window.apiToken = () => localStorage.getItem('internhub_token') || '';
window.userRole = () => localStorage.getItem('internhub_role') || '';

window.api = async (url, options = {}) => {
  const res = await fetch(url, {
    ...options,
    headers: {
      'Accept': 'application/json',
      'Content-Type': 'application/json',
      'Authorization': 'Bearer ' + window.apiToken(),
      ...(options.headers || {}),
    },
  });
  const body = await res.json().catch(() => ({}));
  if (!res.ok) throw Object.assign(new Error(body.message || 'Request failed'), { status: res.status, body });
  return body;
};

window.flash = (message, ok = true) => {
  const el = document.getElementById('flash');
  el.textContent = message;
  el.className = 'eval-flash ' + (ok ? 'is-ok' : 'is-bad');
};

/* Send anyone without the right role back to the homepage rather than
   showing them buttons the API will refuse. Pages opt in via data-role. */
window.requireRole = (allowed) => {
  const role = window.userRole();
  if (!role) { window.location.href = '{{ url('/login') }}'; return false; }
  if (allowed.length && !allowed.includes(role)) {
    document.querySelector('.eval-main').innerHTML =
      '<div class="eval-card"><p class="eval-empty">This page is for ' +
      allowed.join(' and ') + ' accounts. <a href="{{ url('/') }}">Back to the homepage</a>.</p></div>';
    return false;
  }
  return true;
};

/* Nav: swap Login/Register for the signed-in user, reveal role-specific links. */
(function () {
  const token = localStorage.getItem('internhub_token');
  if (!token) return;

  const show = (id) => { const el = document.getElementById(id); if (el) el.style.display = 'block'; };

  fetch('{{ url('/api/me') }}', {
    headers: { Accept: 'application/json', Authorization: 'Bearer ' + token },
  })
    .then((res) => (res.ok ? res.json() : Promise.reject()))
    .then((json) => {
      const user = json.data.user;
      document.getElementById('nav-auth-links').classList.add('is-hidden');
      document.getElementById('nav-user-widget').classList.add('is-visible');
      document.getElementById('nav-user-name').textContent = user.full_name;
      document.getElementById('nav-alerts-link').style.display = 'inline-block';

      const role = localStorage.getItem('internhub_role');
      if (role === 'Employer') {
        show('nav-employer-link'); show('nav-eval-write-link');
        show('nav-eval-list-link'); show('nav-eval-perf-link');
      } else if (role === 'Student') {
        show('nav-student-link'); show('nav-student-bookmarks-link');
        show('nav-eval-list-link'); show('nav-eval-perf-link');
      } else if (role === 'Admin') {
        show('nav-admin-dashboard-link');
        show('nav-eval-review-link'); show('nav-eval-criteria-link');
        show('nav-eval-list-link'); show('nav-eval-perf-link');
      }

      const avatarEl = document.getElementById('nav-user-avatar');
      if (user.profile_picture_url) {
        avatarEl.style.backgroundImage = 'url(' + user.profile_picture_url + ')';
      } else {
        avatarEl.textContent = user.full_name.trim().charAt(0).toUpperCase();
      }

      return api('/api/notifications?unread=1');
    })
    .then((res) => {
      if (!res || !res.unread_count) return;
      const badge = document.getElementById('nav-alerts-badge');
      badge.textContent = res.unread_count;
      badge.hidden = false;
    })
    .catch(() => {
      localStorage.removeItem('internhub_token');
      localStorage.removeItem('internhub_role');
    });

  document.getElementById('nav-logout-btn').addEventListener('click', async () => {
    try {
      await fetch('{{ url('/api/logout') }}', {
        method: 'POST',
        headers: { Accept: 'application/json', Authorization: 'Bearer ' + token },
      });
    } catch (err) { /* clear local state regardless */ }
    localStorage.removeItem('internhub_token');
    localStorage.removeItem('internhub_role');
    window.location.href = '{{ url('/') }}';
  });
})();
</script>

@stack('scripts')
</body>
</html>