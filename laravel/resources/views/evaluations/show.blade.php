{{-- Access is enforced by the API: students only see approved evaluations, employers only their own, admins see everything. --}}
@extends('layouts.app')

@section('title', 'Evaluation — InternHub')
@section('page_title', 'Evaluation')
@section('page_subtitle', 'Loading…')

@section('content')
  <div id="detail"></div>
@endsection

@push('scripts')
<script>
(async function () {
  if (!requireRole([])) return;

  const detailEl = document.getElementById('detail');
  const subEl = document.querySelector('.eval-lede');

  /* Works for both /evaluations/{id} and /admin/evaluations/{id}. */
  const id = window.location.pathname.split('/').filter(Boolean).pop();

  const esc = function (v) { return (v === null || v === undefined || v === '') ? '—' : String(v); };

  try {
    const res = await api('/api/evaluations/' + id);
    const e = res.data;

    // Draft/Rejected evaluations still belong to the employer — send them to the form instead of a read-only summary.
    const editable = (e.status === 'Draft' || e.status === 'Rejected');
    if (editable && userRole() === 'Employer') {
      window.location.replace('/evaluations/create?id=' + e.evaluation_id);
      return;
    }

    const student = (e.student && e.student.user) ? e.student.user.full_name : 'Student';
    const company = (e.employer && e.employer.company) ? e.employer.company.company_name : 'Employer';
    subEl.textContent = student + ' · ' + e.period + ' · ' + company;

    let rows = '';
    e.scores.forEach(function (s) {
      const width = Math.round((s.score / s.criterion.max_score) * 100);
      rows += '<tr><td>' + s.criterion.name + '</td>' +
        '<td>' + s.score + ' / ' + s.criterion.max_score + '</td>' +
        '<td><div class="eval-bar"><span style="width:' + width + '%"></span></div></td>' +
        '<td>' + s.criterion.weight + '%</td>' +
        '<td>' + esc(s.remark) + '</td></tr>';
    });

    const reviewer = e.reviewer ? e.reviewer.full_name : null;
    let reviewNote = '';
    if (e.review_remark || reviewer) {
      reviewNote = '<div class="eval-card"><h2>Review</h2>' +
        (reviewer ? '<p class="eval-note"><strong>Reviewed by.</strong> ' + reviewer +
          (e.reviewed_at ? ' on ' + new Date(e.reviewed_at).toLocaleDateString() : '') + '</p>' : '') +
        (e.review_remark ? '<p class="eval-note"><strong>Note.</strong> ' + e.review_remark + '</p>' : '') +
        '</div>';
    }

    detailEl.innerHTML =
      '<div class="eval-grid">' +
        '<div class="eval-card"><div class="eval-stat-label">Weighted score</div>' +
          '<div class="eval-stat">' + (e.total_score !== null ? e.total_score + '%' : '—') + '</div></div>' +
        '<div class="eval-card"><div class="eval-stat-label">Status</div>' +
          '<div style="margin-top:.5rem;"><span class="eval-pill eval-pill-' + e.status + '">' + e.status + '</span></div></div>' +
        '<div class="eval-card"><div class="eval-stat-label">Submitted</div>' +
          '<div class="eval-note" style="margin-top:.7rem;">' +
          (e.submitted_at ? new Date(e.submitted_at).toLocaleDateString() : 'Not submitted yet') + '</div></div>' +
      '</div>' +
      '<div class="eval-card"><h2>Scores</h2>' +
        '<table><thead><tr><th>Criterion</th><th>Score</th><th>Level</th><th>Weight</th><th>Note</th></tr></thead>' +
        '<tbody>' + rows + '</tbody></table></div>' +
      '<div class="eval-card"><h2>Written Feedback</h2>' +
        '<p class="eval-note"><strong>Strengths.</strong> ' + esc(e.strengths) + '</p>' +
        '<p class="eval-note"><strong>To work on.</strong> ' + esc(e.improvements) + '</p>' +
        '<p class="eval-note"><strong>Overall.</strong> ' + esc(e.overall_comment) + '</p></div>' +
      reviewNote;
  } catch (err) {
    subEl.textContent = '';
    detailEl.innerHTML = '<div class="eval-card"><p class="eval-empty">' +
      (err.status === 403
        ? 'You do not have access to this evaluation.'
        : 'Could not load this evaluation. ' + err.message) +
      ' <a href="{{ url('/evaluations') }}">Back to the list</a>.</p></div>';
  }
})();
</script>
@endpush