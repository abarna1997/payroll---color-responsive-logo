@extends('layouts.app')

@section('content')
<div class="row g-4">
    <div class="col-lg-4">
        <div class="glass-card h-100">
            <h5 class="mb-4 display-font fw-bold text-dark"><i class="bi bi-inboxes me-2 text-primary"></i> Command Queue Summary</h5>
            <div class="row g-3 text-center">
                <div class="col-6"><div class="border rounded p-3 bg-light"><div class="text-secondary fs-8 uppercase">Pending</div><div class="fs-3 fw-bold text-warning">{{ $queueStats['pending'] }}</div></div></div>
                <div class="col-6"><div class="border rounded p-3 bg-light"><div class="text-secondary fs-8 uppercase">Sent</div><div class="fs-3 fw-bold text-info">{{ $queueStats['sent'] }}</div></div></div>
                <div class="col-6"><div class="border rounded p-3 bg-light"><div class="text-secondary fs-8 uppercase">Completed</div><div class="fs-3 fw-bold text-success">{{ $queueStats['completed'] }}</div></div></div>
                <div class="col-6"><div class="border rounded p-3 bg-light"><div class="text-secondary fs-8 uppercase">Failed</div><div class="fs-3 fw-bold text-danger">{{ $queueStats['failed'] }}</div></div></div>
                <div class="col-12"><div class="border rounded p-3 bg-light"><div class="text-secondary fs-8 uppercase">Cancelled</div><div class="fs-3 fw-bold text-secondary">{{ $queueStats['cancelled'] }}</div></div></div>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="glass-card mb-4">
            <h5 class="mb-4 display-font fw-bold text-dark"><i class="bi bi-plus-circle me-2 text-primary"></i> Queue New Device Command</h5>
            <form action="{{ route('queue-manager.store') }}" method="POST" class="row g-3">
                @csrf
                <div class="col-md-6">
                    <label class="form-label text-secondary fs-8 uppercase">Biometric Device</label>
                    <select class="form-select form-select-custom" name="device_id" required>
                        <option value="">Select device</option>
                        @foreach($queueDevices as $device)
                            <option value="{{ $device->id }}">{{ $device->device_name }} ({{ $device->device_serial_number }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label text-secondary fs-8 uppercase">Command</label>
                    <input type="text" class="form-control form-control-custom" name="command" placeholder="REBOOT, INFO, SET_TIME ..." required>
                </div>
                <div class="col-12 text-end">
                    <button type="submit" class="btn btn-custom-primary">Queue Command</button>
                </div>
            </form>
        </div>

        <div class="glass-card">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="display-font fw-bold text-dark m-0"><i class="bi bi-list-check me-2 text-primary"></i> Command Queue</h5>
            </div>
            <div class="table-responsive">
                <table class="table custom-table text-start align-middle">
                    <thead>
                        <tr>
                            <th>Device</th>
                            <th>Command</th>
                            <th>Status</th>
                            <th>Failure Reason</th>
                            <th>Retries</th>
                            <th>Created At & By</th>
                            <th>Execution Time</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($queueCommands as $command)
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ $command->device->device_name ?? 'Unknown Device' }}</div>
                                    <div class="text-secondary fs-8">{{ $command->device->device_serial_number ?? 'N/A' }}</div>
                                </td>
                                <td>
                                    <div class="text-break" style="max-width: 380px; word-break: break-all;">
                                        @if(str_contains($command->command, 'Content='))
                                            @php
                                                $parts = explode('Content=', $command->command, 2);
                                                $prefix = $parts[0] . 'Content=';
                                                $base64 = $parts[1] ?? '';
                                                $truncatedBase64 = substr($base64, 0, 30) . '... [Base64 Photo ' . strlen($base64) . ' Bytes]';
                                            @endphp
                                            <code class="text-primary font-monospace fs-8" title="{{ $command->command }}">{{ $prefix }}{{ $truncatedBase64 }}</code>
                                        @else
                                            <code class="text-primary font-monospace fs-8">{{ \Illuminate\Support\Str::limit($command->command, 120) }}</code>
                                        @endif
                                    </div>
                                </td>
                                <td><span class="badge bg-{{ $command->status === 'pending' ? 'warning' : ($command->status === 'sent' ? 'info' : ($command->status === 'completed' ? 'success' : ($command->status === 'failed' ? 'danger' : 'secondary'))) }}">{{ ucfirst($command->status) }}</span></td>
                                <td class="text-wrap" style="min-width: 220px; max-width: 320px;">{{ $command->failure_reason ?? '—' }}</td>
                                <td>{{ $command->retry_count }}</td>
                                <td>
                                    <div class="fw-semibold text-dark fs-8">{{ $command->created_at ? $command->created_at->format('d M Y, h:i:s A') : 'N/A' }}</div>
                                    <div class="text-secondary fs-8.5">By: {{ $command->creator->name ?? 'System' }}</div>
                                </td>
                                <td>
                                    @if($command->completed_at)
                                        <div class="text-success fw-semibold fs-8">{{ $command->completed_at->format('d M Y, h:i:s A') }}</div>
                                        <div class="text-secondary fs-8.5">{{ $command->execution_time_ms ? $command->execution_time_ms . ' ms' : '' }}</div>
                                    @elseif($command->sent_at)
                                        <div class="text-info fw-semibold fs-8">Sent {{ $command->sent_at->diffForHumans() }}</div>
                                    @else
                                        <div class="text-secondary fs-8 italic">Pending Poll</div>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex gap-2 flex-wrap">
                                        <form action="{{ route('queue-manager.retry', $command->id) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-primary">Retry</button>
                                        </form>
                                        <form action="{{ route('queue-manager.cancel', $command->id) }}" method="POST" onsubmit="return confirm('Cancel this queue item?')">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-danger">Cancel</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-secondary py-4">No queue items found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                {{ $queueCommands->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
