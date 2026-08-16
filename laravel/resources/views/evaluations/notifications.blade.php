{{-- Function 5 — Notification and Alerts. --}}
@extends('layouts.app')

@section('title', 'Alerts — InternHub')
@section('page_title', 'Alerts')
@section('page_subtitle', 'Evaluation activity that needs your attention.')

@section('content')
  <div class="eval-actions" style="margin:0 0 1.25rem;">
    <button type="button" class="eval-btn eval-btn--ghost" id="read-all">Mark All As Read</button>
    <span class="eval-note" id="count" style="align-self:center;"></span>
  </div>

  <div id="list"></div>
@endsection

@push('scripts')
<script>
(function () {
  if (!requireRole([])) return;

  const listEl = document.getElementById('list');
  const countEl = document.getElementById('count');

  const load = async () => {
    try {
      const res = await api('/api/notifications');
      countEl.textContent = res.unread_count ? res.unread_count + ' unread' : 'You are all caught up.';

      if (!res.data.length) {
        listEl.innerHTML = '<div class="eval-card"><p class="eval-empty">No alerts yet. You will be notified here when an evaluation needs your attention.</p></div>';
        return;
      }

      let html = '';
      res.data.forEach(function (n) {
        const style = n.read_at ? 'opacity:.6;' : 'border-left:3px solid var(--eval-accent);';
        html += '<div class="eval-card" style="' + style + '">' +
          '<div style="display:flex;justify-content:space-between;gap:1rem;flex-wrap:wrap;">' +
            '<strong>' + n.title + '</strong>' +
            '<span class="eval-note">' + new Date(n.created_at).toLocaleString() + '</span>' +
          '</div>' +
          '<p class="eval-note" style="margin:.6rem 0 1rem;">' + n.message + '</p>' +
          '<div class="eval-actions" style="margin:0;">' +
            (n.link ? '<a class="eval-btn eval-btn--ghost" href="' + n.link + '">Open</a>' : '') +
            (!n.read_at ? '<button type="button" class="eval-btn eval-btn--ghost" data-read="' + n.notification_id + '">Mark As Read</button>' : '') +
            '<button type="button" class="eval-btn eval-btn--ghost" data-remove="' + n.notification_id + '">Remove</button>' +
          '</div></div>';
      });
      listEl.innerHTML = html;
    } catch (e) {
      flash('Could not load your alerts. ' + e.message, false);
    }
  };

  listEl.addEventListener('click', async function (ev) {
    const read = ev.target.closest('[data-read]');
    const remove = ev.target.closest('[data-remove]');
    try {
      if (read)   await api('/api/notifications/' + read.dataset.read + '/read', { method: 'PATCH' });
      if (remove) await api('/api/notifications/' + remove.dataset.remove, { method: 'DELETE' });
      if (read || remove) load();
    } catch (e) { flash(e.message, false); }
  });

  document.getElementById('read-all').onclick = async function () {
    try {
      const res = await api('/api/notifications/read-all', { method: 'PATCH' });
      flash(res.message);
      load();
    } catch (e) { flash(e.message, false); }
  };

  load();
  setInterval(load, 60000);
})();
</script>
@endpush