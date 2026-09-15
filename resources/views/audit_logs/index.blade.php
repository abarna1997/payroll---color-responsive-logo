@extends('layouts.app')

@section('content')
<div class="row g-4">
    <div class="col-lg-12">
        <div class="glass-card">
            <h5 class="mb-4 display-font"><i class="bi bi-shield-check me-2 text-indigo"></i> System Activity Audit Trail</h5>
            <p class="fs-7 text-secondary mb-4">This activity ledger is read-only and complies with standard security audits.</p>
            
            <div class="table-responsive">
                <table class="table custom-table">
                    <thead>
                        <tr>
                            <th>Log ID</th>
                            <th>Timestamp</th>
                            <th>User Profile</th>
                            <th>Action Tag</th>
                            <th>Module</th>
                            <th>Record Ref</th>
                            <th>Previous State</th>
                            <th>Updated State</th>
                            <th>IP Origin</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                            <tr>
                                <td><span class="text-secondary">#{{ $log->id }}</span></td>
                                <td>{{ $log->created_at->toDateTimeString() }}</td>
                                <td class="fw-semibold">
                                    {{ $log->user ? $log->user->username : 'System / Guest' }}
                                </td>
                                <td>
                                    <span class="badge bg-dark border border-indigo text-indigo px-2 py-1 fs-8">
                                        {{ $log->action }}
                                    </span>
                                </td>
                                <td class="fw-semibold text-light">{{ $log->module }}</td>
                                <td>
                                    {{ $log->record_id ? '#' . $log->record_id : 'N/A' }}
                                </td>
                                <td style="max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="{{ $log->old_value }}">
                                    @if($log->old_value)
                                        <code class="text-danger fs-8" style="cursor: pointer;" onclick="showAuditDetails(this)" data-json="{{ $log->old_value }}">{{ $log->old_value }}</code>
                                    @else
                                        <span class="text-secondary fs-8">-</span>
                                    @endif
                                </td>
                                <td style="max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="{{ $log->new_value }}">
                                    @if($log->new_value)
                                        <code class="text-success fs-8" style="cursor: pointer;" onclick="showAuditDetails(this)" data-json="{{ $log->new_value }}">{{ $log->new_value }}</code>
                                    @else
                                        <span class="text-secondary fs-8">-</span>
                                    @endif
                                </td>
                                <td><span class="text-secondary">{{ $log->ip_address ?? '127.0.0.1' }}</span></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-secondary">No activity logs recorded.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination Links -->
            <div class="d-flex justify-content-center mt-4">
                {{ $logs->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
</div>
</div>

<!-- Audit Details Modal -->
<div class="modal fade" id="auditDetailsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content glass-card p-0 border-0 shadow-lg">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title display-font text-white"><i class="bi bi-code-slash me-2 text-indigo"></i> Audit Record Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body py-4">
                <pre><code id="auditDetailsContent" class="text-light" style="white-space: pre-wrap; word-wrap: break-word;"></code></pre>
            </div>
            <div class="modal-footer border-top-0 pt-0">
                <button type="button" class="btn btn-sm btn-custom-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
function showAuditDetails(element) {
    try {
        const rawJson = element.getAttribute('data-json');
        const parsed = JSON.parse(rawJson);
        const prettyJson = JSON.stringify(parsed, null, 4);
        document.getElementById('auditDetailsContent').textContent = prettyJson;
    } catch(e) {
        // If not valid JSON, just show raw string
        document.getElementById('auditDetailsContent').textContent = element.getAttribute('data-json');
    }
    const modal = new bootstrap.Modal(document.getElementById('auditDetailsModal'));
    modal.show();
}
</script>
@endsection
