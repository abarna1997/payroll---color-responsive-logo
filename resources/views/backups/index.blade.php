@extends('layouts.app')

@section('content')
<div class="row g-4">
    <!-- List of Backups -->
    <div class="col-lg-8">
        <div class="glass-card">
            <h5 class="mb-4 display-font"><i class="bi bi-database-check me-2 text-indigo"></i> Database Backups Ledger</h5>
            
            <div class="table-responsive">
                <table class="table custom-table">
                    <thead>
                        <tr>
                            <th>Backup Date</th>
                            <th>Backup Type</th>
                            <th>Size</th>
                            <th>File Reference</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($backups as $b)
                            <tr>
                                @php
                                    $sizeMb = 0;
                                    $path = storage_path('app/backups/' . $b->backup_location);
                                    if(file_exists($path)) {
                                        $sizeMb = round(filesize($path) / 1048576, 2);
                                    }
                                @endphp
                                <td class="fw-semibold">{{ $b->backup_date->toDateTimeString() }}</td>
                                <td>
                                    <span class="badge {{ $b->backup_type === 'Manual' ? 'bg-primary' : ($b->backup_type === 'Safety' ? 'bg-warning text-dark' : 'bg-info') }}">
                                        {{ $b->backup_type }}
                                    </span>
                                </td>
                                <td><span class="text-secondary fs-8">{{ $sizeMb > 0 ? $sizeMb . ' MB' : '--' }}</span></td>
                                <td><span class="text-secondary">{{ $b->backup_location }}</span></td>
                                <td>
                                    <span class="badge-status {{ $b->backup_status === 'Success' ? 'badge-online' : 'badge-offline' }}">
                                        {{ $b->backup_status }}
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex gap-2">
                                        @if($b->backup_status === 'Success')
                                            <a href="{{ route('backups.download', $b->id) }}" class="btn btn-sm btn-outline-primary border-0" title="Download SQL">
                                                <i class="bi bi-download"></i>
                                            </a>
                                            <form action="{{ route('backups.restore', $b->id) }}" method="POST" class="d-inline" onsubmit="return confirmRestore(this);">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-danger border-0" title="Restore Database">
                                                    <i class="bi bi-arrow-counterclockwise"></i> Restore
                                                </button>
                                            </form>
                                        @else
                                            <span class="text-secondary fs-8">N/A</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-secondary">No database backups generated yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Manual Backup Trigger -->
    <div class="col-lg-4">
        <div class="glass-card text-center py-5">
            <div class="fs-1 text-indigo mb-3"><i class="bi bi-shield-lock-fill"></i></div>
            <h5 class="display-font">Database Recovery Control</h5>
            <p class="text-secondary fs-7 px-3 mb-4">You can trigger a manual database dump at any time. The system will run `mysqldump` and generate a secure SQL file stored locally in the storage server directory.</p>
            
            <form action="{{ route('backups.trigger') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-custom-primary px-4 py-2">
                    <i class="bi bi-shield-plus me-1"></i> Trigger Manual Backup
                </button>
            </form>
        </div>
    </div>
</div>

<script>
function confirmRestore(formElement) {
    const userInput = prompt("⚠️ DATABASE RESTORE\n\nThis operation will replace the current database with the selected backup.\nA safety backup of the current database will be created first.\n\nType RESTORE to continue:");
    
    if (userInput === "RESTORE") {
        formElement.querySelector('button[type="submit"]').innerHTML = '<i class="spinner-border spinner-border-sm"></i> Restoring...';
        formElement.querySelector('button[type="submit"]').disabled = true;
        return true;
    } else {
        if (userInput !== null) {
            alert("Restore cancelled.");
        }
        return false;
    }
}
</script>
@endsection
