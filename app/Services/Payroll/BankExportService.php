<?php

namespace App\Services\Payroll;

use Illuminate\Support\Collection;

class BankExportService
{
    /**
     * Export a list of payslips as a structured string for bank transfer.
     */
    public function export(Collection $payslips, string $bankCode): string
    {
        $output = "";

        switch (strtoupper($bankCode)) {
            case 'BOC':
                $output = $this->formatBOC($payslips);
                break;
            case 'COMMERCIAL':
            case 'COMBANK':
                $output = $this->formatCommercialBank($payslips);
                break;
            case 'HNB':
                $output = $this->formatHNB($payslips);
                break;
            case 'SAMPATH':
                $output = $this->formatSampath($payslips);
                break;
            default:
                $output = $this->formatGenericCSV($payslips);
                break;
        }

        return $output;
    }

    private function formatBOC(Collection $payslips): string
    {
        // Bank of Ceylon standard formatting
        $lines = [];
        $lines[] = "BOC DIRECT DEBIT REGISTER - GENERATED " . now()->toDateString();
        $lines[] = "Account Number,Employee Name,Amount,Bank Code,Branch Code";
        
        foreach ($payslips as $slip) {
            $emp = $slip->employee;
            if ($emp) {
                $acc = $slip->bank_account ?? $emp->bank_account ?? '0000000000';
                $name = $emp->full_name;
                $amount = number_format((float)$slip->net_salary, 2, '.', '');
                $lines[] = "{$acc},\"{$name}\",{$amount},7010,001";
            }
        }

        return implode("\n", $lines);
    }

    private function formatCommercialBank(Collection $payslips): string
    {
        // Commercial Bank of Ceylon formats
        $lines = [];
        $lines[] = "COMMERCIAL BANK TRANSFER SUMMARY";
        $lines[] = "Serial,Account,Name,Net Salary,Branch";

        foreach ($payslips as $idx => $slip) {
            $emp = $slip->employee;
            if ($emp) {
                $serial = $idx + 1;
                $acc = $slip->bank_account ?? $emp->bank_account ?? '0000000000';
                $name = $emp->full_name;
                $amount = number_format((float)$slip->net_salary, 2, '.', '');
                $lines[] = "{$serial},{$acc},\"{$name}\",{$amount},7044";
            }
        }

        return implode("\n", $lines);
    }

    private function formatHNB(Collection $payslips): string
    {
        $lines = [];
        $lines[] = "HNB BANK SALARY DISBURSEMENT REGISTER";
        $lines[] = "Beneficiary Account,Beneficiary Name,Net Payout";

        foreach ($payslips as $slip) {
            $emp = $slip->employee;
            if ($emp) {
                $acc = $slip->bank_account ?? $emp->bank_account ?? '0000000000';
                $name = $emp->full_name;
                $amount = number_format((float)$slip->net_salary, 2, '.', '');
                $lines[] = "{$acc},\"{$name}\",{$amount}";
            }
        }

        return implode("\n", $lines);
    }

    private function formatSampath(Collection $payslips): string
    {
        $lines = [];
        $lines[] = "SAMPATH BANK REGISTRATION FILE";
        $lines[] = "Account,Name,Amount";

        foreach ($payslips as $slip) {
            $emp = $slip->employee;
            if ($emp) {
                $acc = $slip->bank_account ?? $emp->bank_account ?? '0000000000';
                $name = $emp->full_name;
                $amount = number_format((float)$slip->net_salary, 2, '.', '');
                $lines[] = "{$acc},\"{$name}\",{$amount}";
            }
        }

        return implode("\n", $lines);
    }

    private function formatGenericCSV(Collection $payslips): string
    {
        $lines = [];
        $lines[] = "Employee ID,Employee Name,Bank Name,Bank Account,Net Salary";

        foreach ($payslips as $slip) {
            $emp = $slip->employee;
            if ($emp) {
                $empId = $emp->employee_id;
                $name = $emp->full_name;
                $bank = $emp->bank_name ?? 'N/A';
                $acc = $slip->bank_account ?? $emp->bank_account ?? 'N/A';
                $amount = number_format((float)$slip->net_salary, 2, '.', '');
                $lines[] = "{$empId},\"{$name}\",\"{$bank}\",{$acc},{$amount}";
            }
        }

        return implode("\n", $lines);
    }
}
