@extends('layouts.app')

@section('title', 'Review Evaluations — InternHub')
@section('page_title', 'Review Evaluations')
@section('page_subtitle', 'Approving releases the evaluation to the student. Sending it back returns it to the employer with your note, and they can edit and resubmit.')

@section('content')
  <div class="eval-card" style="display:flex;gap:.75rem;align-items:center;flex-wrap:wrap;">
    <label for="status" style="margin:0;">Showing</label>
    <select id="status" style="width:auto;min-width:14rem;">
      <option value="Submitted" selected>Waiting For Review</option>
      <option value="Approved">Approved</option>
      <option value="Rejected">Sent Back</option>
      <option value="">All</option>
    </select>
  </div>

  <div id="list"></div>
@endsection

@push('scripts')
<script>
(function () {
  if (!requireRole(['Admin'])) return;

  const listEl = document.getElementById('list');
  const statusEl = document.getElementById('status');

  const load = async () => {
    listEl.innerHTML = '<div class="eval-card"><p class="eval-empty">Loading…</p></div>';
    try {
      const q = statusEl.value ? '?status=' + statusEl.value : '';
      const page = await api('/api/evaluations' + q);

      if (!page.data.length) {
        listEl.innerHTML = '<div class="eval-card"><p class="eval-empty">Nothing in this queue.</p></div>';
        return;
      }

      let html = '';
      page.data.forEach(function (e) {
        const student = (e.student && e.student.user) ? e.student.user.full_name : 'Student';
        const company = (e.employer && e.employer.company) ? e.employer.company.company_name : 'Employer';
        html += '<div class="eval-card">' +
          '<div style="display:flex;justify-content:space-between;gap:1rem;align-items:baseline;flex-wrap:wrap;">' +
            '<div><strong>' + student + '</strong>' +
            '<span class="eval-note"> · ' + e.period + ' · ' + company + '</span></div>' +
            '<span class="eval-pill eval-pill-' + e.status + '">' + e.status + '</span>' +
          '</div>' +
          '<p class="eval-note" style="margin:.75rem 0;">Weighted score: <strong>' +
            (e.total_score !== null ? e.total_score + '%' : '—') + '</strong></p>' +
          '<button type="button" class="eval-btn eval-btn--ghost" data-open="' + e.evaluation_id + '">View Full Evaluation</button>' +
          '<div data-detail="' + e.evaluation_id + '" hidden style="margin-top:1.25rem;"></div>' +
          '</div>';
      });
      listEl.innerHTML = html;
    } catch (err) {
      flash('Could not load the queue. ' + err.message, false);
      listEl.innerHTML = '';
    }
  };

  const openDetail = async (id) => {
    const box = document.querySelector('[data-detail="' + id + '"]');
    if (!box.hidden) { box.hidden = true; return; }
    box.hidden = false;
    box.innerHTML = '<p class="eval-empty">Loading…</p>';

    const res = await api('/api/evaluations/' + id);
    const e = res.data;

    let rows = '';
    e.scores.forEach(function (s) {
      rows += '<tr><td>' + s.criterion.name + '</td>' +
        '<td>' + s.score + ' / ' + s.criterion.max_score + '</td>' +
        '<td>' + s.criterion.weight + '%</td>' +
        '<td>' + (s.remark || '—') + '</td></tr>';
    });

    let actions = '';
    if (e.status === 'Submitted') {
      actions =
        '<label for="remark-' + id + '">Note to the employer (required to send back)</label>' +
        '<textarea id="remark-' + id + '" rows="2"></textarea>' +
        '<div class="eval-actions">' +
          '<button type="button" class="eval-btn" data-decide="approve" data-eval="' + id + '">Approve</button>' +
          '<button type="button" class="eval-btn eval-btn--ghost" data-decide="reject" data-eval="' + id + '">Send Back</button>' +
        '</div>';
    } else if (e.review_remark) {
      actions = '<p class="eval-note"><strong>Review note.</strong> ' + e.review_remark + '</p>';
    }

    box.innerHTML =
      '<table><thead><tr><th>Criterion</th><th>Score</th><th>Weight</th><th>Note</th></tr></thead>' +
      '<tbody>' + rows + '</tbody></table>' +
      '<p class="eval-note" style="margin-top:1rem;"><strong>Strengths.</strong> ' + (e.strengths || '—') + '</p>' +
      '<p class="eval-note"><strong>To work on.</strong> ' + (e.improvements || '—') + '</p>' +
      '<p class="eval-note"><strong>Overall.</strong> ' + (e.overall_comment || '—') + '</p>' +
      actions;
  };

  const decide = async (id, decision, button) => {
    button.disabled = true;
    try {
      const remarkEl = document.getElementById('remark-' + id);
      const res = await api('/api/evaluations/' + id + '/review', {
        method: 'POST',
        body: JSON.stringify({ decision: decision, remark: remarkEl ? remarkEl.value : null }),
      });
      flash(res.message);
      await load();
    } catch (err) {
      const detail = err.body && err.body.errors && err.body.errors.remark;
      flash(detail ? detail[0] : err.message, false);
      button.disabled = false;
    }
  };

  listEl.addEventListener('click', function (ev) {
    const open = ev.target.closest('[data-open]');
    if (open) return openDetail(open.dataset.open);

    const btn = ev.target.closest('[data-decide]');
    if (btn) return decide(btn.dataset.eval, btn.dataset.decide, btn);
  });

  statusEl.addEventListener('change', load);
  load();
})();
</script>
@endpush