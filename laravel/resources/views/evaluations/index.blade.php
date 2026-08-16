{{-- Role-aware list. Students only ever see approved evaluations. --}}
@extends('layouts.app')

@section('title', 'Evaluations — InternHub')
@section('page_title', 'Evaluations')
@section('page_subtitle', 'Performance evaluations linked to your account.')

@section('content')
  <div class="eval-actions" id="actions" style="margin:0 0 1.25rem;"></div>
  <div id="list"></div>
@endsection

@push('scripts')
<script>
(async function () {
  if (!requireRole([])) return;

  if (userRole() === 'Employer') {
    document.getElementById('actions').innerHTML =
      '<a class="eval-btn" href="{{ url('/evaluations/create') }}">Evaluate An Intern</a>';
  }

  try {
    const page = await api('/api/evaluations');

    if (!page.data.length) {
      document.getElementById('list').innerHTML =
        '<div class="eval-card"><p class="eval-empty">Nothing here yet.</p></div>';
      return;
    }

    let rows = '';
    page.data.forEach(function (e) {
      const student = (e.student && e.student.user) ? e.student.user.full_name : '—';
      const company = (e.employer && e.employer.company) ? e.employer.company.company_name : '—';
      rows += '<tr><td>' + student + '</td><td>' + company + '</td><td>' + e.period + '</td>' +
        '<td>' + (e.total_score ? e.total_score + '%' : '—') + '</td>' +
        '<td><span class="eval-pill eval-pill-' + e.status + '">' + e.status + '</span></td>' +
        '<td><a href="/evaluations/' + e.evaluation_id + '">' +
          ((e.status === 'Draft' || e.status === 'Rejected') && userRole() === 'Employer' ? 'Continue' : 'Open') +
        '</a></td></tr>';
    });

    document.getElementById('list').innerHTML =
      '<div class="eval-card"><table><thead><tr>' +
      '<th>Student</th><th>Company</th><th>Period</th><th>Score</th><th>Status</th><th></th>' +
      '</tr></thead><tbody>' + rows + '</tbody></table></div>';
  } catch (e) {
    flash('Could not load evaluations. ' + e.message, false);
  }
})();
</script>
@endpush