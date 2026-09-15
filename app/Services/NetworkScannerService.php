<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Mithun\PhpZkteco\Libs\ZKTeco;

class NetworkScannerService
{
    /**
     * Automatically scans the local /24 subnet for ZKTeco devices on port 4370
     * and attempts to retrieve their serial numbers.
     *
     * @return array List of discovered devices with IP and Serial Number
     */
    public function autoDiscoverDevices(): array
    {
        $discoveredDevices = [];
        
        $subnetsToScan = [];
        
        // Try to automatically determine the server's local subnet
        if (isset($_SERVER['SERVER_ADDR']) && $_SERVER['SERVER_ADDR'] !== '127.0.0.1') {
            $subnetsToScan[] = substr($_SERVER['SERVER_ADDR'], 0, strrpos($_SERVER['SERVER_ADDR'], '.')) . '.';
        }
        
        $hostIp = gethostbyname(gethostname());
        if ($hostIp && $hostIp !== '127.0.0.1') {
            $subnetsToScan[] = substr($hostIp, 0, strrpos($hostIp, '.')) . '.';
        }
        
        // Limit scanning to only the detected local subnets, ignoring 0.0.0.0
        $subnetsToScan = array_unique(array_filter($subnetsToScan, function($subnet) {
            return $subnet && $subnet !== '0.0.0.';
        }));

        Log::info("Starting automatic ZKTeco device scan on subnets: " . implode(', ', $subnetsToScan));

        foreach ($subnetsToScan as $baseIp) {
            // We will scan IPs 1 through 254
            for ($i = 1; $i <= 254; $i++) {
                $ip = $baseIp . $i;
                
                // Skip the server's own IP
                if ($ip === $hostIp || (isset($_SERVER['SERVER_ADDR']) && $ip === $_SERVER['SERVER_ADDR'])) {
                    continue;
                }

                // Quick socket check to see if port 4370 (TCP) or 80 (HTTP) is open
                // ZKTeco devices usually have one of these open.
                $socket4370 = @fsockopen($ip, 4370, $errno, $errstr, 0.05);
                $isOpen = false;
                
                if ($socket4370) {
                    fclose($socket4370);
                    $isOpen = true;
                } else {
                    $socket80 = @fsockopen($ip, 80, $errno, $errstr, 0.05);
                    if ($socket80) {
                        fclose($socket80);
                        $isOpen = true;
                    }
                }
                
                if ($isOpen) {
                    // Try to pull the Serial Number using the ZKTeco library via UDP
                    try {
                        $zk = new ZKTeco($ip, 4370, timeout: 1, protocol: 'udp');
                        if ($zk->connect()) {
                            $serialNumber = $zk->serialNumber();
                            $deviceName = $zk->deviceName();
                            $zk->disconnect();

                            $discoveredDevices[] = [
                                'ip' => $ip,
                                'serial_number' => $serialNumber,
                                'name' => $deviceName ?? 'ZKTeco Device',
                            ];
                            
                            Log::info("Found ZKTeco device at {$ip} with SN: {$serialNumber}");
                        }
                    } catch (\Exception $e) {
                        Log::warning("Found open port on {$ip} but failed to connect via ZKTeco protocol: " . $e->getMessage());
                    }
                }
            }
        }

        return $discoveredDevices;
    }
}
