@extends('portal.layout')

@section('content')
<div class="card" style="margin-bottom: 2rem;">
    <div class="card-header">
        <h2 class="card-title"><i class="fa-solid fa-user-circle"></i> My Profile</h2>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 2rem; margin-top: 1.5rem;">
        
        <!-- Profile Picture / Summary -->
        <div style="text-align: center; padding: 2rem; background: rgba(248, 250, 252, 0.5); border: 1px solid var(--border); border-radius: 8px;">
            <div style="width: 120px; height: 120px; background: linear-gradient(135deg, var(--primary) 0%, #818CF8 100%); color: white; font-size: 3rem; display: flex; align-items: center; justify-content: center; border-radius: 50%; margin: 0 auto 1.5rem auto; box-shadow: 0 8px 16px rgba(79, 70, 229, 0.2);">
                {{ substr($employee->first_name, 0, 1) }}{{ substr($employee->last_name, 0, 1) }}
            </div>
            
            <h3 style="margin: 0 0 0.5rem 0; font-size: 1.5rem; color: var(--dark);">{{ $employee->first_name }} {{ $employee->last_name }}</h3>
            <p style="margin: 0; color: var(--text-muted); font-size: 1rem;">{{ $employee->designation->name ?? 'Designation N/A' }}</p>
            
            <div style="margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px dashed var(--border);">
                <div style="font-size: 0.85rem; color: var(--text-muted); text-transform: uppercase; margin-bottom: 0.5rem; letter-spacing: 0.05em;">Employee ID</div>
                <div style="font-size: 1.25rem; font-weight: 600; color: var(--dark-light);">{{ $employee->employee_id }}</div>
            </div>
        </div>

        <!-- Profile Details -->
        <div>
            <div style="margin-bottom: 2rem;">
                <h4 style="border-bottom: 2px solid var(--primary); padding-bottom: 0.5rem; color: var(--primary); margin-top: 0;">Employment Details</h4>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-top: 1rem;">
                    <div>
                        <div style="font-size: 0.85rem; color: var(--text-muted);">Company</div>
                        <div style="font-weight: 500;">{{ $employee->company->company_name ?? 'N/A' }}</div>
                    </div>
                    <div>
                        <div style="font-size: 0.85rem; color: var(--text-muted);">Department</div>
                        <div style="font-weight: 500;">{{ $employee->department->name ?? 'N/A' }}</div>
                    </div>
                    <div>
                        <div style="font-size: 0.85rem; color: var(--text-muted);">Join Date</div>
                        <div style="font-weight: 500;">{{ $employee->join_date ? \Carbon\Carbon::parse($employee->join_date)->format('F d, Y') : 'N/A' }}</div>
                    </div>
                    <div>
                        <div style="font-size: 0.85rem; color: var(--text-muted);">Employment Status</div>
                        <div style="font-weight: 500;">
                            @if($employee->status == 'Active')
                                <span class="badge badge-success">Active</span>
                            @else
                                <span class="badge badge-danger">{{ $employee->status }}</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div>
                <h4 style="border-bottom: 2px solid var(--primary); padding-bottom: 0.5rem; color: var(--primary); margin-top: 0;">Contact Information</h4>
                <div style="display: grid; grid-template-columns: 1fr; gap: 1rem; margin-top: 1rem;">
                    <div>
                        <div style="font-size: 0.85rem; color: var(--text-muted);">Email Address</div>
                        <div style="font-weight: 500;">{{ $employee->user->email ?? 'N/A' }}</div>
                    </div>
                </div>
            </div>
            
            <div style="margin-top: 3rem; background: #DBEAFE; border: 1px solid #93C5FD; padding: 1rem; border-radius: 8px; color: #1E3A8A; font-size: 0.9rem;">
                <i class="fa-solid fa-circle-info"></i> To update your personal information, please contact HR or your manager.
            </div>
        </div>

    </div>
</div>
@endsection
