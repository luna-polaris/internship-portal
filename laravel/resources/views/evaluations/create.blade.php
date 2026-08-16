{{-- Function 2 — Feedback Submission. --}}
@extends('layouts.app')

@section('title', 'Evaluate An Intern — InternHub')
@section('page_title', 'Evaluate An Intern')
@section('page_subtitle', 'Score each criterion, then save a draft or submit for admin review. Once submitted you cannot edit it unless an admin sends it back.')

@section('content')
  <form id="form" class="eval-card" novalidate>
    <label for="student_id">Intern</label>
    <select id="student_id" required>
      <option value="">Select an intern</option>
    </select>

    <label for="period">Evaluation period</label>
    <select id="period">
      <option value="Midterm">Midterm</option>
      <option value="Final" selected>Final</option>
    </select>

    <h2 style="margin:2rem 0 .5rem;">Scores</h2>
    <p class="eval-note" id="rubric-note" style="margin:0 0 1rem;"></p>
    <div id="criteria"></div>

    <label for="strengths">What they did well</label>
    <textarea id="strengths" rows="3" placeholder="Specific examples help the student more than general praise."></textarea>

    <label for="improvements">What to work on</label>
    <textarea id="improvements" rows="3"></textarea>

    <label for="overall_comment">Overall comment (required to submit)</label>
    <textarea id="overall_comment" rows="3"></textarea>

    <div class="eval-actions">
      <button type="button" class="eval-btn eval-btn--ghost" id="save">Save Draft</button>
      <button type="button" class="eval-btn" id="submit">Submit For Review</button>
    </div>
  </form>
@endsection

@push('scripts')
<script>
(async function () {
  if (!requireRole(['Employer'])) return;

  const wrap = document.getElementById('criteria');
  let criteria = [];

  try {
    const res = await api('/api/criteria');
    criteria = res.data;

    if (!criteria.length) {
      wrap.innerHTML = '<p class="eval-empty">No evaluation criteria have been set up yet. Ask an admin to define the rubric first.</p>';
    } else {
      let html = '';
      criteria.forEach(function (c) {
        html += '<div class="eval-criterion">' +
          '<div class="eval-criterion__head">' +
            '<span class="eval-criterion__name">' + c.name + '</span>' +
            '<span class="eval-criterion__weight">' + c.category + ' · ' + c.weight + '% of total</span>' +
          '</div>' +
          (c.description ? '<p class="eval-criterion__desc">' + c.description + '</p>' : '') +
          '<div class="eval-criterion__inputs">' +
            '<input type="number" min="0" max="' + c.max_score + '" step="0.5" data-criterion="' + c.criterion_id + '"' +
              ' aria-label="Score for ' + c.name + ' out of ' + c.max_score + '">' +
            '<span class="eval-criterion__scale">out of ' + c.max_score + '</span>' +
            '<input type="text" data-remark="' + c.criterion_id + '" placeholder="Optional note">' +
          '</div></div>';
      });
      wrap.innerHTML = html;
    }

    document.getElementById('rubric-note').textContent = res.meta.weights_balanced
      ? 'Weighted out of 100.'
      : 'Heads up: the active criteria weights add up to ' + res.meta.active_weight_total + '%, not 100%.';
  } catch (e) {
    flash('Could not load the rubric. ' + e.message, false);
  }

  /* Only students this employer has actually accepted onto a posting. */
  try {
    const res2 = await api('/api/evaluable-students');
    const sel = document.getElementById('student_id');

    res2.data.forEach(function (s) {
      const o = document.createElement('option');
      o.value = s.student_id;
      o.dataset.applicationId = s.application_id;
      o.textContent = s.full_name + ' — ' + s.matric_no + ' (' + s.internship_title + ')';
      sel.appendChild(o);
    });

    if (!res2.data.length) {
      sel.insertAdjacentHTML('afterend',
        '<p class="eval-note" style="margin:.5rem 0 0;">No accepted interns yet. Students appear here once you accept their application.</p>');
    }/* Resuming a draft or a returned evaluation: prefill everything so the
       employer edits rather than retypes. */
    const editId = new URLSearchParams(window.location.search).get('id');
    if (editId) {
      const existing = await api('/api/evaluations/' + editId);
      const ev = existing.data;

      sel.value = ev.student_id;
      document.getElementById('period').value = ev.period;
      document.getElementById('strengths').value = ev.strengths || '';
      document.getElementById('improvements').value = ev.improvements || '';
      document.getElementById('overall_comment').value = ev.overall_comment || '';

      ev.scores.forEach(function (s) {
        const scoreInput = document.querySelector('[data-criterion="' + s.criterion_id + '"]');
        const remarkInput = document.querySelector('[data-remark="' + s.criterion_id + '"]');
        if (scoreInput) scoreInput.value = parseFloat(s.score);
        if (remarkInput) remarkInput.value = s.remark || '';
      });

      document.querySelector('.eval-title').textContent =
        ev.status === 'Rejected' ? 'Revise Evaluation' : 'Continue Draft';

      if (ev.status === 'Rejected' && ev.review_remark) {
        document.getElementById('form').insertAdjacentHTML('beforebegin',
          '<div class="eval-card" style="border-color:var(--eval-warn);">' +
          '<p class="eval-note"><strong>An admin returned this.</strong> ' + ev.review_remark + '</p></div>');
      }
    }
  } catch (e) {
    flash('Could not load your interns. ' + e.message, false);
  }

  const send = async function (action) {
    const scores = [];
    criteria.forEach(function (c) {
      const value = document.querySelector('[data-criterion="' + c.criterion_id + '"]').value;
      if (value === '') return;
      scores.push({
        criterion_id: c.criterion_id,
        score: value,
        remark: document.querySelector('[data-remark="' + c.criterion_id + '"]').value || null,
      });
    });

    document.querySelectorAll('.eval-err').forEach(function (el) { el.remove(); });

    const selected = document.getElementById('student_id').selectedOptions[0];

    try {
      const res = await api('/api/evaluations', {
        method: 'POST',
        body: JSON.stringify({
          student_id: document.getElementById('student_id').value,
          application_id: selected ? selected.dataset.applicationId : null,
          period: document.getElementById('period').value,
          strengths: document.getElementById('strengths').value,
          improvements: document.getElementById('improvements').value,
          overall_comment: document.getElementById('overall_comment').value,
          action: action,
          scores: scores,
        }),
      });
      flash(res.message);
      window.scrollTo({ top: 0, behavior: 'smooth' });
    } catch (e) {
      const errors = (e.body && e.body.errors) || {};
      Object.keys(errors).forEach(function (field) {
        const target = document.getElementById(field.split('.')[0]) || wrap;
        const p = document.createElement('p');
        p.className = 'eval-err';
        p.textContent = errors[field][0];
        target.after(p);
      });
      flash(e.message || 'Fix the highlighted fields and try again.', false);
      window.scrollTo({ top: 0, behavior: 'smooth' });
    }
  };

  document.getElementById('save').onclick = function () { send('save'); };
  document.getElementById('submit').onclick = function () { send('submit'); };
})();
</script>
@endpush