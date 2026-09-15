@extends('layouts.app')

@section('content')
<div class="row g-4">
    <div class="col-lg-12">
        <!-- Card Header -->
        <div class="glass-card mb-4 d-flex justify-content-between align-items-center">
            <div>
                <h4 class="display-font m-0 fw-bold"><i class="bi bi-building-gear me-2 text-primary"></i> Edit Company: {{ $company->company_name }}</h4>
                <div class="text-secondary fs-7 mt-1">Configure company profiles, upload branding assets, signatures, email configurations, and customize documents designs.</div>
            </div>
            <a href="{{ route('companies') }}" class="btn btn-custom-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back to List
            </a>
        </div>

        @if($readOnly)
            <div class="alert alert-warning border-0 shadow-sm p-3 mb-4 d-flex align-items-center gap-2" style="background-color: rgba(245, 158, 11, 0.12); color: #d97706;">
                <i class="bi bi-eye-fill fs-5"></i>
                <div class="fw-semibold">Read-Only View: HR Administrators can review company settings but cannot make updates.</div>
            </div>
        @endif

        <form action="{{ route('companies.update', $company->id) }}" method="POST" enctype="multipart/form-data">
            @csrf

            <!-- Navigation Tabs -->
            <div class="card border-0 bg-transparent mb-4">
                <ul class="nav nav-pills custom-pills" id="companyTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="general-tab" data-bs-toggle="tab" data-bs-target="#general" type="button" role="tab" aria-controls="general" aria-selected="true">
                            <i class="bi bi-info-circle me-1"></i> General Details
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="branding-tab" data-bs-toggle="tab" data-bs-target="#branding" type="button" role="tab" aria-controls="branding" aria-selected="false">
                            <i class="bi bi-palette me-1"></i> Company Branding
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="payslip-tab" data-bs-toggle="tab" data-bs-target="#payslip" type="button" role="tab" aria-controls="payslip" aria-selected="false">
                            <i class="bi bi-file-earmark-pdf me-1"></i> Payslip Options
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="signatures-tab" data-bs-toggle="tab" data-bs-target="#signatures" type="button" role="tab" aria-controls="signatures" aria-selected="false">
                            <i class="bi bi-pencil-square me-1"></i> Signatures
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="email-tab" data-bs-toggle="tab" data-bs-target="#email" type="button" role="tab" aria-controls="email" aria-selected="false">
                            <i class="bi bi-envelope-gear me-1"></i> Email Config
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="appearance-tab" data-bs-toggle="tab" data-bs-target="#appearance" type="button" role="tab" aria-controls="appearance" aria-selected="false">
                            <i class="bi bi-window-sidebar me-1"></i> Appearance
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="preview-tab" data-bs-toggle="tab" data-bs-target="#preview" type="button" role="tab" aria-controls="preview" aria-selected="false">
                            <i class="bi bi-eye me-1"></i> Live Preview
                        </button>
                    </li>
                </ul>
            </div>

            <!-- Tabs Content -->
            <div class="tab-content" id="companyTabContent">
                
                <!-- TAB 1: GENERAL -->
                <div class="tab-pane fade show active" id="general" role="tabpanel" aria-labelledby="general-tab">
                    <div class="glass-card">
                        <h5 class="mb-4 display-font fw-bold text-dark"><i class="bi bi-building me-2 text-primary"></i> General Company Information</h5>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label text-secondary fs-8 uppercase">Company Name</label>
                                <input type="text" class="form-control form-control-custom" name="company_name" value="{{ $company->company_name }}" required @disabled($readOnly)>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-secondary fs-8 uppercase">Company Code</label>
                                <input type="text" class="form-control form-control-custom" name="company_code" value="{{ $company->company_code }}" required @disabled($readOnly)>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label text-secondary fs-8 uppercase">Registration Number (BR)</label>
                                <input type="text" class="form-control form-control-custom" name="registration_number" value="{{ $company->registration_number }}" @disabled($readOnly)>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label text-secondary fs-8 uppercase">Tax Number</label>
                                <input type="text" class="form-control form-control-custom" name="tax_number" value="{{ $company->tax_number }}" @disabled($readOnly)>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label text-secondary fs-8 uppercase">VAT Registration Number</label>
                                <input type="text" class="form-control form-control-custom" name="vat_number" value="{{ $company->vat_number }}" @disabled($readOnly)>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-secondary fs-8 uppercase">EPF Registration Number</label>
                                <input type="text" class="form-control form-control-custom" name="epf_registration_number" value="{{ $company->epf_registration_number }}" @disabled($readOnly)>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-secondary fs-8 uppercase">ETF Registration Number</label>
                                <input type="text" class="form-control form-control-custom" name="etf_registration_number" value="{{ $company->etf_registration_number }}" @disabled($readOnly)>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label text-secondary fs-8 uppercase">Street Address</label>
                                <textarea class="form-control form-control-custom" name="address" rows="3" @disabled($readOnly)>{{ $company->address }}</textarea>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label text-secondary fs-8 uppercase">City</label>
                                <input type="text" class="form-control form-control-custom" name="city" value="{{ $company->city }}" @disabled($readOnly)>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label text-secondary fs-8 uppercase">Province / State</label>
                                <input type="text" class="form-control form-control-custom" name="province" value="{{ $company->province }}" @disabled($readOnly)>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label text-secondary fs-8 uppercase">Country</label>
                                <input type="text" class="form-control form-control-custom" name="country" value="{{ $company->country }}" @disabled($readOnly)>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label text-secondary fs-8 uppercase">Postal Code</label>
                                <input type="text" class="form-control form-control-custom" name="postal_code" value="{{ $company->postal_code }}" @disabled($readOnly)>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label text-secondary fs-8 uppercase">Office Phone Number</label>
                                <input type="text" class="form-control form-control-custom" name="contact_number" value="{{ $company->contact_number }}" @disabled($readOnly)>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label text-secondary fs-8 uppercase">Mobile Number</label>
                                <input type="text" class="form-control form-control-custom" name="mobile_number" value="{{ $company->mobile_number }}" @disabled($readOnly)>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label text-secondary fs-8 uppercase">Fax Number</label>
                                <input type="text" class="form-control form-control-custom" name="fax_number" value="{{ $company->fax_number }}" @disabled($readOnly)>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-secondary fs-8 uppercase">Official Email Address</label>
                                <input type="email" class="form-control form-control-custom" name="email" value="{{ $company->email }}" @disabled($readOnly)>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-secondary fs-8 uppercase">Corporate Website (URL)</label>
                                <input type="text" class="form-control form-control-custom" name="website" value="{{ $company->website }}" placeholder="e.g. www.primeone.lk" @disabled($readOnly)>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label text-secondary fs-8 uppercase">Company Status</label>
                                <select class="form-select form-select-custom" name="status" required @disabled($readOnly)>
                                    <option value="Active" {{ $company->status === 'Active' ? 'selected' : '' }}>Active</option>
                                    <option value="Inactive" {{ $company->status === 'Inactive' ? 'selected' : '' }}>Inactive</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TAB 2: BRANDING -->
                <div class="tab-pane fade" id="branding" role="tabpanel" aria-labelledby="branding-tab">
                    <div class="row g-4">
                        <!-- Left uploads block -->
                        <div class="col-md-6">
                            <!-- Card: Logo -->
                            <div class="glass-card mb-4">
                                <h6 class="display-font text-primary border-bottom pb-2 mb-3"><i class="bi bi-image me-1"></i> Company Logo</h6>
                                @include('companies.partials.upload_field', ['fieldName' => 'logo', 'dbPath' => $company->logo_path, 'dimensions' => 'Recommended: 200 x 200 px (Square)', 'readOnly' => $readOnly])
                            </div>
                            <!-- Card: Header Banner -->
                            <div class="glass-card mb-4">
                                <h6 class="display-font text-primary border-bottom pb-2 mb-3"><i class="bi bi-image me-1"></i> Header Banner (Full Width)</h6>
                                @include('companies.partials.upload_field', ['fieldName' => 'header_banner', 'dbPath' => $company->banner_path, 'dimensions' => 'Recommended: 800 x 100 px', 'readOnly' => $readOnly])
                            </div>
                            <!-- Card: Footer Banner -->
                            <div class="glass-card mb-4">
                                <h6 class="display-font text-primary border-bottom pb-2 mb-3"><i class="bi bi-image me-1"></i> Footer Banner</h6>
                                @include('companies.partials.upload_field', ['fieldName' => 'footer_banner', 'dbPath' => $company->footer_banner_path, 'dimensions' => 'Recommended: 800 x 60 px', 'readOnly' => $readOnly])
                            </div>
                            <!-- Card: Watermark -->
                            <div class="glass-card mb-4">
                                <h6 class="display-font text-primary border-bottom pb-2 mb-3"><i class="bi bi-image me-1"></i> Document Watermark</h6>
                                @include('companies.partials.upload_field', ['fieldName' => 'watermark', 'dbPath' => $company->watermark_path, 'dimensions' => 'Recommended: 500 x 500 px (Faded PNG)', 'readOnly' => $readOnly])
                            </div>
                        </div>

                        <!-- Right uploads block -->
                        <div class="col-md-6">
                            <!-- Card: Seal -->
                            <div class="glass-card mb-4">
                                <h6 class="display-font text-primary border-bottom pb-2 mb-3"><i class="bi bi-image me-1"></i> Company Seal</h6>
                                @include('companies.partials.upload_field', ['fieldName' => 'company_seal', 'dbPath' => $company->digital_seal_path, 'dimensions' => 'Recommended: 150 x 150 px', 'readOnly' => $readOnly])
                            </div>
                            <!-- Card: Stamp -->
                            <div class="glass-card mb-4">
                                <h6 class="display-font text-primary border-bottom pb-2 mb-3"><i class="bi bi-image me-1"></i> Company Stamp</h6>
                                @include('companies.partials.upload_field', ['fieldName' => 'company_stamp', 'dbPath' => $company->company_stamp_path, 'dimensions' => 'Recommended: 180 x 80 px', 'readOnly' => $readOnly])
                            </div>
                            <!-- Card: Favicon -->
                            <div class="glass-card mb-4">
                                <h6 class="display-font text-primary border-bottom pb-2 mb-3"><i class="bi bi-image me-1"></i> System Favicon</h6>
                                @include('companies.partials.upload_field', ['fieldName' => 'favicon', 'dbPath' => $company->favicon_path, 'dimensions' => 'Recommended: 32 x 32 px (.ico or .png)', 'readOnly' => $readOnly])
                            </div>
                            <!-- Card: Email Branding -->
                            <div class="glass-card mb-4">
                                <h6 class="display-font text-primary border-bottom pb-2 mb-3"><i class="bi bi-image me-1"></i> Email Logo</h6>
                                @include('companies.partials.upload_field', ['fieldName' => 'email_logo', 'dbPath' => $company->email_logo_path, 'dimensions' => 'Recommended: 150 x 50 px', 'readOnly' => $readOnly])
                            </div>
                            <div class="glass-card mb-4">
                                <h6 class="display-font text-primary border-bottom pb-2 mb-3"><i class="bi bi-image me-1"></i> Email Footer Logo</h6>
                                @include('companies.partials.upload_field', ['fieldName' => 'email_footer_logo', 'dbPath' => $company->email_footer_logo_path, 'dimensions' => 'Recommended: 150 x 30 px', 'readOnly' => $readOnly])
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TAB 3: PAYSLIP -->
                <div class="tab-pane fade" id="payslip" role="tabpanel" aria-labelledby="payslip-tab">
                    <div class="glass-card">
                        <h5 class="mb-4 display-font fw-bold text-dark"><i class="bi bi-file-earmark-pdf me-2 text-primary"></i> Payslip Branding Options</h5>
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" name="show_header_banner" value="1" id="showHeaderBanner" {{ $company->show_header_banner ? 'checked' : '' }} @disabled($readOnly)>
                                    <label class="form-check-label fw-semibold text-dark" for="showHeaderBanner">Display Full Width Header Banner</label>
                                </div>
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" name="show_footer_banner" value="1" id="showFooterBanner" {{ $company->show_footer_banner ? 'checked' : '' }} @disabled($readOnly)>
                                    <label class="form-check-label fw-semibold text-dark" for="showFooterBanner">Display Footer Banner on Payslip</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" name="show_watermark" value="1" id="showWatermark" {{ $company->show_watermark ? 'checked' : '' }} @disabled($readOnly)>
                                    <label class="form-check-label fw-semibold text-dark" for="showWatermark">Enable Document Watermark Backdrop</label>
                                </div>
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" name="show_company_seal" value="1" id="showCompanySeal" {{ $company->show_company_seal ? 'checked' : '' }} @disabled($readOnly)>
                                    <label class="form-check-label fw-semibold text-dark" for="showCompanySeal">Display Company Seal next to Signature</label>
                                </div>
                            </div>
                        </div>

                        <hr style="border-top: 1px dashed var(--border-color); margin: 25px 0;">

                        <h5 class="mb-4 display-font fw-bold text-dark"><i class="bi bi-file-earmark-check me-2 text-primary"></i> Payslip Signature Options</h5>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label text-secondary fs-8 uppercase">Signature Mode</label>
                                <select class="form-select form-select-custom" name="signature_mode" required @disabled($readOnly)>
                                    <option value="No Signature" {{ $company->signature_mode === 'No Signature' ? 'selected' : '' }}>No Signature (Plain Document)</option>
                                    <option value="Digital Signature" {{ $company->signature_mode === 'Digital Signature' ? 'selected' : '' }}>Digital Signature (Render Saved Image)</option>
                                    <option value="Manual Signature" {{ $company->signature_mode === 'Manual Signature' ? 'selected' : '' }}>Manual Signature (Add Signature Placeholders)</option>
                                    <option value="Digital + Manual Signature" {{ $company->signature_mode === 'Digital + Manual Signature' ? 'selected' : '' }}>Digital + Manual (Show both)</option>
                                </select>
                            </div>
                            <div class="col-md-6 d-flex align-items-end">
                                <div class="fs-8 text-secondary border-start ps-3 py-1">
                                    <strong>Manual Mode Placeholders:</strong> Displays 'Prepared By', 'Checked By', 'Approved By', and 'Employee Signature' blocks in printed/PDF outputs.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TAB 4: SIGNATURES -->
                <div class="tab-pane fade" id="signatures" role="tabpanel" aria-labelledby="signatures-tab">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <!-- HR Manager -->
                            <div class="glass-card mb-4">
                                <h6 class="display-font text-primary border-bottom pb-2 mb-3"><i class="bi bi-pen-fill me-1"></i> HR Manager Signature</h6>
                                @include('companies.partials.upload_field', ['fieldName' => 'hr_signature', 'dbPath' => $company->hr_signature_path, 'dimensions' => 'Recommended: 150 x 60 px', 'readOnly' => $readOnly])
                            </div>
                            <!-- Finance Manager -->
                            <div class="glass-card mb-4">
                                <h6 class="display-font text-primary border-bottom pb-2 mb-3"><i class="bi bi-pen-fill me-1"></i> Finance Manager Signature</h6>
                                @include('companies.partials.upload_field', ['fieldName' => 'finance_signature', 'dbPath' => $company->finance_signature_path, 'dimensions' => 'Recommended: 150 x 60 px', 'readOnly' => $readOnly])
                            </div>
                            <!-- Authorized Officer -->
                            <div class="glass-card mb-4">
                                <h6 class="display-font text-primary border-bottom pb-2 mb-3"><i class="bi bi-pen-fill me-1"></i> Authorized Officer Signature</h6>
                                @include('companies.partials.upload_field', ['fieldName' => 'authorized_signature', 'dbPath' => $company->authorized_signature_path, 'dimensions' => 'Recommended: 150 x 60 px', 'readOnly' => $readOnly])
                            </div>
                        </div>

                        <div class="col-md-6">
                            <!-- Managing Director -->
                            <div class="glass-card mb-4">
                                <h6 class="display-font text-primary border-bottom pb-2 mb-3"><i class="bi bi-pen-fill me-1"></i> Managing Director Signature</h6>
                                @include('companies.partials.upload_field', ['fieldName' => 'director_signature', 'dbPath' => $company->director_signature_path, 'dimensions' => 'Recommended: 150 x 60 px', 'readOnly' => $readOnly])
                            </div>
                            <!-- CEO -->
                            <div class="glass-card mb-4">
                                <h6 class="display-font text-primary border-bottom pb-2 mb-3"><i class="bi bi-pen-fill me-1"></i> CEO Signature</h6>
                                @include('companies.partials.upload_field', ['fieldName' => 'ceo_signature', 'dbPath' => $company->ceo_signature_path, 'dimensions' => 'Recommended: 150 x 60 px', 'readOnly' => $readOnly])
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TAB 5: EMAIL -->
                <div class="tab-pane fade" id="email" role="tabpanel" aria-labelledby="email-tab">
                    <div class="glass-card">
                        <h5 class="mb-4 display-font fw-bold text-dark"><i class="bi bi-envelope-gear me-2 text-primary"></i> Company SMTP & Email Gateway Configurations</h5>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label text-secondary fs-8 uppercase">Sender From Name</label>
                                <input type="text" class="form-control form-control-custom" name="smtp_from_name" value="{{ $company->smtp_from_name }}" placeholder="e.g. Prime One - Fin" @disabled($readOnly)>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-secondary fs-8 uppercase">Sender From Email</label>
                                <input type="email" class="form-control form-control-custom" name="smtp_from_email" value="{{ $company->smtp_from_email }}" placeholder="e.g. payslips@primeone.lk" @disabled($readOnly)>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label text-secondary fs-8 uppercase">Reply-To Address</label>
                                <input type="email" class="form-control form-control-custom" name="smtp_reply_to" value="{{ $company->smtp_reply_to }}" placeholder="e.g. support@primeone.lk" @disabled($readOnly)>
                            </div>
                            
                            <hr class="my-4" style="border-top: 1px dashed var(--border-color);">
                            
                            <div class="col-md-6">
                                <label class="form-label text-secondary fs-8 uppercase">SMTP Hostname</label>
                                <input type="text" class="form-control form-control-custom" name="smtp_host" value="{{ $company->smtp_host }}" placeholder="e.g. mail.primeone.global" @disabled($readOnly)>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label text-secondary fs-8 uppercase">SMTP Port</label>
                                <input type="number" class="form-control form-control-custom" name="smtp_port" value="{{ $company->smtp_port }}" placeholder="e.g. 465" @disabled($readOnly)>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label text-secondary fs-8 uppercase">Encryption Protocol</label>
                                <select class="form-select form-select-custom" name="smtp_encryption" @disabled($readOnly)>
                                    <option value="" {{ $company->smtp_encryption === null ? 'selected' : '' }}>None</option>
                                    <option value="ssl" {{ $company->smtp_encryption === 'ssl' ? 'selected' : '' }}>SSL (Recommended)</option>
                                    <option value="tls" {{ $company->smtp_encryption === 'tls' ? 'selected' : '' }}>TLS</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-secondary fs-8 uppercase">SMTP Username</label>
                                <input type="text" class="form-control form-control-custom" name="smtp_username" value="{{ $company->smtp_username }}" placeholder="e.g. payslip@primeone.lk" @disabled($readOnly)>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-secondary fs-8 uppercase">SMTP Password</label>
                                <input type="password" class="form-control form-control-custom" name="smtp_password" placeholder="Leave blank to keep existing password" @disabled($readOnly)>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TAB 6: APPEARANCE -->
                <div class="tab-pane fade" id="appearance" role="tabpanel" aria-labelledby="appearance-tab">
                    <div class="glass-card">
                        <h5 class="mb-4 display-font fw-bold text-dark"><i class="bi bi-palette-fill me-2 text-primary"></i> Corporate Document Appearance</h5>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label text-secondary fs-8 uppercase">Primary Theme Color</label>
                                <input type="color" class="form-control form-control-custom" name="color_theme" value="{{ $company->color_theme ?? '#f95716' }}" @disabled($readOnly)>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label text-secondary fs-8 uppercase">Secondary Accent Color</label>
                                <input type="color" class="form-control form-control-custom" name="secondary_color" value="{{ $company->secondary_color ?? '#1b2a35' }}" @disabled($readOnly)>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label text-secondary fs-8 uppercase">Font Family</label>
                                <select class="form-select form-select-custom" name="font_family" required @disabled($readOnly)>
                                    <option value="Inter" {{ $company->font_family === 'Inter' ? 'selected' : '' }}>Inter (Clean Sans-Serif)</option>
                                    <option value="Outfit" {{ $company->font_family === 'Outfit' ? 'selected' : '' }}>Outfit (Geometrical Display)</option>
                                    <option value="Roboto" {{ $company->font_family === 'Roboto' ? 'selected' : '' }}>Roboto</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TAB 7: PREVIEW -->
                <div class="tab-pane fade" id="preview" role="tabpanel" aria-labelledby="preview-tab">
                    <div class="glass-card">
                        <h5 class="mb-4 display-font fw-bold text-dark"><i class="bi bi-file-earmark-slides me-2 text-primary"></i> Branding Assets Live Preview</h5>
                        
                        <div class="row g-4">
                            <div class="col-md-6">
                                <div class="p-3 border rounded mb-3 bg-light">
                                    <div class="fs-8 text-secondary uppercase fw-bold mb-2">Live Logo Preview</div>
                                    @if($company->logo_path)
                                        <img src="{{ asset('storage/' . $company->logo_path) }}" style="max-height: 80px; object-fit: contain;">
                                    @else
                                        <span class="text-secondary fs-8">No logo uploaded.</span>
                                    @endif
                                </div>
                                <div class="p-3 border rounded mb-3 bg-light">
                                    <div class="fs-8 text-secondary uppercase fw-bold mb-2">Live Full Banner Preview</div>
                                    @if($company->banner_path)
                                        <img src="{{ asset('storage/' . $company->banner_path) }}" class="w-100" style="max-height: 100px; object-fit: cover;">
                                    @else
                                        <span class="text-secondary fs-8">No banner uploaded.</span>
                                    @endif
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="p-3 border rounded mb-3 bg-light">
                                    <div class="fs-8 text-secondary uppercase fw-bold mb-2">HR Signature Live Preview</div>
                                    @if($company->hr_signature_path)
                                        <img src="{{ asset('storage/' . $company->hr_signature_path) }}" style="max-height: 50px; object-fit: contain;">
                                    @else
                                        <span class="text-secondary fs-8">No HR Signature uploaded.</span>
                                    @endif
                                </div>
                                <div class="p-3 border rounded mb-3 bg-light">
                                    <div class="fs-8 text-secondary uppercase fw-bold mb-2">Digital Seal Live Preview</div>
                                    @if($company->digital_seal_path)
                                        <img src="{{ asset('storage/' . $company->digital_seal_path) }}" style="max-height: 80px; object-fit: contain;">
                                    @else
                                        <span class="text-secondary fs-8">No seal uploaded.</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Submit Button (Hidden in Read-Only) -->
            @if(!$readOnly)
                <div class="text-end mt-4">
                    <button type="submit" class="btn btn-custom-primary px-5 py-2 fw-semibold">
                        <i class="bi bi-check-circle me-1"></i> Save Company Profile Changes
                    </button>
                </div>
            @endif
        </form>
    </div>
</div>
@endsection
