@extends('layouts.app')

@section('title', 'Performance Dashboard — InternHub')
@section('page_title', 'Performance')
@section('page_subtitle', 'Loading your numbers...')

@section('content')
  <div id="stats" class="eval-grid"></div>
  <div id="panels"></div>
@endsection

@push('scripts')
<script>
(async function () {
  if (!requireRole([])) return;

  const statsEl = document.getElementById('stats');
  const panelsEl = document.getElementById('panels');
  const subEl = document.querySelector('.eval-lede');

  const esc = function (v) { return (v === null || v === undefined) ? '-' : String(v); };
  const pct = function (v) { return (v === null || v === undefined || v === 0) ? '-' : v + '%'; };

  const stat = function (label, value) {
    return '<div class="eval-card"><div class="eval-stat-label">' + label +
           '</div><div class="eval-stat">' + esc(value) + '</div></div>';
  };

  const panel = function (title, inner) {
    return '<div class="eval-card"><h2>' + title + '</h2>' + inner + '</div>';
  };

  const criterionRows = function (rows) {
    if (!rows || !rows.length) return '<p class="eval-empty">No scored criteria yet.</p>';
    let html = '<table><thead><tr><th>Criterion</th><th>Average</th><th>Level</th><th>Responses</th></tr></thead><tbody>';
    rows.forEach(function (r) {
      const name = r.criterion || r.name;
      const avg = (r.average !== undefined) ? r.average : r.average_score;
      const width = (r.percentage !== undefined && r.percentage !== null)
        ? r.percentage : Math.round((avg / r.max_score) * 100);
      html += '<tr><td>' + esc(name) + '</td><td>' + esc(avg) + ' / ' + esc(r.max_score) + '</td>' +
              '<td><div class="eval-bar"><span style="width:' + width + '%"></span></div></td>' +
              '<td>' + esc(r.responses) + '</td></tr>';
    });
    return html + '</tbody></table>';
  };

  try {
    const res = await api('/api/performance/dashboard');
    const role = res.role;
    const data = res.data;

    if (role === 'Student') {
      subEl.textContent = 'How your host companies rated you. Only evaluations approved by an admin appear here.';

      statsEl.innerHTML =
        stat('Evaluations received', data.evaluations_received) +
        stat('Latest score', pct(data.latest_total_score)) +
        stat('Average score', pct(data.average_total_score));

      let trend = '<p class="eval-empty">Nothing here yet. Your evaluation appears once your employer submits it and an admin approves it.</p>';
      if (data.trend && data.trend.length) {
        trend = '<table><thead><tr><th>Period</th><th>Company</th><th>Score</th><th>Received</th></tr></thead><tbody>';
        data.trend.forEach(function (t) {
          trend += '<tr><td>' + esc(t.period) + '</td><td>' + esc(t.company) + '</td><td>' +
                   pct(t.total_score) + '</td><td>' + esc(t.received_at) + '</td></tr>';
        });
        trend += '</tbody></table>';
      }

      let feedback = '';
      if (data.latest_feedback) {
        feedback = panel('Latest Written Feedback',
          '<p class="eval-note"><strong>Strengths.</strong> ' + esc(data.latest_feedback.strengths) + '</p>' +
          '<p class="eval-note"><strong>To work on.</strong> ' + esc(data.latest_feedback.improvements) + '</p>' +
          '<p class="eval-note"><strong>Overall.</strong> ' + esc(data.latest_feedback.overall_comment) + '</p>');
      }

      panelsEl.innerHTML =
        panel('Score By Criterion', criterionRows(data.by_criterion)) +
        panel('Over Time', trend) + feedback;
    }

    if (role === 'Employer') {
      subEl.textContent = 'Evaluations your company has written.';

      statsEl.innerHTML =
        stat('Written', data.total_written) +
        stat('Needs your action', data.needs_your_action) +
        stat('Awaiting admin review', data.awaiting_review) +
        stat('Average score given', pct(data.average_total_score));

      let interns = '<p class="eval-empty">You have not evaluated anyone yet. Start one from the evaluation form.</p>';
      if (data.interns && data.interns.length) {
        interns = '<table><thead><tr><th>Student</th><th>Period</th><th>Score</th><th>Status</th></tr></thead><tbody>';
        data.interns.forEach(function (i) {
          interns += '<tr><td>' + esc(i.student) + '</td><td>' + esc(i.period) + '</td><td>' +
                     pct(i.total_score) + '</td><td><span class="eval-pill eval-pill-' + i.status +
                     '">' + i.status + '</span></td></tr>';
        });
        interns += '</tbody></table>';
      }

      panelsEl.innerHTML =
        panel('Your Interns', interns) +
        panel('Average By Criterion', criterionRows(data.by_criterion));
    }

    if (role === 'Admin') {
      subEl.textContent = 'Programme-wide evaluation activity.';
      const health = data.rubric_health || {};

      statsEl.innerHTML =
        stat('Approved', data.approved_total) +
        stat('Pending review', data.pending_review) +
        stat('Sent back', data.returned_total) +
        stat('Average score', pct(data.average_total_score)) +
        stat('Active criteria', health.active_criteria);

      let warning = '';
      if (health.weights_balanced === false) {
        warning = '<div class="eval-card" style="border-color:var(--eval-warn);">' +
          '<p class="eval-note">The active criteria weights add up to ' + esc(health.active_weight_total) +
          '%, not 100%. Totals will be off until you rebalance them.</p></div>';
      }

      let criterionPanel = criterionRows(data.by_criterion);
      if (data.weakest_criterion) {
        criterionPanel += '<p class="eval-note" style="margin-top:1rem;">Lowest scoring right now: <strong>' +
          esc(data.weakest_criterion.name) + '</strong> - worth flagging to the faculty.</p>';
      }

      let top = '<p class="eval-empty">No approved evaluations yet.</p>';
      if (data.top_students && data.top_students.length) {
        top = '<table><thead><tr><th>Student</th><th>Matric no</th><th>Average</th></tr></thead><tbody>';
        data.top_students.forEach(function (s) {
          top += '<tr><td>' + esc(s.full_name) + '</td><td>' + esc(s.matric_no) + '</td><td>' +
                 pct(s.average_score) + '</td></tr>';
        });
        top += '</tbody></table>';
      }

      let monthly = '<p class="eval-empty">No submissions recorded yet.</p>';
      if (data.submissions_by_month && data.submissions_by_month.length) {
        monthly = '<table><thead><tr><th>Month</th><th>Submitted</th></tr></thead><tbody>';
        data.submissions_by_month.forEach(function (m) {
          monthly += '<tr><td>' + esc(m.month) + '</td><td>' + esc(m.total) + '</td></tr>';
        });
        monthly += '</tbody></table>';
      }

      panelsEl.innerHTML = warning +
        panel('Average By Criterion', criterionPanel) +
        panel('Highest Rated Students', top) +
        panel('Submissions By Month', monthly);
    }
  } catch (e) {
    subEl.textContent = '';
    flash('Could not load the dashboard. ' + e.message, false);
  }
})();
</script>
@endpush