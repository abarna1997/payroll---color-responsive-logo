@extends('layouts.app')

@push('styles')
<style>
    .form-control-custom:disabled, .form-control:disabled {
        background-color: rgba(241, 245, 249, 0.4) !important;
        color: var(--text-muted) !important;
        cursor: not-allowed !important;
        border-color: rgba(203, 213, 225, 0.3) !important;
    }
    .form-check-input:checked {
        background-color: #4f46e5 !important;
        border-color: #4f46e5 !important;
    }
</style>
@endpush

@section('content')
<div class="row g-4">
    <!-- List of Shifts -->
    <div class="col-lg-12">
        <div class="glass-card">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="m-0 display-font"><i class="bi bi-clock-history me-2 text-indigo"></i> Registered Shifts</h5>
                <button type="button" class="btn btn-custom-primary" data-bs-toggle="modal" data-bs-target="#addShiftModal">
                    <i class="bi bi-plus-circle-fill me-2"></i> Add Shift
                </button>
            </div>
            
            <div class="table-responsive">
                <table class="table custom-table">
                    <thead>
                        <tr>
                            <th>Shift Name</th>
                            <th>Start Time</th>
                            <th>End Time</th>
                            <th>Grace Period</th>
                            <th>OT Eligible</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($shifts as $s)
                            <tr>
                                <td class="fw-semibold">{{ $s->shift_name }}</td>
                                <td>{{ Carbon\Carbon::parse($s->start_time)->format('H:i') }}</td>
                                <td>{{ Carbon\Carbon::parse($s->end_time)->format('H:i') }}</td>
                                <td>{{ $s->grace_period }} Mins</td>
                                <td>
                                    <span class="badge {{ $s->overtime_eligibility ? 'bg-success' : 'bg-secondary' }}">
                                        {{ $s->overtime_eligibility ? 'Yes' : 'No' }}
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <button type="button" class="btn btn-sm btn-outline-primary border-0 me-2" data-bs-toggle="modal" data-bs-target="#editModal{{ $s->id }}">
                                            <i class="bi bi-pencil"></i> Edit
                                        </button>
                                        <form action="{{ route('shifts.delete', $s->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this shift?');" class="m-0">
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
                                <td colspan="6" class="text-center text-secondary">No shifts configured yet. Add one to schedule employees.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add Shift Modal -->
@include('shifts.partials.shift_modal', ['shift' => null])

<!-- Edit Shift Modals -->
@foreach($shifts as $s)
    @include('shifts.partials.shift_modal', ['shift' => $s])
