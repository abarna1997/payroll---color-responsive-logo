@extends('layouts.app')

@section('content')
<div class="row g-4">
    <!-- Statistics Summary Widget -->
    <div class="col-lg-12 mb-2">
        <div class="glass-card py-3">
            <div class="row align-items-center g-3">
                <div class="col-md-2 border-end border-secondary border-opacity-10 text-center">
                    <span class="fs-8 text-secondary uppercase text-uppercase tracking-wider d-block mb-1">Total Devices</span>
                    <h3 class="m-0 text-light fw-bold display-font">{{ $totalDevices }}</h3>
                </div>
                <div class="col-md-4 border-end border-secondary border-opacity-10">
                    <div class="row text-center fs-8">
                        <div class="col-3">
                            <span class="text-success d-block mb-1"><i class="bi bi-circle-fill me-1"></i> Online</span>
                            <span class="text-light fw-bold fs-6">{{ $onlineDevices }}</span>
                        </div>
                        <div class="col-3">
                            <span class="text-danger d-block mb-1"><i class="bi bi-circle-fill me-1"></i> Offline</span>
                            <span class="text-light fw-bold fs-6">{{ $offlineDevices }}</span>
                        </div>
                        <div class="col-3">
                            <span class="text-secondary d-block mb-1"><i class="bi bi-pause-circle-fill me-1"></i> Disabled</span>
                            <span class="text-light fw-bold fs-6">{{ $disabledDevices }}</span>
                        </div>
                        <div class="col-3">
                            <span class="text-warning d-block mb-1"><i class="bi bi-question-circle-fill me-1"></i> Pending</span>
                            <span class="text-light fw-bold fs-6">{{ $pendingDevices }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 border-end border-secondary border-opacity-10 text-center">
                    <span class="fs-8 text-secondary uppercase text-uppercase tracking-wider d-block mb-1">Average Health</span>
                    <h5 class="m-0 text-indigo fw-bold display-font">
                        {{ $averageHealth }}/100
                        @if($averageHealth >= 90)
                            <span class="fs-8 text-success d-block">★★★★★ Excellent</span>
                        @elseif($averageHealth >= 75)
                            <span class="fs-8 text-info d-block">★★★★☆ Good</span>
                        @elseif($averageHealth >= 50)
                            <span class="fs-8 text-warning d-block">★★★☆☆ Fair</span>
                        @elseif($averageHealth >= 25)
                            <span class="fs-8 text-danger d-block">★★☆☆☆ Poor</span>
                        @else
                            <span class="fs-8 text-danger d-block">★☆☆☆☆ Critical</span>
                        @endif
                    </h5>
                </div>
                <div class="col-md-3 text-center">
                    <span class="fs-8 text-secondary uppercase text-uppercase tracking-wider d-block mb-1">Avg Response Latency</span>
                    <h4 class="m-0 text-light fw-bold display-font">
                        {{ $avgResponseTime > 0 ? $avgResponseTime . ' ms' : 'N/A' }}
                    </h4>
                </div>
            </div>
        </div>
    </div>

    <!-- Devices List -->
    <div class="col-lg-12">
        <div class="glass-card">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="m-0 display-font"><i class="bi bi-cpu me-2 text-indigo"></i> Biometric Terminals Control Board</h5>
                <div>
                    <button class="btn btn-sm btn-custom-info me-2" data-bs-toggle="modal" data-bs-target="#scanDevicesModal">
                        <i class="bi bi-search me-1"></i> Scan Network
                    </button>
                    <button class="btn btn-sm btn-custom-primary" data-bs-toggle="modal" data-bs-target="#manualDiscoverModal">
                        <i class="bi bi-plus-circle me-1"></i> Register Device
                    </button>
                </div>
            </div>
            
            <div class="row g-4">
                @forelse($devices as $d)
                    <div class="col-xl-6">
                        <div class="p-4 rounded border h-100 d-flex flex-column justify-content-between" style="background-color: rgba(0,0,0,0.15); border-color: var(--border-color) !important;">
                            <div>
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div>
                                        <h6 class="m-0 text-light fs-5 display-font fw-bold">{{ $d->device_name }}</h6>
                                        <span class="fs-8 text-secondary">Model: {{ $d->device_model ?? 'ZKTeco SenseFace' }} | Firmware: {{ $d->firmware_version ?? 'PUSH SDK 2.0' }}</span>
                                    </div>
                                    <div>
                                        <span class="badge-status {{ $d->status_text === 'Online' ? 'badge-online' : ($d->status_text === 'Pending Approval' ? 'badge-pending' : ($d->status_text === 'Disabled' ? 'badge-disabled' : 'badge-offline')) }}">
                                            <i class="bi bi-circle-fill" style="font-size: 0.5rem;"></i> {{ $d->status_text }}
                                        </span>
                                    </div>
                                </div>

                                <div class="row g-3 fs-7 mb-4">
                                    <div class="col-6">
                                        <span class="text-secondary d-block">Serial Number</span>
                                        <span class="text-light fw-medium">{{ $d->device_serial_number }}</span>
                                    </div>
                                    <div class="col-6">
                                        <span class="text-secondary d-block">Timezone</span>
                                        <span class="text-light fw-medium">{{ $d->timezone }}</span>
                                    </div>
                                    <div class="col-6">
                                        <span class="text-secondary d-block">Company / Branch</span>
                                        <span class="text-light fw-medium">
                                            @if($d->company)
                                                {{ $d->company->company_code }} - {{ $d->branch ? $d->branch->branch_name : 'All Branches' }}
                                            @else
                                                <span class="text-warning">Unassigned</span>
                                            @endif
                                        </span>
                                    </div>
                                    <div class="col-6">
                                        <span class="text-secondary d-block">Location</span>
                                        <span class="text-light fw-medium">{{ $d->location ?? 'Not Specified' }}</span>
                                    </div>
                                    <div class="col-6">
                                        <span class="text-secondary d-block">Last Activity Log</span>
                                        <span class="text-light fw-medium">
                                            {{ $d->last_seen ? $d->last_seen->diffForHumans() : 'Never Connected' }}
                                        </span>
                                    </div>
                                    <div class="col-6">
                                        <span class="text-secondary d-block">Last Log Received</span>
                                        <span class="text-light fw-medium">
                                            {{ $d->last_attendance_received ? $d->last_attendance_received->diffForHumans() : 'No Punches' }}
                                        </span>
                                    </div>
                                    <div class="col-6">
                                        <span class="text-secondary d-block">Employees Assigned</span>
                                        <span class="text-light fw-bold">{{ $d->employees_count }} Staff</span>
                                    </div>
                                    <div class="col-6">
                                        <span class="text-secondary d-block">Punches Logged Today</span>
                                        <span class="text-success fw-bold">{{ $d->attendance_today_count }} Punches</span>
                                    </div>
                                </div>

                                <!-- Device Information Section -->
                                <div class="mt-4 pt-3 border-top" style="border-color: rgba(255,255,255,0.05) !important;">
                                    <h6 class="fs-7 text-indigo display-font fw-bold mb-3"><i class="bi bi-info-circle me-1"></i> Device Information</h6>
                                    
                                    <div class="row g-2 fs-7 text-secondary">
                                        <!-- Network & Details -->
                                        <div class="col-6">
                                            <span class="d-block text-secondary fs-8">IP Address</span>
                                            <span class="text-light fw-medium">{{ $d->ip_address ?? 'N/A' }}</span>
                                        </div>
                                        <div class="col-6">
                                            <span class="d-block text-secondary fs-8">Public IP</span>
                                            <span class="text-light fw-medium">{{ $d->public_ip_address ?? 'N/A' }}</span>
                                        </div>
                                        <div class="col-6">
                                            <span class="d-block text-secondary fs-8">Firmware Version</span>
                                            <span class="text-light fw-medium">{{ $d->firmware_version ?? 'N/A' }}</span>
                                        </div>
                                        <div class="col-6">
                                            <span class="d-block text-secondary fs-8">SDK Version</span>
                                            <span class="text-light fw-medium">{{ $d->sdk_version ?? 'N/A' }}</span>
                                        </div>
                                        
                                        <!-- Health Score -->
                                        <div class="col-12 text-start">
                                            <span class="d-block text-secondary fs-8">Device Health</span>
                                            <span class="fw-bold fs-7 {{ $d->health_score >= 75 ? 'text-success' : ($d->health_score >= 50 ? 'text-warning' : 'text-danger') }}">
                                                {{ $d->health_score_stars }} ({{ $d->health_score }}/100)
                                            </span>
                                        </div>
                                        
                                        <!-- Storage Info -->
                                        <div class="col-12 mt-2 bg-black bg-opacity-10 p-2 rounded">
                                            <span class="d-block fs-8 text-secondary mb-1">Terminal Storage Capacity</span>
                                            <div class="row g-1 text-center text-light fs-8">
                                                <div class="col-4 border-end border-secondary border-opacity-10">
                                                    <span class="d-block text-secondary fs-9">Capacity</span>
                                                    <span class="fw-semibold">{{ $d->storage_capacity ? number_format($d->storage_capacity) : 'N/A' }}</span>
                                                </div>
                                                <div class="col-4 border-end border-secondary border-opacity-10">
                                                    <span class="d-block text-secondary fs-9">Used</span>
                                                    <span class="fw-semibold text-warning">{{ $d->storage_used ? number_format($d->storage_used) : 'N/A' }}</span>
                                                </div>
                                                <div class="col-4">
                                                    <span class="d-block text-secondary fs-9">Available</span>
                                                    <span class="fw-semibold text-success">{{ $d->storage_available ? number_format($d->storage_available) : 'N/A' }}</span>
                                                </div>
                                            </div>
                                         <!-- Multi-Biometric Counts -->
                                         <div class="col-12 mt-2">
                                             <div class="row g-1 text-secondary fs-8 text-center">
                                                 <div class="col">
                                                     <span class="d-block text-secondary fs-9">Users</span>
                                                     <span class="text-light fw-semibold">{{ number_format($d->user_count) }}</span>
                                                 </div>
                                                 <div class="col">
                                                     <span class="d-block text-secondary fs-9">Faces</span>
                                                     <span class="text-info fw-semibold">{{ number_format($d->face_count) }}</span>
                                                 </div>
                                                 <div class="col">
                                                     <span class="d-block text-secondary fs-9">Photos</span>
                                                     <span class="text-warning fw-semibold">{{ number_format($d->photo_count) }}</span>
                                                 </div>
                                                 <div class="col">
                                                     <span class="d-block text-secondary fs-9">Fingers</span>
                                                     <span class="text-light fw-semibold">{{ number_format($d->fingerprint_count) }}</span>
                                                 </div>
                                                 <div class="col">
                                                     <span class="d-block text-secondary fs-9">Cards</span>
                                                     <span class="text-light fw-semibold">{{ number_format($d->card_count) }}</span>
                                                 </div>
                                             </div>
                                         </div>  </div>
                                        </div>

                                        <!-- Sync times -->
                                        <div class="col-6 mt-2">
                                            <span class="d-block text-secondary fs-8">Device Logs Count</span>
                                            <span class="text-light fw-medium">{{ number_format($d->device_attendance_count) }}</span>
                                        </div>
                                        <div class="col-6 mt-2">
                                            <span class="d-block text-secondary fs-8">Last Info Sync</span>
                                            <span class="text-light fw-medium">{{ $d->last_info_sync ? $d->last_info_sync->diffForHumans() : 'Never' }}</span>
                                        </div>
                                        <div class="col-12 mt-1">
                                            <span class="d-block text-secondary fs-8">Last Heartbeat</span>
                                            <span class="text-light fw-medium">{{ $d->last_seen ? $d->last_seen->diffForHumans() : 'Never' }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="pt-3 mt-4 border-top d-flex gap-2" style="border-color: var(--border-color) !important;">
                                <button class="btn btn-sm btn-outline-info flex-grow-1" data-bs-toggle="modal" data-bs-target="#editModal{{ $d->id }}">
                                    <i class="bi bi-pencil-square me-1"></i> Edit
                                </button>

                                <form action="{{ route('devices.delete', $d->id) }}" method="POST" class="flex-grow-1" onsubmit="return confirm('Delete this device? Attendance history will remain, but device commands will be removed.')">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-danger w-100"><i class="bi bi-trash me-1"></i> Delete</button>
                                </form>

                                @if($d->status === 'Pending Approval')
                                    <button class="btn btn-sm btn-custom-primary flex-grow-1" data-bs-toggle="modal" data-bs-target="#approveModal{{ $d->id }}">
                                        <i class="bi bi-check-circle me-1"></i> Approve Terminal
                                    </button>
                                @else
                                    <!-- Command Dropdown -->
                                    <div class="dropdown flex-grow-1">
                                        <button class="btn btn-sm btn-custom-primary dropdown-toggle w-100" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="bi bi-terminal me-1"></i> Trigger Command
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-dark w-100">
                                            @if($d->status !== 'Disabled')
                                                <li>
                                                    <form action="{{ route('devices.sync-employees', $d->id) }}" method="POST">
                                                        @csrf
                                                        <button type="submit" class="dropdown-item py-2 text-indigo fw-bold"><i class="bi bi-people-fill me-2"></i> Sync Assigned Employees</button>
                                                    </form>
                                                </li>
                                                <li><hr class="dropdown-divider"></li>
                                                <li>
                                                    <form action="{{ route('devices.command', $d->id) }}" method="POST">
                                                        @csrf
                                                        <input type="hidden" name="command" value="REBOOT">
                                                        <button type="submit" class="dropdown-item py-2"><i class="bi bi-bootstrap-reboot me-2"></i> Reboot Device</button>
                                                    </form>
                                                </li>
                                                <li>
                                                    <form action="{{ route('devices.command', $d->id) }}" method="POST">
                                                        @csrf
                                                        <input type="hidden" name="command" value="SET_TIME">
                                                        <button type="submit" class="dropdown-item py-2"><i class="bi bi-clock me-2"></i> Sync Device Time</button>
                                                    </form>
                                                </li>
                                                <li>
                                                    <form action="{{ route('devices.command', $d->id) }}" method="POST">
                                                        @csrf
                                                        <input type="hidden" name="command" value="REFRESH_USERS">
                                                        <button type="submit" class="dropdown-item py-2"><i class="bi bi-people me-2"></i> Refresh Users</button>
                                                    </form>
                                                </li>
                                                <li>
                                                    <form action="{{ route('devices.command', $d->id) }}" method="POST">
                                                        @csrf
                                                        <input type="hidden" name="command" value="DATA QUERY USERINFO">
                                                        <button type="submit" class="dropdown-item py-2"><i class="bi bi-person-down me-2"></i> Sync Users from Device</button>
                                                    </form>
                                                </li>
                                                <li><hr class="dropdown-divider"></li>
                                                <li>
                                                    <a href="#" class="dropdown-item py-2 text-danger" data-bs-toggle="modal" data-bs-target="#deleteDeviceUserModal{{ $d->id }}">
                                                        <i class="bi bi-person-x me-2"></i> Delete User from Device
                                                    </a>
                                                </li>
                                            @endif
                                            
                                            <li>
                                                <form action="{{ route('devices.command', $d->id) }}" method="POST">
                                                    @csrf
                                                    <input type="hidden" name="command" value="INFO">
                                                    <button type="submit" class="dropdown-item py-2"><i class="bi bi-info-circle me-2"></i> Get Device Information</button>
                                                </form>
                                            </li>
                                            
                                            <li>
                                                <form action="{{ route('devices.command', $d->id) }}" method="POST">
                                                    @csrf
                                                    <input type="hidden" name="command" value="NETWORK_INFO">
                                                    <button type="submit" class="dropdown-item py-2"><i class="bi bi-broadcast me-2"></i> Get Network Information</button>
                                                </form>
                                            </li>

                                            @if($d->status !== 'Disabled')
                                                <li>
                                                    <form action="{{ route('devices.command', $d->id) }}" method="POST">
                                                        @csrf
                                                        <input type="hidden" name="command" value="LOGATT">
                                                        <button type="submit" class="dropdown-item py-2"><i class="bi bi-download me-2"></i> Download Attendance Logs</button>
                                                    </form>
                                                </li>
                                                <li>
                                                    <form action="{{ route('devices.command', $d->id) }}" method="POST">
                                                        @csrf
                                                        <input type="hidden" name="command" value="CLEAR LOG">
                                                        <button type="submit" class="dropdown-item py-2 text-danger" onclick="return confirm('Are you sure you want to clear attendance logs on the device?')"><i class="bi bi-trash me-2 text-danger"></i> Clear Attendance Logs</button>
                                                    </form>
                                                </li>
                                            @endif
                                            
                                            <li>
                                                <form action="{{ route('devices.command', $d->id) }}" method="POST">
                                                    @csrf
                                                    <input type="hidden" name="command" value="PING">
                                                    <button type="submit" class="dropdown-item py-2"><i class="bi bi-activity me-2"></i> Ping Device</button>
                                                </form>
                                            </li>
                                        </ul>
                                    </div>

                                    @if($d->status === 'Disabled')
                                        <form action="{{ route('devices.enable', $d->id) }}" method="POST" class="flex-grow-1">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-success w-100"><i class="bi bi-play-fill me-1"></i> Enable</button>
                                        </form>
                                    @else
                                        <form action="{{ route('devices.disable', $d->id) }}" method="POST" class="flex-grow-1">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-danger w-100"><i class="bi bi-pause-fill me-1"></i> Disable</button>
                                        </form>
                                    @endif
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12 text-center py-5 text-secondary">
                        <i class="bi bi-cpu fs-1 mb-3"></i>
                        <p>No biometric devices discovered yet.</p>
                        <span class="fs-7">Plug in your ZKTeco SenseFace terminal and point the ADMS Cloud Server setting to your server address.</span>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<!-- Manual Discover Modal -->
<div class="modal fade" id="manualDiscoverModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content glass-card p-0" >
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title display-font"><i class="bi bi-plus-circle text-indigo me-2"></i> Register Biometric Device</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('devices.discover') }}" method="POST">
                @csrf
                <div class="modal-body py-4">
                    <div class="mb-3">
                        <label class="form-label text-secondary">Device Serial Number (SN)</label>
                        <input type="text" class="form-control form-control-custom" name="device_serial_number" placeholder="e.g. SN12345678" required>
                        <div class="form-text text-secondary fs-8">Must match the Serial Number shown on the ZKTeco device screen or label.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-secondary">Device Name</label>
                        <input type="text" class="form-control form-control-custom" name="device_name" placeholder="e.g. Main Lobby Face Terminal" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-secondary">Location</label>
                        <input type="text" class="form-control form-control-custom" name="location" placeholder="e.g. Ground Floor Entrance">
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-sm btn-custom-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-custom-primary">Register Device</button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- Edit Modals -->
@foreach($devices as $d)
    <div class="modal fade" id="editModal{{ $d->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content glass-card p-0" >
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title display-font"><i class="bi bi-pencil-square text-info me-2"></i> Edit Biometric Device</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('devices.update', $d->id) }}" method="POST">
                    @csrf
                    <div class="modal-body py-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label text-secondary">Device Name</label>
                                <input type="text" class="form-control form-control-custom" name="device_name" value="{{ $d->device_name }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-secondary">Serial Number</label>
                                <input type="text" class="form-control form-control-custom" name="device_serial_number" value="{{ $d->device_serial_number }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-secondary">Device Model</label>
                                <input type="text" class="form-control form-control-custom" name="device_model" value="{{ $d->device_model }}" placeholder="ZKTeco SenseFace">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-secondary">Firmware Version</label>
                                <input type="text" class="form-control form-control-custom" name="firmware_version" value="{{ $d->firmware_version }}" placeholder="PUSH SDK 2.0">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-secondary">Timezone</label>
                                <input type="text" class="form-control form-control-custom" name="timezone" value="{{ $d->timezone }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-secondary">Status</label>
                                <select class="form-select form-select-custom" name="status" required>
                                    @foreach(['Pending Approval', 'Online', 'Offline', 'Disabled', 'Error'] as $status)
                                        <option value="{{ $status }}" @selected($d->status === $status)>{{ $status }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-secondary">Assign Company</label>
                                <select class="form-select form-select-custom" name="company_id">
                                    <option value="">Unassigned</option>
                                    @foreach($companies as $c)
                                        <option value="{{ $c->id }}" @selected($d->company_id === $c->id)>{{ $c->company_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-secondary">Assign Branch</label>
                                <select class="form-select form-select-custom" name="branch_id">
                                    <option value="">Unassigned</option>
                                    @foreach($branches as $b)
                                        <option value="{{ $b->id }}" @selected($d->branch_id === $b->id)>{{ $b->branch_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label text-secondary">Location</label>
                                <input type="text" class="form-control form-control-custom" name="location" value="{{ $d->location }}" placeholder="e.g. Main Lobby">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-top-0 pt-0">
                        <button type="button" class="btn btn-sm btn-custom-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-sm btn-custom-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endforeach
<!-- Approve Modals (rendered at top-level to prevent Bootstrap backdrop overlay issues) -->
@foreach($devices as $d)
    @if($d->status === 'Pending Approval')
        <div class="modal fade" id="approveModal{{ $d->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content glass-card p-0" >
                    <div class="modal-header border-bottom-0 pb-0">
                        <h5 class="modal-title display-font"><i class="bi bi-shield-check text-warning me-2"></i> Approve Biometric Device</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="{{ route('devices.approve', $d->id) }}" method="POST">
                        @csrf
                        <div class="modal-body py-4">
                            <div class="mb-3">
                                <label class="form-label text-secondary">Device Name</label>
                                <input type="text" class="form-control form-control-custom" name="device_name" value="{{ $d->device_name }}" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-secondary">Assign Company</label>
                                <select class="form-select form-select-custom" name="company_id" required>
                                    <option value="">Select Company</option>
                                    @foreach($companies as $c)
                                        <option value="{{ $c->id }}">{{ $c->company_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-secondary">Assign Branch</label>
                                <select class="form-select form-select-custom" name="branch_id">
                                    <option value="">Select Branch</option>
                                    @foreach($branches as $b)
                                        <option value="{{ $b->id }}">{{ $b->branch_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-secondary">Location</label>
                                <input type="text" class="form-control form-control-custom" name="location" value="{{ $d->location }}" placeholder="e.g. Lobby Entrance">
                            </div>
                        </div>
                        <div class="modal-footer border-top-0 pt-0">
                            <button type="button" class="btn btn-sm btn-custom-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-sm btn-custom-primary">Approve & Link</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
@endforeach

<!-- Delete User from Device Modals -->
@foreach($devices as $d)
    @if($d->status !== 'Disabled')
        <div class="modal fade" id="deleteDeviceUserModal{{ $d->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content glass-card p-0" >
                    <div class="modal-header border-bottom-0 pb-0">
                        <h5 class="modal-title display-font"><i class="bi bi-person-x text-danger me-2"></i> Delete User from Terminal</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="{{ route('devices.command', $d->id) }}" method="POST">
                        @csrf
                        <div class="modal-body py-4">
                            <p class="text-secondary small mb-3">Enter the user's PIN to permanently remove them from <strong>{{ $d->device_name }}</strong>.</p>
                            <div class="mb-3">
                                <label class="form-label text-secondary">User PINs (IDs on device)</label>
                                <textarea class="form-control form-control-custom" name="command_pin" rows="3" required placeholder="e.g. 1001, 1002, 1003&#10;(Separate multiple IDs by comma or space)"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer border-top-0 pt-0">
                            <button type="button" class="btn btn-sm btn-custom-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-sm btn-danger">Queue Deletion Command</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
@endforeach

<!-- Scan Devices Modal -->
<div class="modal fade" id="scanDevicesModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content glass-card p-0" >
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title display-font"><i class="bi bi-search text-info me-2"></i> Auto-Scan Local Network</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body py-4">
                <p class="text-secondary fs-7">This will scan the server's local subnet for ZKTeco devices on port 4370.</p>
                <div class="text-center mb-4">
                    <button type="button" class="btn btn-info" id="btnStartScan">
                        <i class="bi bi-radar me-1"></i> Start Scan
                    </button>
                </div>
                
                <div id="scanLoader" class="text-center d-none my-4">
                    <div class="spinner-border text-info" role="status"></div>
                    <p class="text-secondary mt-2 fs-7">Scanning network... This may take a moment.</p>
                </div>

                <div class="table-responsive d-none" id="scanResultsContainer">
                    <table class="table table-dark table-hover mb-0 fs-7">
                        <thead>
                            <tr>
                                <th>IP Address</th>
                                <th>Serial Number</th>
                                <th>Device Name</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody id="scanResultsBody">
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer border-top-0 pt-0">
                <button type="button" class="btn btn-sm btn-custom-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const btnScan = document.getElementById('btnStartScan');
    const scanLoader = document.getElementById('scanLoader');
    const resultsContainer = document.getElementById('scanResultsContainer');
    const resultsBody = document.getElementById('scanResultsBody');

    if(btnScan) {
        btnScan.addEventListener('click', function() {
            btnScan.disabled = true;
            scanLoader.classList.remove('d-none');
            resultsContainer.classList.add('d-none');
            resultsBody.innerHTML = '';

            fetch('{{ route("devices.scan") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => response.json())
            .then(data => {
                btnScan.disabled = false;
                scanLoader.classList.add('d-none');
                resultsContainer.classList.remove('d-none');
                
                if(data.devices && data.devices.length > 0) {
                    data.devices.forEach(device => {
                        resultsBody.innerHTML += `
                            <tr>
                                <td>${device.ip}</td>
                                <td>${device.serial_number}</td>
                                <td>${device.name}</td>
                                <td class="text-end">
                                    <button class="btn btn-sm btn-success btn-add-device" data-sn="${device.serial_number}" data-name="${device.name}">
                                        <i class="bi bi-plus"></i> Add
                                    </button>
                                </td>
                            </tr>
                        `;
                    });

                    // Bind add buttons
                    document.querySelectorAll('.btn-add-device').forEach(btn => {
                        btn.addEventListener('click', function() {
                            const sn = this.getAttribute('data-sn');
                            const name = this.getAttribute('data-name');
                            
                            // Close scan modal and open manual discover modal pre-filled
                            const scanModal = bootstrap.Modal.getInstance(document.getElementById('scanDevicesModal'));
                            scanModal.hide();
                            
                            const manualModal = new bootstrap.Modal(document.getElementById('manualDiscoverModal'));
                            document.querySelector('#manualDiscoverModal input[name="device_serial_number"]').value = sn;
                            document.querySelector('#manualDiscoverModal input[name="device_name"]').value = name;
                            manualModal.show();
                        });
                    });
                } else {
                    resultsBody.innerHTML = '<tr><td colspan="4" class="text-center text-secondary">No ZKTeco devices found on the local network.</td></tr>';
                }
            })
            .catch(error => {
                btnScan.disabled = false;
                scanLoader.classList.add('d-none');
                alert('Error scanning network: ' + error.message);
            });
        });
    }
});
</script>

@endsection
