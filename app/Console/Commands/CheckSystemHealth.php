<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use App\Models\System;
use App\Events\SystemHealthUpdated;

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
            $url = $system->url ?? $system->app_url ?? $system->system_url ?? null;
            $isOnline = true;

            if (!empty($url) && filter_var($url, FILTER_VALIDATE_URL)) {
                try {
                    $response = Http::timeout(2)->withoutVerifying()->get($url);
                    $isOnline = $response->successful();
                } catch (\Throwable $e) {
                    $isOnline = false;
                }
            }

            // Mute model events to prevent SystemObserver from firing .system.updated -> window.location.reload()
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

        // Cache summary payload for global footer resolution
        Cache::put('system_health_status', $healthData, 300);
        
        // Broadcast health updates for dynamic DOM manipulation only
        SystemHealthUpdated::dispatch($healthData);

        return Command::SUCCESS;
    }
}