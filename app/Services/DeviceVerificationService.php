<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserDevice;
use App\Notifications\UnrecognizedDeviceLoginNotification;
use GeoIp2\Database\Reader;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class DeviceVerificationService
{
    public function verifyAndRecordDevice(User $user, Request $request): void
    {
        $deviceKey = $request->cookie('lodis_device_key');
        $ua = $request->userAgent() ?? '';
        $ip = $request->ip();
        $location = self::resolveLocation($ip, $request);

        $parsed = $this->parseUserAgent($ua);
        $isNewDevice = false;

        if ($deviceKey) {
            $device = UserDevice::where('user_id', $user->id)
                ->where('device_key', $deviceKey)
                ->first();

            if ($device) {
                $device->update([
                    'ip_address' => $ip,
                    'location' => $location,
                    'last_active_at' => now(),
                ]);
                return;
            }
        }

        $deviceKey = Str::random(40);
        $isNewDevice = true;

        UserDevice::create([
            'user_id' => $user->id,
            'device_key' => $deviceKey,
            'device_name' => "{$parsed['platform']} — {$parsed['browser']}",
            'platform' => $parsed['platform'],
            'browser' => $parsed['browser'],
            'ip_address' => $ip,
            'location' => $location,
            'is_trusted' => true,
            'last_active_at' => now(),
        ]);

        Cookie::queue(Cookie::make('lodis_device_key', $deviceKey, 60 * 24 * 365 * 5, null, null, false, false));

        if ($isNewDevice) {
            $user->notify(new UnrecognizedDeviceLoginNotification([
                'platform' => $parsed['platform'],
                'browser' => $parsed['browser'],
                'ip_address' => $ip,
                'location' => $location,
            ]));
        }
    }

    public function parseUserAgent(string $ua): array
    {
        $platform = 'Unknown OS';
        $browser = 'Unknown Browser';

        if (preg_match('/Windows/i', $ua)) $platform = 'Windows PC';
        elseif (preg_match('/Macintosh|Mac OS X/i', $ua)) $platform = 'macOS';
        elseif (preg_match('/Linux/i', $ua)) $platform = 'Linux';
        elseif (preg_match('/Android/i', $ua)) $platform = 'Android';
        elseif (preg_match('/iPhone|iPad|iPod/i', $ua)) $platform = 'iOS';

        if (preg_match('/Edg/i', $ua)) $browser = 'Microsoft Edge';
        elseif (preg_match('/Chrome/i', $ua)) $browser = 'Google Chrome';
        elseif (preg_match('/Firefox/i', $ua)) $browser = 'Mozilla Firefox';
        elseif (preg_match('/Safari/i', $ua)) $browser = 'Apple Safari';

        return ['platform' => $platform, 'browser' => $browser];
    }

    /**
     * Resolve IP address to location string and coordinates array.
     */
    public static function resolveLocationDetails(?string $ip, ?Request $request = null): array
    {
        if (!$request && function_exists('app') && app()->bound('request')) {
            $request = request();
        }

        if ($request && ($cfIp = $request->header('cf-connecting-ip') ?? $request->header('x-forwarded-for'))) {
            $ip = trim(explode(',', $cfIp)[0]);
        }

        $default = [
            'location' => 'Unknown Location',
            'latitude' => null,
            'longitude' => null,
        ];

        if (empty($ip) || $ip === '127.0.0.1' || str_starts_with($ip, '192.168.') || str_starts_with($ip, '10.') || str_starts_with($ip, '172.')) {
            $default['location'] = 'Local Network / Intranet';
            return $default;
        }

        $mmdbPath = storage_path('app/geoip/GeoLite2-City.mmdb');

        if (file_exists($mmdbPath)) {
            try {
                $reader = new \GeoIp2\Database\Reader($mmdbPath);
                $record = $reader->city($ip);

                $city = $record->city->name;
                $country = $record->country->name;
                $location = implode(', ', array_filter([$city, $country]));

                return [
                    'location'  => !empty($location) ? $location : 'Unknown Location',
                    'latitude'  => $record->location->latitude,
                    'longitude' => $record->location->longitude,
                ];
            } catch (\Throwable $e) {
                // Fallback to Cloudflare on lookup failure
            }
        }

        $cfCountryCode = $request ? $request->header('cf-ipcountry') : null;
        if (!empty($cfCountryCode) && $cfCountryCode !== 'XX') {
            $default['location'] = "Country: {$cfCountryCode}";
        }

        return $default;
    }
}