@extends('portal.layout')

@section('content')
<div class="card" style="max-width: 600px; margin: 0 auto; text-align: center;">
    <div class="card-header" style="justify-content: center; border-bottom: none;">
        <h2 class="card-title" style="font-size: 1.5rem;"><i class="fa-solid fa-location-dot"></i> Remote Web Punch</h2>
    </div>
    
    @if($hasApprovedWfh)
        <p style="color: var(--text-muted); margin-bottom: 2rem;">Your WFH request for today is approved. You can record your attendance below.</p>
        
        <div id="punchStatus" class="alert" style="display: none;"></div>

        <div style="background: rgba(248, 250, 252, 0.5); padding: 2rem; border-radius: 16px; border: 1px solid var(--border); margin-bottom: 2rem;">
            <p id="locationStatus" style="font-weight: 500; margin-bottom: 1.5rem;"><i class="fa-solid fa-spinner fa-spin"></i> Acquiring GPS coordinates...</p>
            
            <div style="display: flex; gap: 1rem; justify-content: center;">
                <button id="btnCheckIn" class="btn btn-primary" style="padding: 1rem 2rem; font-size: 1.1rem; background: var(--secondary);" disabled>
                    <i class="fa-solid fa-arrow-right-to-bracket"></i> Check In
                </button>
                
                <button id="btnCheckOut" class="btn btn-primary" style="padding: 1rem 2rem; font-size: 1.1rem; background: #F59E0B;" disabled>
                    <i class="fa-solid fa-arrow-right-from-bracket"></i> Check Out
                </button>
            </div>
        </div>
        
        <p style="font-size: 0.8rem; color: var(--text-muted);"><i class="fa-solid fa-circle-info"></i> Your precise location will be recorded with your punch.</p>
    @else
        <div style="padding: 3rem 0;">
            <div style="font-size: 4rem; color: #FEE2E2; margin-bottom: 1rem;">
                <i class="fa-solid fa-ban"></i>
            </div>
            <h3 style="color: #991B1B; margin-top: 0;">Access Denied</h3>
            <p style="color: var(--text-muted);">{{ $punchReason ?? 'Remote Web Punch is not available.' }}</p>
            
            <a href="{{ route('portal.wfh.index') }}" class="btn btn-primary" style="margin-top: 1rem;">Go to WFH Requests</a>
        </div>
    @endif
</div>
@endsection

@section('scripts')
@if($hasApprovedWfh)
<script>
    document.addEventListener('DOMContentLoaded', function() {
        let currentLat = null;
        let currentLng = null;
        const locationStatus = document.getElementById('locationStatus');
        const btnCheckIn = document.getElementById('btnCheckIn');
        const btnCheckOut = document.getElementById('btnCheckOut');
        const punchStatus = document.getElementById('punchStatus');

        if ("geolocation" in navigator) {
            navigator.geolocation.getCurrentPosition(function(position) {
                currentLat = position.coords.latitude;
                currentLng = position.coords.longitude;
                
                locationStatus.innerHTML = `<i class="fa-solid fa-check-circle" style="color: var(--secondary);"></i> Location acquired successfully. Ready to punch.`;
                btnCheckIn.disabled = false;
                btnCheckOut.disabled = false;
            }, function(error) {
                locationStatus.innerHTML = `<i class="fa-solid fa-triangle-exclamation" style="color: #991B1B;"></i> Please allow location access to use Web Punch.`;
            });
        } else {
            locationStatus.innerHTML = "Geolocation is not supported by your browser.";
        }

        function submitPunch(state) {
            btnCheckIn.disabled = true;
            btnCheckOut.disabled = true;
            locationStatus.innerHTML = `<i class="fa-solid fa-spinner fa-spin"></i> Recording punch...`;
            
            fetch("{{ route('portal.punch.store') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({
                    latitude: currentLat,
                    longitude: currentLng,
                    punch_state: state
                })
            })
            .then(response => response.json())
            .then(data => {
                punchStatus.style.display = 'block';
                if(data.success) {
                    punchStatus.className = 'alert alert-success';
                    punchStatus.innerHTML = `<i class="fa-solid fa-check-circle"></i> ${data.message}`;
                    locationStatus.innerHTML = "Done.";
                } else {
                    punchStatus.className = 'alert alert-error';
                    punchStatus.innerHTML = `<i class="fa-solid fa-triangle-exclamation"></i> ${data.message || 'Error recording punch'}`;
                    btnCheckIn.disabled = false;
                    btnCheckOut.disabled = false;
                }
            })
            .catch(error => {
                punchStatus.style.display = 'block';
                punchStatus.className = 'alert alert-error';
                punchStatus.innerHTML = `<i class="fa-solid fa-triangle-exclamation"></i> Network error occurred.`;
                btnCheckIn.disabled = false;
                btnCheckOut.disabled = false;
            });
        }

        btnCheckIn.addEventListener('click', () => submitPunch(0));
        btnCheckOut.addEventListener('click', () => submitPunch(1));
    });
</script>
@endif
@endsection
