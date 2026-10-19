<?php

namespace App\Console\Commands;

use App\Events\SystemHealthUpdated;
use App\Models\System;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CheckSystemHealth extends Command
{
    protected $signature = 'health:check-systems';
    protected $description = 'Perform background HTTP pings on active systems, update database records, and broadcast status via Reverb';

    public function handle()
    {
        if (!class_exists(System::class)) {
            return Command::SUCCESS;
        }

        $systems = System::where('is_active', 1)->get();

        if ($systems->isEmpty()) {
            $healthData = [
                'status' => 'operational',
                'label' => 'All Systems Operational',
                'online' => 0,
                'total' => 0,
                'systems' => [],
            ];
            Cache::put('system_health_status', $healthData, 300);
            SystemHealthUpdated::dispatch($healthData);
            return Command::SUCCESS;
        }

        $total = $systems->count();
        $online = 0;
        $systemStatuses = [];

        foreach ($systems as $system) {
            $url = $system->ping_url ?? $system->url ?? $system->app_url ?? $system->system_url ?? null;
            $isOnline = false;

            if (!empty($url) && filter_var($url, FILTER_VALIDATE_URL)) {
                try {
                    // 1. Try HEAD request first on the exact configured URL
                    $response = Http::timeout(3)
                        ->withoutVerifying()
                        ->withHeaders(['User-Agent' => 'SystemHealthChecker/1.0'])
                        ->head($url);

                    // 2. If HEAD returns 405 (Method Not Allowed) or 404, fall back to GET
                    if (in_array($response->status(), [404, 405])) {
                        $response = Http::timeout(3)
                            ->withoutVerifying()
                            ->withHeaders(['User-Agent' => 'SystemHealthChecker/1.0'])
                            ->get($url);
                    }

                    $isOnline = $response->successful();

                    if (!$isOnline) {
                        $this->warn("System ID {$system->id} ({$url}) failed with status code: {$response->status()}");
                    }
                } catch (\Throwable $e) {
                    $isOnline = false;
                    $this->error("System ID {$system->id} ({$url}) connection exception: {$e->getMessage()}");
                    Log::warning("System health ping failed for {$url}: " . $e->getMessage());
                }
            } else {
                $this->error("System ID {$system->id} has an invalid or empty URL: '{$url}'");
            }

            // Mute model events to prevent observer feedback loops
            System::withoutEvents(function () use ($system, $isOnline) {
                $system->update([
                    'is_online' => $isOnline,
                    'last_ping_at' => now(),
                ]);
            });

            if ($isOnline) {
                $online++;
                $systemStatuses[$system->id] = 'operational';
            } else {
                $systemStatuses[$system->id] = 'outage';
            }
        }

        if ($online === $total) {
            $status = 'operational';
            $label = 'All Systems Operational';
        } elseif ($online > 0) {
            $status = 'degraded';
            $label = "Partial Outage ({$online}/{$total} Active)";
        } else {
            $status = 'outage';
            $label = 'Major System Outage';
        }

        $healthData = [
            'status' => $status,
            'label' => $label,
            'online' => $online,
            'total' => $total,
            'systems' => $systemStatuses,
        ];

        Cache::put('system_health_status', $healthData, 300);
        SystemHealthUpdated::dispatch($healthData);

        $this->info("Health check complete: {$online}/{$total} systems online.");

        return Command::SUCCESS;
    }
}