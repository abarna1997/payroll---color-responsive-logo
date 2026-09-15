<div class="row g-3 mb-4">
    <!-- Present -->
    <div class="col-6 col-md-3 col-xl">
        <a href="{{ request()->fullUrlWithQuery(['status' => 'PRESENT', 'flags' => null]) }}" class="text-decoration-none">
            <div class="card border-0 border-start border-4 border-success shadow-sm h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Present</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['present'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i data-feather="check-circle" class="text-success opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </a>
    </div>

    <!-- Late -->
    <div class="col-6 col-md-3 col-xl">
        <a href="{{ request()->fullUrlWithQuery(['flags' => ['late_in'], 'status' => null]) }}" class="text-decoration-none">
            <div class="card border-0 border-start border-4 border-warning shadow-sm h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Late In</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['late'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i data-feather="clock" class="text-warning opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </a>
    </div>

    <!-- Early In -->
    <div class="col-6 col-md-3 col-xl">
        <a href="{{ request()->fullUrlWithQuery(['flags' => ['early_in'], 'status' => null]) }}" class="text-decoration-none">
            <div class="card border-0 border-start border-4 border-primary shadow-sm h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Early In</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['early_in'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i data-feather="arrow-down" class="text-primary opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </a>
    </div>

    <!-- Early Out -->
    <div class="col-6 col-md-3 col-xl">
        <a href="{{ request()->fullUrlWithQuery(['flags' => ['early_out'], 'status' => null]) }}" class="text-decoration-none">
            <div class="card border-0 border-start border-4 border-orange shadow-sm h-100 py-2" style="border-left-color: #fd7e14 !important;">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-uppercase mb-1" style="color: #fd7e14;">Early Out</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['early_out'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i data-feather="arrow-left" class="opacity-50" style="color: #fd7e14;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </a>
    </div>

    <!-- Absent -->
    <div class="col-6 col-md-3 col-xl">
        <a href="{{ request()->fullUrlWithQuery(['status' => 'ABSENT', 'flags' => null]) }}" class="text-decoration-none">
            <div class="card border-0 border-start border-4 border-danger shadow-sm h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Absent</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['absent'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i data-feather="x-circle" class="text-danger opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </a>
    </div>

    <!-- Half Day -->
    <div class="col-6 col-md-3 col-xl">
        <a href="{{ request()->fullUrlWithQuery(['status' => 'HALF_DAY', 'flags' => null]) }}" class="text-decoration-none">
            <div class="card border-0 border-start border-4 border-warning shadow-sm h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Half Day</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['half_day'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i data-feather="clock-alert" class="text-warning opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </a>
    </div>

    <!-- Incomplete -->
    <div class="col-6 col-md-3 col-xl">
        <a href="{{ request()->fullUrlWithQuery(['status' => 'INCOMPLETE', 'flags' => null]) }}" class="text-decoration-none">
            <div class="card border-0 border-start border-4 border-secondary shadow-sm h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-secondary text-uppercase mb-1">Incomplete</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['incomplete'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i data-feather="alert-circle" class="text-secondary opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </a>
    </div>

    <!-- OT Eligible -->
    <div class="col-6 col-md-3 col-xl">
        <a href="{{ request()->fullUrlWithQuery(['flags' => ['ot'], 'status' => null]) }}" class="text-decoration-none">
            <div class="card border-0 border-start border-4 border-info shadow-sm h-100 py-2" style="border-left-color: #6f42c1 !important;">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-uppercase mb-1" style="color: #6f42c1;">OT Eligible</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['ot_eligible'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i data-feather="clock-plus" class="opacity-50" style="color: #6f42c1;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </a>
    </div>
</div>