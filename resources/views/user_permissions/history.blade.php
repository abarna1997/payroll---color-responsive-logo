@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="mb-0 display-font"><i class="bi bi-clock-history me-2 text-indigo"></i> Permissions Override Audit Logs</h5>
            <a href="{{ route('permissions.index') }}" class="btn btn-custom-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Back to Overrides
            </a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="glass-card">
            <div class="table-responsive">
                <table class="table custom-table">
                    <thead>
                        <tr>
                            <th>Target User</th>
                            <th>Permission Key</th>
                            <th class="text-center">Old State</th>
                            <th class="text-center">New State</th>
                            <th>Updated By</th>
                            <th>IP Address</th>
                            <th>Reason</th>
                            <th class="text-end">Timestamp</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                            <tr>
                                <td>
                                    <span class="fw-semibold text-dark">{{ $log->user->username ?? 'N/A' }}</span>
                                </td>
                                <td>
                                    <code class="text-indigo fs-8.5">{{ $log->permission_key }}</code>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-{{ $log->old_value === 'Allow' ? 'success' : ($log->old_value === 'Deny' ? 'danger' : 'secondary') }}">
                                        {{ $log->old_value ?? 'Inherit' }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-{{ $log->new_value === 'Allow' ? 'success' : ($log->new_value === 'Deny' ? 'danger' : 'secondary') }}">
                                        {{ $log->new_value }}
                                    </span>
                                </td>
                                <td>
                                    <span class="text-secondary">{{ $log->editor->username ?? 'System' }}</span>
                                </td>
                                <td>
                                    <span class="text-muted fs-8.5">{{ $log->ip_address ?? '127.0.0.1' }}</span>
                                </td>
                                <td class="fs-8.5 text-secondary" style="max-width: 200px;">
                                    {{ $log->reason ?? 'N/A' }}
                                </td>
                                <td class="text-end text-muted fs-9">
                                    {{ $log->created_at->format('Y-m-d H:i:s') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-secondary py-4">No permission changes logged yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-3 border-top" style="border-color: var(--border-color) !important;">
                {{ $logs->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
