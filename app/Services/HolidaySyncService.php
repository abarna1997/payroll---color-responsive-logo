<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Company;
use App\Models\Holiday;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class HolidaySyncService
{
    protected HolidayApiService $apiService;

    public function __construct(HolidayApiService $apiService)
    {
        $this->apiService = $apiService;
    }

    /**
     * Download and synchronize holidays for a given year.
     */
    public function syncHolidays(int $year, ?string $type = null, bool $force = false): array
    {
        Log::info("Starting holiday synchronization for year: $year, type: " . ($type ?? 'all'));
        $stats = [
            'added' => 0,
            'updated' => 0,
            'skipped' => 0,
            'errors' => 0,
        ];

        // Fetch normalized holidays
        $holidayList = $this->apiService->fetchHolidays($year, $type);
        if (empty($holidayList)) {
            Log::warning("No holidays fetched for year: $year. Sync halted.");
            return $stats;
        }

        // Fetch all registered companies
        $companies = Company::all();
        if ($companies->isEmpty()) {
            Log::warning("No companies found in database. Sync halted.");
            return $stats;
        }

        foreach ($companies as $company) {
            foreach ($holidayList as $item) {
                try {
                    $date = Carbon::parse($item['holiday_date'])->format('Y-m-d');
                    
                    // Match unique key: date, company, and holiday type
                    $existing = Holiday::where('holiday_date', $date)
                        ->where('company_id', $company->id)
                        ->where('holiday_type', $item['holiday_type'])
                        ->first();

                    if ($existing) {
                        if ($existing->source === 'Manual' && !$force) {
                            // Preserve manual holidays created by admins
                            $stats['skipped']++;
                            continue;
                        }

                        // Update existing API holiday record
                        $existing->update([
                            'holiday_name' => $item['holiday_name'],
                            'description' => $item['description'],
                            'calendar_type' => $item['calendar_type'],
                            'english_name' => $item['english_name'],
                            'sinhala_name' => $item['sinhala_name'],
                            'tamil_name' => $item['tamil_name'],
                            'is_paid' => $item['is_paid'],
                            'is_working_day' => $item['is_working_day'],
                            'affects_payroll' => $item['affects_payroll'],
                            'affects_attendance' => $item['affects_attendance'],
                            'affects_overtime' => $item['affects_overtime'],
                            'affects_leave' => $item['affects_leave'],
                            'last_synced_at' => Carbon::now(),
                            'api_reference' => $item['api_reference'],
                            'sync_status' => 'Synced',
                            'source' => 'API'
                        ]);
                        $stats['updated']++;
                    } else {
                        // Create a new API holiday entry
                        Holiday::create([
                            'company_id' => $company->id,
                            'holiday_name' => $item['holiday_name'],
                            'holiday_date' => $date,
                            'description' => $item['description'],
                            'holiday_type' => $item['holiday_type'],
                            'calendar_type' => $item['calendar_type'],
                            'english_name' => $item['english_name'],
                            'sinhala_name' => $item['sinhala_name'],
                            'tamil_name' => $item['tamil_name'],
                            'is_paid' => $item['is_paid'],
                            'is_working_day' => $item['is_working_day'],
                            'affects_payroll' => $item['affects_payroll'],
                            'affects_attendance' => $item['affects_attendance'],
                            'affects_overtime' => $item['affects_overtime'],
                            'affects_leave' => $item['affects_leave'],
                            'last_synced_at' => Carbon::now(),
                            'api_reference' => $item['api_reference'],
                            'sync_status' => 'Synced',
                            'source' => 'API'
                        ]);
                        $stats['added']++;
                    }
                } catch (\Exception $e) {
                    Log::error("Failed to sync holiday '{$item['holiday_name']}' for company {$company->id}: " . $e->getMessage());
                    $stats['errors']++;
                }
            }
        }

        // Write Audit Logs
        try {
            AuditLog::create([
                'user_id' => Auth::id() ?? 1, // system automated falls back to 1
                'action' => 'SYNCED_SRILANKAN_HOLIDAYS',
                'module' => 'Holiday Calendar',
                'record_id' => 0,
                'new_value' => json_encode([
                    'year' => $year,
                    'type' => $type,
                    'added' => $stats['added'],
                    'updated' => $stats['updated'],
                    'skipped' => $stats['skipped'],
                    'errors' => $stats['errors'],
                ]),
                'ip_address' => '127.0.0.1',
            ]);
        } catch (\Exception $ex) {
            Log::warning("Could not record sync audit log: " . $ex->getMessage());
        }

        Log::info("Holiday sync completed. Added: {$stats['added']}, Updated: {$stats['updated']}, Skipped: {$stats['skipped']}, Errors: {$stats['errors']}");
        return $stats;
    }
}
