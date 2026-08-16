{{-- Admin-only overview: registration totals and every internship posting on
     the platform. Gated client-side by requireRole(['Admin']); the data itself
     is protected server-side by the role:Admin middleware on /api/admin/*. --}}
@extends('layouts.app')

@section('title', 'Admin Dashboard — InternHub')
@section('page_title', 'Admin Dashboard')
@section('page_subtitle', 'Platform totals at a glance, and every internship posted by employers.')

@section('content')

  {{-- Registration totals — these two count up from zero on load --}}
  <div class="eval-grid">
    <div class="eval-card">
      <p class="eval-stat-label">Total Students</p>
      <p class="eval-stat" data-count="total_students">0</p>
    </div>
    <div class="eval-card">
      <p class="eval-stat-label">Total Employers</p>
      <p class="eval-stat" data-count="total_employers">0</p>
    </div>
    <div class="eval-card">
      <p class="eval-stat-label">Companies</p>
      <p class="eval-stat" data-count="total_companies">0</p>
    </div>
    <div class="eval-card">
      <p class="eval-stat-label">Pending Verification</p>
      <p class="eval-stat" data-count="pending_users">0</p>
    </div>
  </div>

  {{-- Posting totals --}}
  <div class="eval-grid">
    <div class="eval-card">
      <p class="eval-stat-label">Jobs Posted (All)</p>
      <p class="eval-stat" data-count="total_internships">0</p>
    </div>
    <div class="eval-card">
      <p class="eval-stat-label">Published</p>
      <p class="eval-stat" data-count="published_internships">0</p>
    </div>
    <div class="eval-card">
      <p class="eval-stat-label">Draft</p>
      <p class="eval-stat" data-count="draft_internships">0</p>
    </div>
    <div class="eval-card">
      <p class="eval-stat-label">Closed</p>
      <p class="eval-stat" data-count="closed_internships">0</p>
    </div>
    <div class="eval-card">
      <p class="eval-stat-label">Total Vacancies</p>
      <p class="eval-stat" data-count="total_vacancies">0</p>
    </div>
  </div>

  {{-- Posting details --}}
  <div class="eval-card">
    <h2>Internship Postings</h2>

    <div class="eval-actions" style="margin-bottom:1rem;">
      <input type="search" id="job-search" placeholder="Search title or company…" style="max-width:260px;">
      <select id="job-status" style="max-width:180px;">
        <option value="">All statuses</option>
        <option value="Published">Published</option>
        <option value="Draft">Draft</option>
        <option value="Closed">Closed</option>
      </select>
    </div>

    <div id="jobs-wrap">
      <p class="eval-empty">Loading postings…</p>
    </div>

    <div class="eval-actions" id="jobs-pager" style="margin-top:1rem;" hidden>
      <button type="button" class="eval-btn eval-btn--ghost" id="jobs-prev">&larr; Previous</button>
      <span class="eval-note" id="jobs-pageinfo"></span>
      <button type="button" class="eval-btn eval-btn--ghost" id="jobs-next">Next &rarr;</button>
    </div>
  </div>

@endsection

@push('scripts')
<script>
(function () {
  if (!requireRole(['Admin'])) return;

  const headers = { Accept: 'application/json', Authorization: 'Bearer ' + apiToken() };

  /* ---------------------------------------------------------------
   * Count-up: animate 0 -> target with an ease-out so the number
   * decelerates into place instead of ticking linearly.
   * Respects prefers-reduced-motion by jumping straight to the value.
   * ------------------------------------------------------------- */
  function countUp(el, target, duration = 1400) {
    const reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    if (reduced || target === 0) {
      el.textContent = target.toLocaleString();
      return;
    }

    const start = performance.now();

    function frame(now) {
      const progress = Math.min((now - start) / duration, 1);
      const eased = 1 - Math.pow(1 - progress, 3); // easeOutCubic
      el.textContent = Math.round(eased * target).toLocaleString();

      if (progress < 1) {
        requestAnimationFrame(frame);
      } else {
        el.textContent = target.toLocaleString(); // land exactly on target
      }
    }

    requestAnimationFrame(frame);
  }

  fetch('{{ url('/api/admin/stats') }}', { headers })
    .then(r => r.ok ? r.json() : Promise.reject(r.status))
    .then(({ data }) => {
      document.querySelectorAll('[data-count]').forEach(el => {
        countUp(el, Number(data[el.dataset.count] ?? 0));
      });
    })
    .catch(() => {
      document.querySelectorAll('[data-count]').forEach(el => { el.textContent = '—'; });
      flash('Could not load dashboard totals.', false);
    });

  /* --------------------------- Postings table --------------------------- */
  const wrap = document.getElementById('jobs-wrap');
  const pager = document.getElementById('jobs-pager');
  const pageInfo = document.getElementById('jobs-pageinfo');
  let page = 1;

  const esc = (s) => String(s ?? '').replace(/[&<>"']/g, c =>
    ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));

  function loadJobs() {
    const params = new URLSearchParams({ page, per_page: 15 });
    const q = document.getElementById('job-search').value.trim();
    const status = document.getElementById('job-status').value;
    if (q) params.set('q', q);
    if (status) params.set('status', status);

    wrap.innerHTML = '<p class="eval-empty">Loading postings…</p>';
    pager.hidden = true;

    fetch('{{ url('/api/admin/internships') }}?' + params, { headers })
      .then(r => r.ok ? r.json() : Promise.reject(r.status))
      .then(({ data }) => renderJobs(data))
      .catch(() => { wrap.innerHTML = '<p class="eval-empty">Could not load postings.</p>'; });
  }

  function renderJobs(paginator) {
    const rows = paginator.data || [];

    if (!rows.length) {
      wrap.innerHTML = '<p class="eval-empty">No postings match this filter.</p>';
      return;
    }

    wrap.innerHTML = `
      <div style="overflow-x:auto;">
        <table>
          <thead>
            <tr>
              <th>Title</th><th>Company</th><th>Category</th><th>Mode</th>
              <th>Location</th><th>Allowance</th><th>Vacancies</th>
              <th>Applicants</th><th>Deadline</th><th>Status</th>
            </tr>
          </thead>
          <tbody>
            ${rows.map(job => `
              <tr>
                <td>${esc(job.title)}</td>
                <td>${esc(job.company?.company_name ?? '—')}</td>
                <td>${esc(job.category ?? '—')}</td>
                <td>${esc(job.work_mode)}</td>
                <td>${esc([job.city, job.state].filter(Boolean).join(', ') || '—')}</td>
                <td>${job.allowance ? 'RM ' + Number(job.allowance).toLocaleString() : '—'}</td>
                <td>${esc(job.vacancies)}</td>
                <td>${esc(job.applications_count ?? 0)}</td>
                <td>${job.application_deadline ? esc(String(job.application_deadline).slice(0, 10)) : '—'}</td>
                <td><span class="eval-pill eval-pill-${esc(job.status)}">${esc(job.status)}</span></td>
              </tr>`).join('')}
          </tbody>
        </table>
      </div>`;

    pager.hidden = paginator.last_page <= 1;
    pageInfo.textContent = `Page ${paginator.current_page} of ${paginator.last_page} · ${paginator.total} posting${paginator.total === 1 ? '' : 's'}`;
    document.getElementById('jobs-prev').disabled = paginator.current_page <= 1;
    document.getElementById('jobs-next').disabled = paginator.current_page >= paginator.last_page;
  }

  document.getElementById('jobs-prev').addEventListener('click', () => { page--; loadJobs(); });
  document.getElementById('jobs-next').addEventListener('click', () => { page++; loadJobs(); });
  document.getElementById('job-status').addEventListener('change', () => { page = 1; loadJobs(); });

  let searchTimer;
  document.getElementById('job-search').addEventListener('input', () => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => { page = 1; loadJobs(); }, 300);
  });

  loadJobs();
})();
</script>
@endpush
