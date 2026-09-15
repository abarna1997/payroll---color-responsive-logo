<?php

namespace App\Services;

use App\Models\Company;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class CompanyService
{
    public function updateCompany(Company $company, Request $request)
    {
        $fields = [
            'logo_path' => 'logo',
            'banner_path' => 'header_banner',
            'footer_banner_path' => 'footer_banner',
            'watermark_path' => 'watermark',
            'digital_seal_path' => 'company_seal',
            'company_stamp_path' => 'company_stamp',
            'favicon_path' => 'favicon',
            'email_logo_path' => 'email_logo',
            'email_footer_logo_path' => 'email_footer_logo',
            'hr_signature_path' => 'hr_signature',
            'finance_signature_path' => 'finance_signature',
            'director_signature_path' => 'director_signature',
            'ceo_signature_path' => 'ceo_signature',
            'authorized_signature_path' => 'authorized_signature',
        ];

        $updateData = $request->except(array_values($fields));

        // Toggles / checkboxes
        $toggles = ['show_header_banner', 'show_footer_banner', 'show_watermark', 'show_company_seal'];
        foreach ($toggles as $toggle) {
            $updateData[$toggle] = $request->has($toggle);
        }

        // Handle file uploads securely via Laravel Storage
        foreach ($fields as $dbField => $inputName) {
            if ($request->hasFile($inputName)) {
                $file = $request->file($inputName);
                $path = $file->store('company_branding', 'public');

                // Delete old file if exists
                if ($company->$dbField && Storage::disk('public')->exists($company->$dbField)) {
                    Storage::disk('public')->delete($company->$dbField);
                }
                $updateData[$dbField] = $path;
            } elseif ($request->input($inputName . '_remove') === '1') {
                if ($company->$dbField && Storage::disk('public')->exists($company->$dbField)) {
                    Storage::disk('public')->delete($company->$dbField);
                }
                $updateData[$dbField] = null;
            }
        }

        // SMTP password encryption
        if ($request->filled('smtp_password')) {
            $updateData['smtp_password'] = encrypt($request->input('smtp_password'));
        } else {
            unset($updateData['smtp_password']);
        }

        $oldVal = json_encode($company);
        $company->update($updateData);

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'UPDATE_COMPANY',
            'module' => 'Company Management',
            'record_id' => $company->id,
            'old_value' => $oldVal,
            'new_value' => json_encode($company),
            'ip_address' => $request->ip(),
        ]);

        return $company;
    }
}
