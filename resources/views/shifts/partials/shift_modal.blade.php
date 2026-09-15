@php
    $isEdit = isset($shift) && $shift;
    $modalId = $isEdit ? 'editModal'.$shift->id : 'addShiftModal';
    $formAction = $isEdit ? route('shifts.update', $shift->id) : route('shifts.store');
    $title = $isEdit ? 'Edit Shift: '.$shift->shift_name : 'Add New Shift';
    
    // Helper to safely get value
    $val = function($field, $default = '') use ($shift, $isEdit) {
        if (!$isEdit) return $default;
        
        // Handle time fields which might need formatting
        if (in_array($field, ['start_time', 'end_time', 'early_in_threshold', 'late_threshold', 'half_day_threshold', 'first_half_end', 'absent_threshold', 'second_half_start', 'early_out_threshold', 'overtime_start'])) {
            return $shift->$field ? \Carbon\Carbon::parse($shift->$field)->format('H:i') : '';
        }
        
        return $shift->$field ?? $default;
    };
@endphp

<div class="modal fade shift-modal" id="{{ $modalId }}" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content glass-card p-0" style="background: var(--card-bg); border: 1px solid var(--border-color);">
            <div class="modal-header border-bottom" style="border-color: var(--border-color) !important;">
                <h5 class="modal-title display-font text-white"><i class="bi bi-clock-history me-2 text-indigo"></i> {{ $title }}</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <form action="{{ $formAction }}" method="POST" id="shiftForm_{{ $modalId }}" class="shift-form">
                @csrf
                <div class="modal-body p-0">
                    <div class="d-flex align-items-start flex-column flex-md-row">
                        <!-- Vertical Tabs -->
                        <div class="nav flex-row flex-md-column nav-pills me-0 p-3 w-100 w-md-auto" id="v-pills-tab-{{ $modalId }}" role="tablist" aria-orientation="vertical" style="min-width: 250px; background: rgba(0,0,0,0.2); min-height: 100%; border-right: 1px solid var(--border-color); overflow-x: auto;">
                            <button class="nav-link text-start active mb-2" id="v-pills-basic-tab-{{ $modalId }}" data-bs-toggle="pill" data-bs-target="#v-pills-basic-{{ $modalId }}" type="button" role="tab" style="color: var(--text-muted);">
                                <i class="bi bi-info-circle me-2"></i> Basic Details
                            </button>
                            <button class="nav-link text-start mb-2" id="v-pills-checkin-tab-{{ $modalId }}" data-bs-toggle="pill" data-bs-target="#v-pills-checkin-{{ $modalId }}" type="button" role="tab" style="color: var(--text-muted);">
                                <i class="bi bi-box-arrow-in-right me-2"></i> Check-In Rules
                            </button>
                            <button class="nav-link text-start mb-2" id="v-pills-checkout-tab-{{ $modalId }}" data-bs-toggle="pill" data-bs-target="#v-pills-checkout-{{ $modalId }}" type="button" role="tab" style="color: var(--text-muted);">
                                <i class="bi bi-box-arrow-right me-2"></i> Check-Out Rules
                            </button>
                            <button class="nav-link text-start mb-2" id="v-pills-breaks-tab-{{ $modalId }}" data-bs-toggle="pill" data-bs-target="#v-pills-breaks-{{ $modalId }}" type="button" role="tab" style="color: var(--text-muted);">
                                <i class="bi bi-cup-hot me-2"></i> Break Management
                            </button>
                            <button class="nav-link text-start mb-2" id="v-pills-ot-tab-{{ $modalId }}" data-bs-toggle="pill" data-bs-target="#v-pills-ot-{{ $modalId }}" type="button" role="tab" style="color: var(--text-muted);">
                                <i class="bi bi-cash-coin me-2"></i> Overtime
                            </button>
                            <button class="nav-link text-start mb-2" id="v-pills-preview-tab-{{ $modalId }}" data-bs-toggle="pill" data-bs-target="#v-pills-preview-{{ $modalId }}" type="button" role="tab" style="color: var(--text-muted);">
                                <i class="bi bi-eye me-2"></i> Visual Preview
                            </button>
                        </div>
                        
                        <!-- Tab Content -->
                        <div class="tab-content flex-grow-1 p-4 w-100" id="v-pills-tabContent-{{ $modalId }}" style="min-height: 500px; overflow-y: auto;">
                            
                            <!-- 1. BASIC -->
                            <div class="tab-pane fade show active" id="v-pills-basic-{{ $modalId }}" role="tabpanel">
                                <h5 class="text-white mb-4">Basic Shift Details</h5>
                                <div class="row mb-3">
                                    <div class="col-md-8">
                                        <label class="form-label text-secondary">Shift Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control form-control-custom" name="shift_name" value="{{ $val('shift_name') }}" required>
                                    </div>
                                    @if($isEdit)
                                    <div class="col-md-4">
                                        <label class="form-label text-secondary">Status</label>
                                        <select class="form-select form-select-custom" name="status">
                                            <option value="Active" {{ $val('status') === 'Active' ? 'selected' : '' }}>Active</option>
                                            <option value="Inactive" {{ $val('status') === 'Inactive' ? 'selected' : '' }}>Inactive</option>
                                        </select>
                                    </div>
                                    @endif
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label text-secondary">Start Time <span class="text-danger">*</span></label>
                                        <input type="time" class="form-control form-control-custom time-input start-time-input" name="start_time" value="{{ $val('start_time') }}" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label text-secondary">End Time <span class="text-danger">*</span></label>
                                        <input type="time" class="form-control form-control-custom time-input end-time-input" name="end_time" value="{{ $val('end_time') }}" required>
                                    </div>
                                </div>
                                
                                <div class="mb-4 form-check form-switch">
                                    <!-- Add hidden input so unchecked checkbox still submits 0 -->
                                    <input type="hidden" name="is_cross_midnight" value="0">
                                    <input class="form-check-input time-input" type="checkbox" role="switch" name="is_cross_midnight" value="1" {{ $val('is_cross_midnight') ? 'checked' : '' }} id="cross_midnight_{{ $modalId }}" style="background-color: rgba(15, 23, 42, 0.6); border-color: var(--border-color);">
                                    <label class="form-check-label text-white" for="cross_midnight_{{ $modalId }}">Crosses Midnight</label>
                                    <div class="form-text text-secondary mt-0">Enable if the end time is on the following day (e.g., 22:00 to 06:00).</div>
                                </div>
                                
                                <div class="p-3 rounded" style="background: rgba(255,255,255,0.05); border: 1px solid var(--border-color);">
                                    <label class="form-label text-white mb-2">Expected Work Minutes</label>
                                    <div class="d-flex flex-wrap gap-3 align-items-center mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input calc-toggle auto-calc" type="radio" name="auto_calc_{{ $modalId }}" id="auto_calc_{{ $modalId }}" value="1" checked>
                                            <label class="form-check-label text-secondary" for="auto_calc_{{ $modalId }}">
                                                Auto Calculate (from Start/End minus Unpaid Breaks)
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input calc-toggle manual-calc" type="radio" name="auto_calc_{{ $modalId }}" id="manual_calc_{{ $modalId }}" value="0">
                                            <label class="form-check-label text-secondary" for="manual_calc_{{ $modalId }}">
                                                Manual Override
                                            </label>
                                        </div>
                                    </div>
                                    <input type="number" class="form-control form-control-custom work-minutes-input" name="expected_work_minutes" value="{{ $val('expected_work_minutes') }}" readonly>
                                </div>
                            </div>
                            
                            <!-- 2. CHECK-IN RULES -->
                            <div class="tab-pane fade" id="v-pills-checkin-{{ $modalId }}" role="tabpanel">
                                <h5 class="text-white mb-4">Check-In Rules</h5>
                                
                                <div class="mb-4 border-start border-3 border-primary ps-3">
                                    <label class="form-label text-white">Grace Period (Minutes) <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control form-control-custom mb-1" name="grace_period" value="{{ $val('grace_period', 15) }}" min="0" required>
                                    <small class="text-secondary d-block">Time allowed after shift start before "Late In" is applied.</small>
                                </div>
                                
                                <div class="row mb-4 border-start border-3 border-info ps-3">
                                    <div class="col-12 mb-2">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input toggle-section" type="checkbox" role="switch" id="enable_early_in_{{ $modalId }}" {{ $val('early_in_threshold') ? 'checked' : '' }} style="background-color: rgba(15, 23, 42, 0.6); border-color: var(--border-color);">
                                            <label class="form-check-label text-white" for="enable_early_in_{{ $modalId }}">Enable Early In Tracking</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6 section-target">
                                        <label class="form-label text-secondary">Early In Threshold</label>
                                        <input type="time" class="form-control form-control-custom time-input time-early-in" name="early_in_threshold" value="{{ $val('early_in_threshold') }}">
                                        <small class="text-secondary d-block">Defines how far before shift start an arrival is classified as Early In.</small>
                                    </div>
                                </div>
                                
                                <div class="row border-start border-3 border-warning ps-3">
                                    <div class="col-12 mb-2">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input toggle-section" type="checkbox" role="switch" id="enable_late_in_{{ $modalId }}" {{ $val('late_threshold') || $val('half_day_threshold') ? 'checked' : '' }} style="background-color: rgba(15, 23, 42, 0.6); border-color: var(--border-color);">
                                            <label class="form-check-label text-white" for="enable_late_in_{{ $modalId }}">Advanced Late Rules</label>
                                        </div>
                                    </div>
                                    <div class="col-12 section-target">
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="form-label text-secondary">Latest Full-Day Arrival</label>
                                                <input type="time" class="form-control form-control-custom time-input time-late" name="late_threshold" value="{{ $val('late_threshold') }}">
                                                <small class="text-secondary d-block">Latest arrival time that can still qualify for full-day attendance.</small>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label text-secondary">Latest Half-Day Arrival</label>
                                                <input type="time" class="form-control form-control-custom time-input" name="half_day_threshold" value="{{ $val('half_day_threshold') }}">
                                                <small class="text-secondary d-block">Latest arrival time permitted for half-day attendance.</small>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label text-secondary">Second Half Starts</label>
                                                <input type="time" class="form-control form-control-custom time-input" name="second_half_start" value="{{ $val('second_half_start') }}">
                                                <small class="text-secondary d-block">Time from which an arrival is considered a second-half arrival.</small>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label text-secondary">Absence Evaluation</label>
                                                <input type="time" class="form-control form-control-custom time-input" name="absent_threshold" value="{{ $val('absent_threshold') }}">
                                                <small class="text-secondary d-block">Arrival after this time is marked as Absent.</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- 3. CHECK-OUT RULES -->
                            <div class="tab-pane fade" id="v-pills-checkout-{{ $modalId }}" role="tabpanel">
                                <h5 class="text-white mb-4">Check-Out Rules</h5>
                                
                                <!-- 1. Normal End Time -->
                                <div class="mb-4 border-start border-3 border-success ps-3">
                                    <label class="form-label text-white">Normal End Time</label>
                                    <input type="time" class="form-control form-control-custom display-end-time" value="{{ $val('end_time') }}" disabled>
                                    <small class="text-secondary d-block">Mirrors the Shift End Time set in Basic Details.</small>
                                </div>
                                
                                <!-- 2. Early Out Tracking -->
                                <div class="row mb-4 border-start border-3 border-info ps-3">
                                    <div class="col-12 mb-2">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input toggle-section" type="checkbox" role="switch" id="enable_early_out_{{ $modalId }}" {{ $val('early_out_threshold') || (int)$val('early_out_grace') > 0 ? 'checked' : '' }}>
                                            <label class="form-check-label text-white" for="enable_early_out_{{ $modalId }}">Enable Early Out Tracking</label>
                                        </div>
                                    </div>
                                    <div class="col-12 section-target">
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="form-label text-secondary">Early Out Grace (Minutes)</label>
                                                <input type="number" class="form-control form-control-custom" name="early_out_grace" value="{{ $val('early_out_grace', 0) }}" min="0">
                                                <small class="text-secondary d-block">Grace window allowed before Normal End Time where departure is not flagged.</small>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label text-secondary">Early Out Threshold</label>
                                                <input type="time" class="form-control form-control-custom time-input time-early-out" name="early_out_threshold" value="{{ $val('early_out_threshold') }}">
                                                <small class="text-secondary d-block">Earliest departure time tracked as Early Out before First-Half cutoff.</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- 3. Missing Checkout Handling -->
                                <div class="row border-start border-3 border-warning ps-3">
                                    <div class="col-12 mb-2">
                                        <h6 class="text-white m-0 d-flex align-items-center">
                                            <i class="bi bi-box-arrow-right text-warning me-2"></i> Missing Checkout Handling
                                        </h6>
                                    </div>
                                    <div class="col-12">
                                        <div class="p-3 rounded" style="background: rgba(255,255,255,0.03); border: 1px solid var(--border-color);">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <span class="text-white fw-semibold small">Engine Detection Policy</span>
                                                <span class="badge bg-warning text-dark"><i class="bi bi-shield-exclamation me-1"></i> Automatic Incomplete</span>
                                            </div>
                                            <p class="text-secondary small mb-2">
                                                Any shift session with a valid <strong class="text-white">Check-In</strong> punch but no corresponding <strong class="text-white">Check-Out</strong> punch is automatically flagged with <span class="badge bg-danger">Missing Out</span> and assigned the attendance status <strong class="text-warning">INCOMPLETE</strong>.
                                            </p>
                                            <div class="text-secondary small">
                                                <i class="bi bi-info-circle text-info me-1"></i> <strong class="text-info">Precedence Rule:</strong> Missing checkout status strictly overrides partial-day classifications (Half-Day / First-Half / Second-Half) and prevents premature working hours calculation.
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- 4. BREAKS -->
                            <div class="tab-pane fade" id="v-pills-breaks-{{ $modalId }}" role="tabpanel">
                                <div class="d-flex justify-content-between align-items-center mb-4">
                                    <h5 class="text-white m-0">Shift Break Management</h5>
                                    <button type="button" class="btn btn-sm btn-outline-success add-break-btn"><i class="bi bi-plus"></i> Add Break</button>
                                </div>
                                
                                <div class="breaks-container" id="breaks_container_{{ $modalId }}">
                                    <!-- Dynamic rows will be injected here via JS -->
                                    <div class="text-center text-muted no-breaks-msg p-4 border border-secondary border-dashed rounded mb-3" style="border-style: dashed !important;">
                                        No breaks configured. Click "Add Break" to create one.
                                    </div>
                                </div>
                                
                                <!-- Hidden template for new rows -->
                                <template class="break-template">
                                    <div class="break-row card bg-dark border-secondary mb-3 p-3 position-relative">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <h6 class="text-white m-0 break-title">BREAK</h6>
                                            <button type="button" class="btn btn-sm btn-outline-danger remove-break-btn border-0"><i class="bi bi-trash"></i> Remove</button>
                                        </div>
                                        <input type="hidden" class="break-id-input" name="breaks[__INDEX__][id]" value="">
                                        <div class="row g-2">
                                            <div class="col-md-3">
                                                <label class="form-label text-secondary" style="font-size: 0.8rem;">Break Name</label>
                                                <input type="text" class="form-control form-control-sm form-control-custom break-name-input" name="breaks[__INDEX__][name]" placeholder="e.g. Lunch" required>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label text-secondary" style="font-size: 0.8rem;">Start Time</label>
                                                <input type="time" class="form-control form-control-sm form-control-custom break-time-input break-start" name="breaks[__INDEX__][start_time]" required>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label text-secondary" style="font-size: 0.8rem;">End Time</label>
                                                <input type="time" class="form-control form-control-sm form-control-custom break-time-input break-end" name="breaks[__INDEX__][end_time]" required>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label text-secondary" style="font-size: 0.8rem;">Duration</label>
                                                <input type="text" class="form-control form-control-sm form-control-custom break-duration" readonly value="0 min">
                                            </div>
                                        </div>
                                        <div class="mt-3 form-check form-switch">
                                            <input type="hidden" name="breaks[__INDEX__][is_paid]" value="0">
                                            <input class="form-check-input break-paid-toggle" type="checkbox" role="switch" name="breaks[__INDEX__][is_paid]" value="1" checked style="background-color: rgba(15, 23, 42, 0.6); border-color: var(--border-color);">
                                            <label class="form-check-label text-white" style="font-size: 0.9rem;">Paid Break</label>
                                            <div class="form-text text-secondary mt-0" style="font-size: 0.75rem;">If unchecked, duration is automatically deducted from Expected Working Hours.</div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                            
                            <!-- 5. OVERTIME -->
                            <div class="tab-pane fade" id="v-pills-ot-{{ $modalId }}" role="tabpanel">
                                <h5 class="text-white mb-4">Overtime Configuration</h5>
                                
                                <div class="mb-4 border-start border-3 border-warning ps-3">
                                    <div class="form-check form-switch mb-3">
                                        <input type="hidden" name="overtime_eligibility" value="0">
                                        <input class="form-check-input toggle-section" type="checkbox" role="switch" id="ot_eligibility_{{ $modalId }}" name="overtime_eligibility" value="1" {{ $val('overtime_eligibility') ? 'checked' : '' }} style="background-color: rgba(15, 23, 42, 0.6); border-color: var(--border-color);">
                                        <label class="form-check-label text-white" for="ot_eligibility_{{ $modalId }}">OT Eligibility (Enable)</label>
                                        <div class="form-text text-secondary mt-0">Allows employees on this shift to be flagged as eligible for overtime.</div>
                                    </div>
                                    
                                    <div class="row g-3 section-target mt-2">
                                        <div class="col-md-6">
                                            <label class="form-label text-secondary">OT Start Time</label>
                                            <input type="time" class="form-control form-control-custom time-input time-ot" name="overtime_start" value="{{ $val('overtime_start') }}">
                                            <small class="text-secondary d-block">Time after which work is considered overtime.</small>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label text-secondary">Minimum OT Minutes</label>
                                            <input type="number" class="form-control form-control-custom" name="minimum_overtime_minutes" value="{{ $val('minimum_overtime_minutes', 0) }}" min="0">
                                            <small class="text-secondary d-block">Minimum minutes required to grant OT.</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- 6. PREVIEW -->
                            <div class="tab-pane fade" id="v-pills-preview-{{ $modalId }}" role="tabpanel">
                                <h5 class="text-white mb-4">Visual Shift Preview</h5>
                                <p class="text-secondary">This timeline updates automatically based on your configured rules.</p>
                                
                                <div class="preview-timeline p-4 rounded" style="background: rgba(0,0,0,0.3); border: 1px solid var(--border-color);">
                                    <div class="d-flex flex-column gap-3 timeline-container" id="timeline_container_{{ $modalId }}">
                                        <!-- Timeline populated by JS -->
                                        <div class="text-center text-muted timeline-placeholder">Enter Start and End times to view preview.</div>
                                    </div>
                                </div>
                            </div>
                            
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer border-top p-3" style="border-color: var(--border-color) !important;">
                    <button type="button" class="btn btn-custom-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-custom-primary"><i class="bi bi-save me-2"></i> Save Shift</button>
                </div>
            </form>
        </div>
    </div>
</div>

@if($isEdit)
    <script>
        // Store existing breaks data securely for JS init
        window.existingBreaks_{{ $shift->id }} = {!! json_encode($shift->breaks->map(function($b) { 
            return [
                'id' => $b->id,
                'name' => $b->name,
                'start_time' => \Carbon\Carbon::parse($b->start_time)->format('H:i'),
                'end_time' => \Carbon\Carbon::parse($b->end_time)->format('H:i'),
                'is_paid' => $b->is_paid
            ];
        })) !!};
    </script>
@endif
