@extends('layouts.app')

@section('content')
<div class="row g-4">
    <div class="col-lg-8 mx-auto">
        <div class="glass-card">
            <h5 class="mb-4 display-font"><i class="bi bi-sliders me-2 text-primary"></i> System Settings & Policies</h5>
            
            <form action="{{ route('settings.store') }}" method="POST">
                @csrf
                @foreach($settings->groupBy('group_name') as $group => $items)
                    @if($group === 'Payroll') @continue @endif
                    <div class="mb-5 p-4 rounded border" style="background-color: rgba(0,0,0,0.15); border-color: var(--border-color) !important;">
                        <h6 class="display-font text-primary border-bottom pb-2 mb-4">
                            <i class="bi bi-gear-fill me-1"></i> {{ strtoupper($group) }} POLICY SETTINGS
                        </h6>
                        
                        <div class="row g-3">
                            @foreach($items as $s)
                                <div class="col-md-6">
                                    <label class="form-label text-secondary fs-7 mb-1">{{ preg_replace('/(?<!\ )[A-Z]/', ' $0', $s->setting_key) }}</label>
                                    
                                    @php
                                        $currentVal = \App\Models\Setting::getVal($s->group_name, $s->setting_key);
                                    @endphp

                                    @if($s->setting_key === 'PasswordMinLength' || $s->setting_key === 'MaxLoginAttempts' || $s->setting_key === 'SessionTimeout' || $s->setting_key === 'LateGracePeriod' || $s->setting_key === 'EarlyOutGracePeriod' || $s->setting_key === 'OvertimeStartAfterMinutes' || $s->setting_key === 'DeviceOfflineThreshold')
                                        <input type="number" class="form-control form-control-custom" name="{{ $s->group_name }}__{{ $s->setting_key }}" value="{{ $currentVal }}" required>
                                    @elseif($currentVal === 'true' || $currentVal === 'false')
                                        <select class="form-select form-select-custom" name="{{ $s->group_name }}__{{ $s->setting_key }}">
                                            <option value="true" {{ $currentVal === 'true' ? 'selected' : '' }}>Yes / Enabled</option>
                                            <option value="false" {{ $currentVal === 'false' ? 'selected' : '' }}>No / Disabled</option>
                                        </select>
                                    @else
                                        <input type="{{ $s->is_encrypted ? 'password' : 'text' }}" class="form-control form-control-custom" name="{{ $s->group_name }}__{{ $s->setting_key }}" value="{{ $currentVal }}">
                                    @endif
                                    
                                    <span class="fs-8 text-secondary mt-1 d-block">{{ $s->description }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach

                <div class="text-end">
                    <button type="submit" class="btn btn-custom-primary px-4"><i class="bi bi-check-circle me-1"></i> Save Configuration</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
