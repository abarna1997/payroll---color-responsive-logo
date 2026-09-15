<?php

namespace App\Services\Payroll;

class APITService
{
    /**
     * Calculate APIT (Adjusted Personal Income Tax) for Sri Lankan tax compliance.
     */
    public function calculateTax(float $grossSalary): float
    {
        $enableApit = payroll_setting('EnableAPIT', false);
        if (!$enableApit) {
            return 0.00;
        }

        // Standard Sri Lankan Slabs for Monthly Income (as of 2023/2024 legislation)
        // 0 - 100,000: 0%
        // 100,000 - 141,667: 6%
        // 141,667 - 183,333: 12%
        // 183,333 - 225,000: 18%
        // 225,000 - 266,667: 24%
        // 266,667 - 308,333: 30%
        // Above 308,333: 36%

        $taxable = $grossSalary;
        if ($taxable <= 100000) {
            return 0.00;
        }

        $tax = 0.00;
        $remaining = $taxable - 100000;

        // Slab 1: 6% (up to 41,667)
        $slab1 = min($remaining, 41667);
        $tax += $slab1 * 0.06;
        $remaining -= $slab1;

        if ($remaining <= 0) return round($tax, 2);

        // Slab 2: 12% (up to 41,667)
        $slab2 = min($remaining, 41667);
        $tax += $slab2 * 0.12;
        $remaining -= $slab2;

        if ($remaining <= 0) return round($tax, 2);

        // Slab 3: 18% (up to 41,667)
        $slab3 = min($remaining, 41667);
        $tax += $slab3 * 0.18;
        $remaining -= $slab3;

        if ($remaining <= 0) return round($tax, 2);

        // Slab 4: 24% (up to 41,667)
        $slab4 = min($remaining, 41667);
        $tax += $slab4 * 0.24;
        $remaining -= $slab4;

        if ($remaining <= 0) return round($tax, 2);

        // Slab 5: 30% (up to 41,667)
        $slab5 = min($remaining, 41667);
        $tax += $slab5 * 0.30;
        $remaining -= $slab5;

        if ($remaining <= 0) return round($tax, 2);

        // Slab 6: 36% (on excess)
        $tax += $remaining * 0.36;

        return round($tax, 2);
    }
}
