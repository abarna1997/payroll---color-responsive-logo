<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class HolidayApiService
{
    protected string $url;
    protected ?string $key;

    public function __construct()
    {
        $this->url = config('services.holiday_api.url') ?? 'https://induwara.lk/api/v1/holidays';
        $this->key = config('services.holiday_api.key');
    }

    /**
     * Fetch holidays from the API or return fallbacks on failure.
     */
    public function fetchHolidays(int $year, ?string $type = null): array
    {
        if (empty($this->key)) {
            Log::warning('Holiday API credentials not configured. Using fallback local gazetted holidays.');
            return $this->getFallbackHolidays($year, $type);
        }

        try {
            // Call API with Bearer Token, retrying twice with a 5-second timeout
            $response = Http::withToken($this->key)
                ->timeout(5)
                ->retry(2, 200)
                ->get($this->url, [
                    'year' => $year,
                    'type' => $type
                ]);

            if ($response->successful()) {
                $data = $response->json();
                $holidays = $data['data']['holidays'] ?? $data['holidays'] ?? $data;
                if (is_array($holidays) && count($holidays) > 0) {
                    return $this->normalizeResponse($holidays);
                }
            }

            Log::error('Holiday API response error. Status: ' . $response->status() . '. Falling back to local data.');
        } catch (\Exception $e) {
            Log::error('Holiday API connection failed. Message: ' . $e->getMessage() . '. Falling back to local data.');
        }

        return $this->getFallbackHolidays($year, $type);
    }

    /**
     * Normalize API responses into a standard holiday format.
     */
    protected function normalizeResponse(array $rawHolidays): array
    {
        $normalized = [];
        foreach ($rawHolidays as $item) {
            // Calculate holiday type matching (Poya, Public, Bank, Mercantile)
            $typeVal = 'Public';
            $nameLower = strtolower($item['name'] ?? $item['holiday_name'] ?? '');
            if (($item['type'] ?? '') === 'Buddhist' || str_contains($nameLower, 'poya')) {
                $typeVal = 'Poya';
            } elseif (!empty($item['public'])) {
                $typeVal = 'Public';
            } elseif (!empty($item['bank'])) {
                $typeVal = 'Bank';
            } elseif (!empty($item['mercantile'])) {
                $typeVal = 'Mercantile';
            }

            $normalized[] = [
                'holiday_name' => $item['name'] ?? $item['holiday_name'] ?? 'Unnamed Holiday',
                'holiday_date' => $item['date'] ?? $item['holiday_date'] ?? null,
                'description' => $item['description'] ?? null,
                'holiday_type' => $typeVal,
                'calendar_type' => $item['type'] ?? $item['calendar_type'] ?? null,
                'english_name' => $item['name'] ?? $item['english_name'] ?? null,
                'sinhala_name' => $item['nameSinhala'] ?? $item['sinhala_name'] ?? null,
                'tamil_name' => $item['nameTamil'] ?? $item['tamil_name'] ?? null,
                'is_paid' => isset($item['is_paid']) ? (bool)$item['is_paid'] : true,
                'is_working_day' => isset($item['is_working_day']) ? (bool)$item['is_working_day'] : false,
                'affects_payroll' => isset($item['affects_payroll']) ? (bool)$item['affects_payroll'] : true,
                'affects_attendance' => isset($item['affects_attendance']) ? (bool)$item['affects_attendance'] : true,
                'affects_overtime' => isset($item['affects_overtime']) ? (bool)$item['affects_overtime'] : true,
                'affects_leave' => isset($item['affects_leave']) ? (bool)$item['affects_leave'] : true,
                'api_reference' => $item['id'] ?? $item['api_reference'] ?? null,
            ];
        }
        return array_filter($normalized, fn($h) => !empty($h['holiday_date']));
    }

    /**
     * Sri Lankan Fallback Holidays dictionary when API is unavailable.
     */
    protected function getFallbackHolidays(int $year, ?string $filterType = null): array
    {
        $fallback = [
            [
                'holiday_name' => 'Tamil Thai Pongal Day',
                'holiday_date' => "$year-01-15",
                'description' => 'Tamil Thai Pongal Religious Holiday',
                'holiday_type' => 'Public',
                'calendar_type' => 'National',
                'english_name' => 'Tamil Thai Pongal Day',
                'sinhala_name' => 'දෙමළ තෛපොංගල් දිනය',
                'tamil_name' => 'தமிழர் தைப்பொங்கல் දිනය',
                'is_paid' => true,
                'is_working_day' => false,
                'affects_payroll' => true,
                'affects_attendance' => true,
                'affects_overtime' => true,
                'affects_leave' => true,
                'api_reference' => 'fallback-thaipongal',
            ],
            [
                'holiday_name' => 'National Day (Independence Day)',
                'holiday_date' => "$year-02-04",
                'description' => 'Sri Lankan National Day Celebration',
                'holiday_type' => 'Public',
                'calendar_type' => 'National',
                'english_name' => 'National Day',
                'sinhala_name' => 'ජාතික නිදහස් දිනය',
                'tamil_name' => 'தேசிய சுதந்திர தினம்',
                'is_paid' => true,
                'is_working_day' => false,
                'affects_payroll' => true,
                'affects_attendance' => true,
                'affects_overtime' => true,
                'affects_leave' => true,
                'api_reference' => 'fallback-independence',
            ],
            [
                'holiday_name' => 'Good Friday',
                'holiday_date' => "$year-04-03", // standard approx
                'description' => 'Good Friday religious holiday',
                'holiday_type' => 'Public',
                'calendar_type' => 'Religious',
                'english_name' => 'Good Friday',
                'sinhala_name' => 'මහ සිකුරාදා',
                'tamil_name' => 'புனித வெள்ளி',
                'is_paid' => true,
                'is_working_day' => false,
                'affects_payroll' => true,
                'affects_attendance' => true,
                'affects_overtime' => true,
                'affects_leave' => true,
                'api_reference' => 'fallback-goodfriday',
            ],
            [
                'holiday_name' => 'Day before Sinhala & Tamil New Year',
                'holiday_date' => "$year-04-12",
                'description' => 'Traditional New Year Eve',
                'holiday_type' => 'Public',
                'calendar_type' => 'National',
                'english_name' => 'Sinhala & Tamil New Year Eve',
                'sinhala_name' => 'සිංහල සහ දෙමළ අලුත් අවුරුදු දිනයට පෙර දිනය',
                'tamil_name' => 'சிங்கள புத்தாண்டுக்கு முந்தைய நாள்',
                'is_paid' => true,
                'is_working_day' => false,
                'affects_payroll' => true,
                'affects_attendance' => true,
                'affects_overtime' => true,
                'affects_leave' => true,
                'api_reference' => 'fallback-nyeve',
            ],
            [
                'holiday_name' => 'Sinhala & Tamil New Year Day',
                'holiday_date' => "$year-04-13",
                'description' => 'Traditional New Year Festival',
                'holiday_type' => 'Public',
                'calendar_type' => 'National',
                'english_name' => 'Sinhala & Tamil New Year Day',
                'sinhala_name' => 'සිංහල සහ දෙමළ අලුත් අවුරුදු දිනය',
                'tamil_name' => 'சிங்கள தமிழ் புத்தாண்டு தினம்',
                'is_paid' => true,
                'is_working_day' => false,
                'affects_payroll' => true,
                'affects_attendance' => true,
                'affects_overtime' => true,
                'affects_leave' => true,
                'api_reference' => 'fallback-nyday',
            ],
            [
                'holiday_name' => 'May Day (International Workers\' Day)',
                'holiday_date' => "$year-05-01",
                'description' => 'Labour Day holiday',
                'holiday_type' => 'Public',
                'calendar_type' => 'National',
                'english_name' => 'May Day',
                'sinhala_name' => 'මැයි දිනය',
                'tamil_name' => 'மே தினம்',
                'is_paid' => true,
                'is_working_day' => false,
                'affects_payroll' => true,
                'affects_attendance' => true,
                'affects_overtime' => true,
                'affects_leave' => true,
                'api_reference' => 'fallback-mayday',
            ],
            [
                'holiday_name' => 'Vesak Full Moon Poya Day',
                'holiday_date' => "$year-05-24", // standard approx
                'description' => 'Buddhist religious holiday',
                'holiday_type' => 'Poya',
                'calendar_type' => 'Religious',
                'english_name' => 'Vesak Poya Day',
                'sinhala_name' => 'වෙසක් පුර පසළොස්වක පෝය දිනය',
                'tamil_name' => 'விசாக பௌர்ணமி',
                'is_paid' => true,
                'is_working_day' => false,
                'affects_payroll' => true,
                'affects_attendance' => true,
                'affects_overtime' => true,
                'affects_leave' => true,
                'api_reference' => 'fallback-vesak',
            ],
            [
                'holiday_name' => 'Eid-ul-Fitr (Ramazan Festival Day)',
                'holiday_date' => "$year-06-25", // standard approx
                'description' => 'Islamic religious holiday',
                'holiday_type' => 'Public',
                'calendar_type' => 'Religious',
                'english_name' => 'Ramazan Festival',
                'sinhala_name' => 'රාමසාන් උත්සව දිනය',
                'tamil_name' => 'நோன்பුப் பெருநாள்',
                'is_paid' => true,
                'is_working_day' => false,
                'affects_payroll' => true,
                'affects_attendance' => true,
                'affects_overtime' => true,
                'affects_leave' => true,
                'api_reference' => 'fallback-ramazan',
            ],
            [
                'holiday_name' => 'Christmas Day',
                'holiday_date' => "$year-12-25",
                'description' => 'Christian religious holiday',
                'holiday_type' => 'Public',
                'calendar_type' => 'Religious',
                'english_name' => 'Christmas Day',
                'sinhala_name' => 'නත්තල් දිනය',
                'tamil_name' => 'கிறிஸ்துமஸ் தினம்',
                'is_paid' => true,
                'is_working_day' => false,
                'affects_payroll' => true,
                'affects_attendance' => true,
                'affects_overtime' => true,
                'affects_leave' => true,
                'api_reference' => 'fallback-christmas',
            ]
        ];

        if ($filterType) {
            return array_values(array_filter($fallback, fn($h) => strtolower($h['holiday_type']) === strtolower($filterType)));
        }

        return $fallback;
    }
}
