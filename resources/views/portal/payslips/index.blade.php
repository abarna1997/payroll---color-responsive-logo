@extends('portal.layout')

@section('content')
<div class="card">
    <div class="card-header">
        <h2 class="card-title"><i class="fa-solid fa-file-invoice-dollar"></i> My Payslips</h2>
    </div>
    
    @if($payslips->isEmpty())
        <div style="text-align: center; padding: 4rem 0;">
            <div style="font-size: 4rem; color: var(--border); margin-bottom: 1rem;">
                <i class="fa-solid fa-folder-open"></i>
            </div>
            <h3 style="color: var(--text-muted); margin-top: 0;">No Payslips Available</h3>
            <p style="color: var(--text-muted);">Your payroll records will appear here once they are processed.</p>
        </div>
    @else
        <div class="dashboard-grid">
            @foreach($payslips as $payslip)
            <div class="card" style="margin-bottom: 0; display: flex; flex-direction: column; justify-content: space-between;">
                <div>
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1rem;">
                        <div>
                            <h3 style="margin: 0; font-size: 1.2rem; color: var(--primary);">{{ $payslip->period->period_name ?? Carbon\Carbon::parse($payslip->period->start_date)->format('F Y') }}</h3>
                            <div style="font-size: 0.85rem; color: var(--text-muted); margin-top: 0.25rem;">
                                {{ Carbon\Carbon::parse($payslip->period->start_date)->format('M d') }} - {{ Carbon\Carbon::parse($payslip->period->end_date)->format('M d, Y') }}
                            </div>
                        </div>
                        <span class="badge badge-success"><i class="fa-solid fa-check"></i> Paid</span>
                    </div>
                    
                    <div style="margin: 1.5rem 0;">
                        <div style="font-size: 0.85rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em;">Net Pay</div>
                        <div style="font-size: 2rem; font-weight: 700; color: var(--dark-light);">LKR {{ number_format($payslip->net_pay, 2) }}</div>
                    </div>
                </div>
                
                <div style="border-top: 1px solid var(--border); padding-top: 1rem; margin-top: 1rem;">
                    <a href="{{ route('portal.payslips.show', $payslip->id) }}" class="btn btn-outline" style="width: 100%; justify-content: center;">
                        <i class="fa-solid fa-eye"></i> View Payslip
                    </a>
                </div>
            </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
