@extends('layouts.guest')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-danger text-white text-center py-3">
                    <h4 class="mb-0"><i class="bi bi-exclamation-triangle me-2"></i>Invalid Document</h4>
                </div>
                <div class="card-body p-5 text-center">
                    <h5 class="text-danger mb-3">Document Not Found or Invalid</h5>
                    <p class="text-muted">
                        The QR code scanned does not match any valid document in our system. 
                        This document may be forged or tampered with.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
