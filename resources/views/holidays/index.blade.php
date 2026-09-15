@extends('layouts.app')

@section('content')
<div class="row g-4">
    <!-- List of Holidays -->
    <div class="col-lg-8">
        <div class="glass-card">
            <h5 class="mb-4 display-font"><i class="bi bi-calendar-event-fill me-2 text-indigo"></i> Registered Holidays</h5>
            <div class="table-responsive">
                <table class="table custom-table">
                    <thead>
                        <tr>
                            <th>Company</th>
                            <th>Holiday Name</th>
                            <th>Holiday Date</th>
                            <th>Description</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($holidays as $h)
                            <tr>
                                <td class="fw-semibold">{{ $h->company->company_name }}</td>
                                <td>{{ $h->holiday_name }}</td>
                                <td>
                                    <span class="badge bg-indigo text-light">
                                        {{ $h->holiday_date->format('Y-m-d') }}
                                    </span>
                                </td>
                                <td>{{ $h->description ?? 'N/A' }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <button type="button" class="btn btn-sm btn-outline-primary border-0 me-2" data-bs-toggle="modal" data-bs-target="#editModal{{ $h->id }}">
                                            <i class="bi bi-pencil"></i> Edit
                                        </button>
                                        <form action="{{ route('holidays.delete', $h->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this holiday?');" class="m-0">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-danger border-0">
                                                <i class="bi bi-trash"></i> Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-secondary">No holidays registered. Use the panel to register one.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Create Holiday Form -->
    <div class="col-lg-4">
        <div class="glass-card">
            <h5 class="mb-4 display-font"><i class="bi bi-plus-circle-fill me-2 text-indigo"></i> Add Holiday</h5>
            <form action="{{ route('holidays.store') }}" method="POST">
                @csrf
                
                <div class="mb-3">
                    <label class="form-label text-secondary">Company</label>
                    <select class="form-select form-select-custom" name="company_id" required>
                        <option value="">Select Company</option>
                        @foreach($companies as $c)
                            <option value="{{ $c->id }}">{{ $c->company_name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label text-secondary">Holiday Name</label>
                    <input type="text" class="form-control form-control-custom" name="holiday_name" placeholder="e.g. New Year Holiday" required>
                </div>

                <div class="mb-3">
                    <label class="form-label text-secondary">Holiday Date</label>
                    <input type="date" class="form-control form-control-custom" name="holiday_date" required>
                </div>

                <div class="mb-3">
                    <label class="form-label text-secondary">Description (Optional)</label>
                    <textarea class="form-control form-control-custom" name="description" rows="3" placeholder="Holiday description details..."></textarea>
                </div>

                <button type="submit" class="btn btn-custom-primary w-100 mt-2">
                    <i class="bi bi-save me-2"></i> Register Holiday
                </button>
            </form>
        </div>
    </div>
</div>

<!-- Edit Modals Loop (Placed at bottom to prevent z-index/backdrop trapping) -->
@foreach($holidays as $h)
    <div class="modal fade" id="editModal{{ $h->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content glass-card p-0" >
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title display-font"><i class="bi bi-pencil-square me-2 text-indigo"></i> Edit Holiday</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('holidays.update', $h->id) }}" method="POST">
                    @csrf
                    <div class="modal-body text-start">
                        <div class="mb-3">
                            <label class="form-label text-secondary">Company</label>
                            <select class="form-select form-select-custom" name="company_id" required>
                                @foreach($companies as $c)
                                    <option value="{{ $c->id }}" {{ $h->company_id == $c->id ? 'selected' : '' }}>{{ $c->company_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-secondary">Holiday Name</label>
                            <input type="text" class="form-control form-control-custom" name="holiday_name" value="{{ $h->holiday_name }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-secondary">Holiday Date</label>
                            <input type="date" class="form-control form-control-custom" name="holiday_date" value="{{ $h->holiday_date->format('Y-m-d') }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-secondary">Description</label>
                            <textarea class="form-control form-control-custom" name="description" rows="3">{{ $h->description }}</textarea>
                        </div>
                    </div>
                    <div class="modal-footer border-top-0 pt-0">
                        <button type="button" class="btn btn-custom-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-custom-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endforeach
@endsection
