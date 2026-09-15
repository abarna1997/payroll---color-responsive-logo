<?php

namespace App\Services\Contracts;

use App\Models\Employee;

interface DeviceCommunicationInterface
{
    /**
     * Create user profile command for the targeted hardware terminal.
     */
    public function createUser(Employee $employee, int $commandId): string;

    /**
     * Update user profile command for the targeted hardware terminal.
     */
    public function updateUser(Employee $employee, int $commandId): string;

    /**
     * Delete user profile command from the terminal.
     */
    public function deleteUser(string $deviceUserId, int $commandId): string;

    /**
     * Reboot terminal command.
     */
    public function reboot(): string;

    /**
     * Sync time command.
     */
    public function syncTime(?string $dateTime = null): string;

    /**
     * Query device information / logs command.
     */
    public function getInformation(): string;
}
