@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-10 col-xl-8">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h4 class="fw-bold m-0 text-light display-font"><i class="bi bi-person-plus-fill me-2 text-indigo"></i> Register New Employee</h4>
                <p class="text-secondary fs-7 m-0">Add employee profile, assign organization, shift schedule & dispatch ADMS biometric commands.</p>
            </div>
            <a href="{{ route('employees') }}" class="btn btn-custom-secondary btn-sm px-3">
                <i class="bi bi-arrow-left me-1"></i> Back to Employee Directory
            </a>
        </div>

        <link href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css" rel="stylesheet">
        <style>
            #cropperImage { max-width: 100%; display: block; }
            .preview-container { text-align: center; margin-top: 10px; }
            #croppedPreview { border-radius: 50%; width: 100px; height: 100px; object-fit: cover; display: none; border: 2px solid var(--primary); margin: 0 auto; }
        </style>

        <div class="glass-card p-4">
            <form action="{{ route('employees.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <!-- Section 1: Organization & ID Setup -->
                <div class="mb-4">
                    <h6 class="text-indigo fs-7 fw-bold uppercase-track mb-3 pb-1 border-bottom border-indigo-subtle">
                        <i class="bi bi-building me-1"></i> 1. Organization & ID Setup
                    </h6>
                    <div class="mb-3">
                        <label class="form-label text-secondary fs-8 fw-semibold">Company *</label>
                        <select class="form-select form-select-custom" name="company_id" id="create_company_id" required>
                            <option value="">Select Company</option>
                            @foreach($companies as $c)
                                <option value="{{ $c->id }}" data-code="{{ $c->company_code }}">{{ $c->company_name }} ({{ $c->company_code }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-secondary fs-8 fw-semibold">Employee ID / Biometric User ID *</label>
                        <div class="input-group">
                            <span class="input-group-text bg-dark-subtle border-secondary text-indigo fw-bold" id="company_prefix_addon">P1-</span>
                            <input type="text" class="form-control form-control-custom" name="employee_id" id="create_employee_id" placeholder="e.g. 1001 or P1-1001" required>
                        </div>
                        <div class="form-text text-secondary fs-8">Auto-prefixed based on company code. Numeric portion will be used as Hardware Sync PIN.</div>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label text-secondary fs-8 fw-semibold">Branch</label>
                            <select class="form-select form-select-custom" name="branch_id">
                                <option value="">Select Branch</option>
                                @foreach($branches as $b)
                                    <option value="{{ $b->id }}">{{ $b->branch_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label text-secondary fs-8 fw-semibold">Department *</label>
                            <select class="form-select form-select-custom" name="department_id" required>
                                <option value="">Select Dept</option>
                                @foreach($departments as $d)
                                    <option value="{{ $d->id }}">{{ $d->department_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-secondary fs-8 fw-semibold">Designation</label>
                        <select name="designation" class="form-select form-control-custom">
                            <option value="">Select Designation</option>
                            @foreach($designations as $designation)
                                <option value="{{ $designation->title }}">{{ $designation->title }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Section 2: Personal Information -->
                <div class="mb-4">
                    <h6 class="text-indigo fs-7 fw-bold uppercase-track mb-3 pb-1 border-bottom border-indigo-subtle">
                        <i class="bi bi-person me-1"></i> 2. Personal Information
                    </h6>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label text-secondary fs-8 fw-semibold">First Name *</label>
                            <input type="text" class="form-control form-control-custom" name="first_name" placeholder="John" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label text-secondary fs-8 fw-semibold">Last Name *</label>
                            <input type="text" class="form-control form-control-custom" name="last_name" placeholder="Doe" required>
                        </div>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label text-secondary fs-8 fw-semibold">Gender</label>
                            <select class="form-select form-select-custom" name="gender">
                                <option value="">Select</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label text-secondary fs-8 fw-semibold">Date of Birth</label>
                            <input type="date" class="form-control form-control-custom" name="date_of_birth">
                        </div>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label text-secondary fs-8 fw-semibold">NIC Number</label>
                            <input type="text" class="form-control form-control-custom" name="nic" placeholder="e.g. 991234567V">
                        </div>
                        <div class="col-6">
                            <label class="form-label text-secondary fs-8 fw-semibold">Mobile Number</label>
                            <input type="text" class="form-control form-control-custom" name="mobile_number" placeholder="e.g. 0771234567">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-secondary fs-8 fw-semibold">Email Address</label>
                        <input type="email" class="form-control form-control-custom" name="email" placeholder="john.doe@company.com">
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-secondary fs-8 fw-semibold">Profile Photo</label>
                        <input type="file" class="form-control form-control-custom" name="profile_photo" accept=".jpg,.jpeg">
                        <small class="form-text text-muted">
                            <span class="badge bg-warning text-dark"><i class="fas fa-exclamation-triangle"></i> Note</span> Only <strong>JPG / JPEG</strong> images are supported by fingerprint devices.
                        </small>
                        <small class="text-muted d-block mt-1">Image will be automatically optimized for Face Recognition.</small>
                    </div>
                </div>

                <!-- Section 3: Attendance, Shifts & ADMS Biometric Terminal Settings -->
                <div class="mb-4">
                    <h6 class="text-indigo fs-7 fw-bold uppercase-track mb-3 pb-1 border-bottom border-indigo-subtle">
                        <i class="bi bi-cpu me-1"></i> 3. Shifts & Biometrics Setup
                    </h6>
                    
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label text-secondary fs-8 fw-semibold">Primary Shift</label>
                            <select class="form-select form-select-custom" name="shift_id">
                                <option value="">Assign Shift</option>
                                @foreach($shifts as $s)
                                    <option value="{{ $s->id }}">{{ $s->shift_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label text-secondary fs-8 fw-semibold">Secondary Shift</label>
                            <select class="form-select form-select-custom" name="secondary_shift_id">
                                <option value="">None</option>
                                @foreach($shifts as $s)
                                    <option value="{{ $s->id }}">{{ $s->shift_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label text-secondary fs-8 fw-semibold">Default Work Mode</label>
                            <select class="form-select form-select-custom" name="work_mode" required>
                                <option value="NORMAL">NORMAL (Office Based)</option>
                                <option value="WFH">WFH (Permanent Remote)</option>
                                <option value="HYBRID">HYBRID (Flexible / Both)</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label text-secondary fs-8 fw-semibold">Web Punch Access</label>
                            <div class="form-check form-switch mt-1">
                                <input class="form-check-input" type="checkbox" name="allow_remote_punch" value="1" id="createAllowRemotePunch">
                                <label class="form-check-label text-secondary fs-8" for="createAllowRemotePunch">Allow Remote Punch</label>
                            </div>
                        </div>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label text-secondary fs-8 fw-semibold">RFID Card Number</label>
                            <input type="text" class="form-control form-control-custom" name="card_number" placeholder="RFID Card #">
                        </div>
                        <div class="col-6">
                            <label class="form-label text-secondary fs-8 fw-semibold">Terminal Privilege</label>
                            <select class="form-select form-select-custom" name="privilege">
                                <option value="0">Normal User (0)</option>
                                <option value="14">Super Admin (14)</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-secondary fs-8 fw-semibold">Assign Biometric Terminals</label>
                        <select class="form-select form-select-custom" name="device_ids[]" multiple style="height: 110px;">
                            @forelse($devices as $dev)
                                <option value="{{ $dev->id }}">
                                    {{ $dev->device_name }} ({{ $dev->device_serial_number }}) - {{ $dev->company ? $dev->company->company_code : 'All' }}
                                </option>
                            @empty
                                <option value="" disabled>No terminals registered</option>
                            @endforelse
                        </select>
                        <div class="form-text text-secondary fs-8">Hold Ctrl/Cmd to assign multiple devices.</div>
                    </div>
                </div>

                <!-- Section 4: Web Portal Account & Business Role -->
                <div class="mb-4 mt-4">
                    <h6 class="text-indigo fs-7 fw-bold uppercase-track mb-3 pb-1 border-bottom border-indigo-subtle">
                        <i class="bi bi-shield-lock me-1"></i> 4. Portal Account & Business Role
                    </h6>
                    <div class="glass-card p-3 bg-light bg-opacity-50">
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" name="create_account" value="1" id="createAccountToggle">
                            <label class="form-check-label text-dark fw-bold" for="createAccountToggle">Generate Web Portal Account</label>
                            <div class="form-text fs-8 text-secondary mt-1">If enabled, an account will be created using the provided Email Address. The username will be their Employee ID.</div>
                        </div>

                        <div id="accountFields" style="display: none;">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label text-secondary fs-8 fw-semibold">Business Role</label>
                                    <select class="form-select form-select-custom" name="role">
                                        <option value="Employee">Standard Employee</option>
                                        @foreach($roles as $r)
                                            @if($r->name !== 'Employee')
                                                <option value="{{ $r->name }}">{{ $r->name }} ({{ $r->category }})</option>
                                            @endif
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-secondary fs-8 fw-semibold">Initial Password</label>
                                    <input type="password" class="form-control form-control-custom" name="password" placeholder="Leave blank to use 'password123'">
                                    <div class="form-text fs-8 text-secondary mt-1">Employee will be forced to change this upon first login.</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex align-items-center justify-content-end gap-2">
                    <a href="{{ route('employees') }}" class="btn btn-custom-secondary px-4">Cancel</a>
                    <button type="submit" class="btn btn-custom-primary px-4 py-2 fw-semibold">
                        <i class="bi bi-check-circle me-1"></i> Register & Dispatch ADMS Commands
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const companySelect = document.getElementById('create_company_id');
    const prefixAddon = document.getElementById('company_prefix_addon');
    const empInput = document.getElementById('create_employee_id');
    const accountToggle = document.getElementById('createAccountToggle');
    const accountFields = document.getElementById('accountFields');
    
    if (accountToggle && accountFields) {
        accountToggle.addEventListener('change', function() {
            accountFields.style.display = this.checked ? 'block' : 'none';
        });
    }

    if (companySelect) {
        companySelect.addEventListener('change', function() {
            const selectedOpt = this.options[this.selectedIndex];
            const code = selectedOpt.getAttribute('data-code') || 'P1';
            prefixAddon.textContent = code + '-';

            if (this.value) {
                fetch(`/api/companies/${this.value}/next-employee-id`)
                    .then(res => res.json())
                    .then(data => {
                        if (data.next_id) {
                            empInput.value = data.next_id;
                        }
                    })
                    .catch(err => console.error('Error fetching next employee ID:', err));
            }
        });
    }
});
</script>
@endsection
