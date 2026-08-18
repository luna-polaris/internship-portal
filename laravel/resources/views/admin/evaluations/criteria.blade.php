@extends('layouts.app')

@section('title', 'Evaluation Criteria — InternHub')
@section('page_title', 'Evaluation Criteria')
@section('page_subtitle', 'The rubric every evaluation is scored against. Weights are a percentage of the total and should add up to 100.')

@section('content')
  <div class="eval-card" id="health"></div>

  <div class="eval-card">
    <h2 id="form-heading">Add A Criterion</h2>

    <label for="name">Name</label>
    <input type="text" id="name" maxlength="100" placeholder="e.g. Problem Solving">

    <label for="description">Description</label>
    <textarea id="description" rows="2" placeholder="What an evaluator should look for. Shown on the scoring form."></textarea>

    <div style="display:grid;gap:1rem;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));">
      <div>
        <label for="category">Category</label>
        <select id="category">
          <option value="Technical">Technical</option>
          <option value="Soft Skills">Soft Skills</option>
          <option value="Professionalism">Professionalism</option>
          <option value="Other" selected>Other</option>
        </select>
      </div>
      <div>
        <label for="max_score">Scored out of</label>
        <input type="number" id="max_score" min="1" max="100" value="5">
      </div>
      <div>
        <label for="weight">Weight (%)</label>
        <input type="number" id="weight" min="0" max="100" step="0.5" value="10">
      </div>
    </div>

    <div class="eval-actions">
      <button type="button" class="eval-btn" id="save">Add Criterion</button>
      <button type="button" class="eval-btn eval-btn--ghost" id="cancel" hidden>Cancel</button>
    </div>
  </div>

  <div class="eval-card">
    <h2>Current Rubric</h2>
    <div id="list"></div>
  </div>
@endsection

@push('scripts')
<script>
(function () {
  if (!requireRole(['Admin'])) return;

  const listEl = document.getElementById('list');
  const healthEl = document.getElementById('health');
  const headingEl = document.getElementById('form-heading');
  const saveBtn = document.getElementById('save');
  const cancelBtn = document.getElementById('cancel');

  const fields = ['name', 'description', 'category', 'max_score', 'weight'];
  let editingId = null;

  const read = function () {
    const out = {};
    fields.forEach(function (f) { out[f] = document.getElementById(f).value; });
    return out;
  };

  const write = function (values) {
    fields.forEach(function (f) {
      const v = values[f];
      document.getElementById(f).value = (v === null || v === undefined) ? '' : v;
    });
  };

  const resetForm = function () {
    editingId = null;
    write({ name: '', description: '', category: 'Other', max_score: 5, weight: 10 });
    headingEl.textContent = 'Add A Criterion';
    saveBtn.textContent = 'Add Criterion';
    cancelBtn.hidden = true;
    document.querySelectorAll('.eval-err').forEach(function (el) { el.remove(); });
  };

  const load = async function () {
    listEl.innerHTML = '<p class="eval-empty">Loading…</p>';
    try {
      const res = await api('/api/criteria');

      // Weights only mean anything if the active ones sum to 100 — surface that rather than letting it silently skew every score.
      const total = res.meta.active_weight_total;
      healthEl.innerHTML = res.meta.weights_balanced
        ? '<p class="eval-note">Active weights total <strong>' + total + '%</strong>. The rubric is balanced.</p>'
        : '<p class="eval-note" style="color:var(--eval-warn);">Active weights total <strong>' + total +
          '%</strong>, not 100%. Every score will be off until you rebalance them.</p>';

      if (!res.data.length) {
        listEl.innerHTML = '<p class="eval-empty">No criteria yet. Add one above, or run the seeder for a default set of seven.</p>';
        return;
      }

      let rows = '';
      res.data.forEach(function (c) {
        rows += '<tr' + (c.is_active ? '' : ' style="opacity:.5;"') + '>' +
          '<td><strong>' + c.name + '</strong>' +
            (c.description ? '<br><span class="eval-note">' + c.description + '</span>' : '') +
            (c.is_active ? '' : '<br><span class="eval-note">Inactive — hidden from new forms</span>') + '</td>' +
          '<td>' + c.category + '</td>' +
          '<td>' + c.max_score + '</td>' +
          '<td>' + c.weight + '%</td>' +
          '<td style="white-space:nowrap;">' +
            '<button type="button" class="eval-btn eval-btn--ghost" data-edit="' + c.criterion_id + '">Edit</button> ' +
            '<button type="button" class="eval-btn eval-btn--ghost" data-remove="' + c.criterion_id + '">Remove</button>' +
          '</td></tr>';
      });

      listEl.innerHTML = '<table><thead><tr><th>Criterion</th><th>Category</th>' +
        '<th>Out of</th><th>Weight</th><th></th></tr></thead><tbody>' + rows + '</tbody></table>';
    } catch (e) {
      flash('Could not load the rubric. ' + e.message, false);
      listEl.innerHTML = '';
    }
  };

  saveBtn.onclick = async function () {
    document.querySelectorAll('.eval-err').forEach(function (el) { el.remove(); });

    const payload = read();
    payload.is_active = true;

    try {
      const res = await api(editingId ? '/api/criteria/' + editingId : '/api/criteria', {
        method: editingId ? 'PUT' : 'POST',
        body: JSON.stringify(payload),
      });
      flash(res.message);
      resetForm();
      await load();
      window.scrollTo({ top: 0, behavior: 'smooth' });
    } catch (e) {
      const errors = (e.body && e.body.errors) || {};
      Object.keys(errors).forEach(function (field) {
        const target = document.getElementById(field);
        if (!target) return;
        const p = document.createElement('p');
        p.className = 'eval-err';
        p.textContent = errors[field][0];
        target.after(p);
      });
      flash(e.message || 'Fix the highlighted fields and try again.', false);
    }
  };

  cancelBtn.onclick = resetForm;

  listEl.addEventListener('click', async function (ev) {
    const edit = ev.target.closest('[data-edit]');
    const remove = ev.target.closest('[data-remove]');

    if (edit) {
      try {
        const res = await api('/api/criteria/' + edit.dataset.edit);
        write(res.data);
        editingId = res.data.criterion_id;
        headingEl.textContent = 'Edit ' + res.data.name;
        saveBtn.textContent = 'Save Changes';
        cancelBtn.hidden = false;
        window.scrollTo({ top: 0, behavior: 'smooth' });
      } catch (e) { flash(e.message, false); }
    }

    if (remove) {
      if (!confirm('Remove this criterion? If it has already been used to score someone, it will be deactivated instead so past totals stay intact.')) return;
      try {
        const res = await api('/api/criteria/' + remove.dataset.remove, { method: 'DELETE' });
        flash(res.message);
        await load();
      } catch (e) { flash(e.message, false); }
    }
  });

  resetForm();
  load();
})();
</script>
@endpush