@endforeach

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    
    // Initialize logic for each modal independently
    const modals = document.querySelectorAll('.shift-modal');
    modals.forEach(modal => {
        const modalId = modal.id;
        
        // --- 1. Toggles for Advanced Sections ---
        const toggles = modal.querySelectorAll('.toggle-section');
        toggles.forEach(toggle => {
            const wrapper = toggle.closest('.row, .border-start');
            const targetInputs = wrapper.querySelectorAll('.section-target input');
            
            const syncToggle = () => {
                const isChecked = toggle.checked;
                const sectionTarget = wrapper.querySelector('.section-target');
                if (sectionTarget) {
                    if (isChecked) {
                        sectionTarget.classList.remove('opacity-50');
                        sectionTarget.style.pointerEvents = 'auto';
                    } else {
                        sectionTarget.classList.add('opacity-50');
                        sectionTarget.style.pointerEvents = 'none';
                    }
                }
                targetInputs.forEach(input => {
                    input.disabled = !isChecked;
                });
                updatePreview();
            };
            
            toggle.addEventListener('change', syncToggle);
            syncToggle(); // init
        });
        
        // --- 2. Auto Calculate vs Manual Expected Work Minutes ---
        const calcRadios = modal.querySelectorAll('.calc-toggle');
        const expectedMinsInput = modal.querySelector('.work-minutes-input');
        
        const syncCalc = () => {
            const isAuto = modal.querySelector('.auto-calc').checked;
            expectedMinsInput.readOnly = isAuto;
            if (isAuto) {
                recalculateExpectedMinutes();
            }
        };
        
        calcRadios.forEach(radio => radio.addEventListener('change', syncCalc));
        
        // Inputs that trigger recalculation
        const timeTriggers = modal.querySelectorAll('.start-time-input, .end-time-input');
        timeTriggers.forEach(el => {
            el.addEventListener('change', () => {
                // Sync the readonly end time in check-out rules
                const endInput = modal.querySelector('.end-time-input').value;
                const displayEnd = modal.querySelector('.display-end-time');
                if (displayEnd) displayEnd.value = endInput;
                
                recalculateExpectedMinutes();
                updatePreview();
            });
        });
        
        modal.querySelector('input[name="is_cross_midnight"]').addEventListener('change', () => {
            recalculateExpectedMinutes();
            updatePreview();
        });
        
        // --- 3. Break Management ---
        const addBreakBtn = modal.querySelector('.add-break-btn');
        const breaksContainer = modal.querySelector('.breaks-container');
        const noBreaksMsg = modal.querySelector('.no-breaks-msg');
        const template = modal.querySelector('.break-template');
        let breakIndex = 100;
        
        const updateBreaksUI = () => {
            const breakRows = breaksContainer.querySelectorAll('.break-row');
            if (breakRows.length > 0) {
                if(noBreaksMsg) noBreaksMsg.style.display = 'none';
            } else {
                if(noBreaksMsg) noBreaksMsg.style.display = 'block';
            }
            recalculateExpectedMinutes();
            updatePreview();
        };
        
        const addBreakRow = (data = null) => {
            breakIndex++;
            const clone = template.content.cloneNode(true);
            const row = clone.querySelector('.break-row');
            
            // Replace __INDEX__
            row.innerHTML = row.innerHTML.replace(/__INDEX__/g, breakIndex);
            
            if (data) {
                row.querySelector('.break-id-input').value = data.id || '';
                row.querySelector('.break-name-input').value = data.name || '';
                row.querySelector('.break-start').value = data.start_time || '';
                row.querySelector('.break-end').value = data.end_time || '';
                if (!data.is_paid) {
                    row.querySelector('.break-paid-toggle').checked = false;
                }
            }
            
            // Bind remove
            row.querySelector('.remove-break-btn').addEventListener('click', function() {
                row.remove();
                updateBreaksUI();
            });
            
            // Bind recalc on time change
            const timeInputs = row.querySelectorAll('.break-time-input, .break-paid-toggle');
            timeInputs.forEach(input => {
                input.addEventListener('change', () => {
                    updateBreakDuration(row);
                    recalculateExpectedMinutes();
                    updatePreview();
                });
            });
            
            breaksContainer.appendChild(clone);
            if(data) updateBreakDuration(breaksContainer.lastElementChild);
            updateBreaksUI();
        };
        
        addBreakBtn.addEventListener('click', () => addBreakRow());
        
        function updateBreakDuration(row) {
            const start = row.querySelector('.break-start').value;
            const end = row.querySelector('.break-end').value;
            const durInput = row.querySelector('.break-duration');
            
            if (start && end) {
                const s = start.split(':');
                const e = end.split(':');
                let startMins = parseInt(s[0])*60 + parseInt(s[1]);
                let endMins = parseInt(e[0])*60 + parseInt(e[1]);
                
                let diff = endMins - startMins;
                if (diff < 0) diff += 24*60; // Assumes cross midnight if end < start
                
                durInput.value = diff + ' min';
                return diff;
            }
            durInput.value = '0 min';
            return 0;
        }
        
        // --- Init Existing Breaks (For Edit Modals) ---
        const shiftIdMatch = modalId.match(/editModal(\d+)/);
        if (shiftIdMatch) {
            const shiftId = shiftIdMatch[1];
            if (window['existingBreaks_' + shiftId]) {
                window['existingBreaks_' + shiftId].forEach(b => addBreakRow(b));
            }
        }
        
        // --- Calculation Logic ---
        function recalculateExpectedMinutes() {
            const isAuto = modal.querySelector('.auto-calc').checked;
            if (!isAuto) return;
            
            const start = modal.querySelector('.start-time-input').value;
            const end = modal.querySelector('.end-time-input').value;
            const cross = modal.querySelector('input[name="is_cross_midnight"]').checked;
            
            if (start && end) {
                const s = start.split(':');
                const e = end.split(':');
                let startMins = parseInt(s[0])*60 + parseInt(s[1]);
                let endMins = parseInt(e[0])*60 + parseInt(e[1]);
                
                let diff = endMins - startMins;
                if (cross && diff < 0) {
                    diff += 24*60;
                } else if (!cross && diff < 0) {
                    // Invalid, but let's just do abs
                    diff = Math.abs(diff);
                }
                
                // Subtract unpaid breaks
                const breakRows = breaksContainer.querySelectorAll('.break-row');
                breakRows.forEach(row => {
                    const isPaid = row.querySelector('.break-paid-toggle').checked;
                    if (!isPaid) {
                        diff -= updateBreakDuration(row);
                    }
                });
                
                if (diff < 0) diff = 0;
                expectedMinsInput.value = diff;
            }
        }
        
        // --- 4. Visual Preview ---
        const timelineContainer = modal.querySelector('.timeline-container');
        function updatePreview() {
            if(!timelineContainer) return;
            
            const start = modal.querySelector('.start-time-input').value;
            const end = modal.querySelector('.end-time-input').value;
            if (!start || !end) {
                timelineContainer.innerHTML = '<div class="text-center text-muted">Enter Start and End times to view preview.</div>';
                return;
            }
            
            let html = '';
            
            // Function to generate a block
            const block = (time, label, colorClass, icon) => {
                if(!time) return '';
                return `
                <div class="d-flex align-items-center">
                    <div style="width: 60px; font-weight: bold; color: var(--text-muted);">${time}</div>
                    <div class="mx-3" style="width: 2px; height: 30px; background: var(--border-color); position: relative;">
                        <div class="bg-${colorClass} rounded-circle" style="width: 12px; height: 12px; position: absolute; left: -5px; top: 9px; box-shadow: 0 0 10px rgba(0,0,0,0.5);"></div>
                    </div>
                    <div class="text-${colorClass} fw-bold" style="letter-spacing: 1px; font-size: 0.85rem;">
                        <i class="bi ${icon} me-2"></i>${label}
                    </div>
                </div>`;
            };
            
            // Connective line
            const line = () => {
                return `
                <div class="d-flex align-items-center">
                    <div style="width: 60px;"></div>
                    <div class="mx-3" style="width: 2px; height: 20px; background: var(--border-color);"></div>
                    <div></div>
                </div>`;
            };

            const addMinutes = (timeStr, mins) => {
                if(!timeStr) return null;
                const pts = timeStr.split(':');
                let m = parseInt(pts[0])*60 + parseInt(pts[1]) + parseInt(mins);
                if(m >= 24*60) m -= 24*60;
                const hh = String(Math.floor(m/60)).padStart(2, '0');
                const mm = String(m%60).padStart(2, '0');
                return hh+':'+mm;
            };
            
            const earlyInEn = modal.querySelector('input[id^="enable_early_in_"]').checked;
            const earlyInTime = modal.querySelector('.time-early-in').value;
            if(earlyInEn && earlyInTime) {
                html += block(earlyInTime, 'EARLY IN TRACKING STARTS', 'info', 'bi-arrow-left-circle');
                html += line();
            }
            
            html += block(start, 'SHIFT START', 'success', 'bi-play-circle');
            
            const grace = modal.querySelector('input[name="grace_period"]').value;
            if (grace > 0) {
                html += line();
                html += block(addMinutes(start, grace), 'GRACE PERIOD ENDS', 'success', 'bi-shield-check');
            }
            
            const lateEn = modal.querySelector('input[id^="enable_late_in_"]').checked;
            const lateTime = modal.querySelector('.time-late').value;
            if (lateEn && lateTime) {
                html += line();
                html += block(lateTime, 'LATE / FULL-DAY LIMIT', 'warning', 'bi-exclamation-triangle');
            }
            
            // Breaks
            const breakRows = breaksContainer.querySelectorAll('.break-row');
            breakRows.forEach(row => {
                const bName = row.querySelector('.break-name-input').value || 'BREAK';
                const bStart = row.querySelector('.break-start').value;
                const bEnd = row.querySelector('.break-end').value;
                if (bStart && bEnd) {
                    html += line();
                    html += block(bStart, bName.toUpperCase() + ' STARTS', 'secondary', 'bi-cup');
                    html += line();
                    html += block(bEnd, 'RESUME WORK', 'secondary', 'bi-arrow-right-circle');
                }
            });
            
            const subMinutes = (timeStr, mins) => {
                if(!timeStr) return null;
                const pts = timeStr.split(':');
                let m = parseInt(pts[0])*60 + parseInt(pts[1]) - parseInt(mins);
                if(m < 0) m += 24*60;
                const hh = String(Math.floor(m/60)).padStart(2, '0');
                const mm = String(m%60).padStart(2, '0');
                return hh+':'+mm;
            };

            const earlyOutEn = modal.querySelector('input[id^="enable_early_out_"]').checked;
            const earlyOutTime = modal.querySelector('.time-early-out').value;
            const earlyOutGraceInput = modal.querySelector('input[name="early_out_grace"]');
            const earlyOutGrace = earlyOutGraceInput ? parseInt(earlyOutGraceInput.value || 0) : 0;
            
            if (earlyOutEn) {
                if (earlyOutTime) {
                    html += line();
                    html += block(earlyOutTime, 'EARLY OUT THRESHOLD (EARLIEST ALLOWED OUT)', 'danger', 'bi-box-arrow-left');
                }
                
                if (earlyOutGrace > 0) {
                    const graceBoundary = subMinutes(end, earlyOutGrace);
                    if (graceBoundary && graceBoundary !== earlyOutTime) {
                        html += line();
                        html += block(graceBoundary, 'EARLY-OUT GRACE BOUNDARY (' + earlyOutGrace + 'M GRACE)', 'warning', 'bi-shield-check');
                    }
                }
            }
            
            html += line();
            html += block(end, 'NORMAL SHIFT END (ACCEPTED CHECKOUT)', 'success', 'bi-stop-circle');
            
            const otEn = modal.querySelector('input[id^="ot_eligibility_"]').checked;
            const otTime = modal.querySelector('.time-ot').value;
            if (otEn && otTime) {
                html += line();
                html += block(otTime, 'OVERTIME CALCULATION STARTS', 'primary', 'bi-cash');
            }
            
            timelineContainer.innerHTML = html;
        }
        
        // Initial sync
        syncCalc();
        updatePreview();
        
        // Add event listeners to input elements inside modal for dynamic preview
        modal.querySelectorAll('input').forEach(el => {
            el.addEventListener('change', updatePreview);
            // also on input for live typing
            if(el.type !== 'checkbox' && el.type !== 'radio') {
                el.addEventListener('input', updatePreview);
            }
        });
    });
});
</script>
@endpush
