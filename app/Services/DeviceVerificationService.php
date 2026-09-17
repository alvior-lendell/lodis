<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserDevice;
use App\Notifications\UnrecognizedDeviceLoginNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;

class DeviceVerificationService
{
    public function verifyAndRecordDevice(User $user, Request $request): void
    {
        $deviceKey = $request->cookie('lodis_device_key');
        $ua = $request->userAgent() ?? '';
        $ip = $request->ip();

        $parsed = $this->parseUserAgent($ua);
        $isNewDevice = false;

        if ($deviceKey) {
            $device = UserDevice::where('user_id', $user->id)
                ->where('device_key', $deviceKey)
                ->first();

            if ($device) {
                $device->update([
                    'ip_address' => $ip,
                    'last_active_at' => now(),
                ]);
                return;
            }
        }

        // Generate a new persistent device key if unknown
        $deviceKey = Str::random(40);
        $isNewDevice = true;

        UserDevice::create([
            'user_id' => $user->id,
            'device_key' => $deviceKey,
            'device_name' => "{$parsed['platform']} — {$parsed['browser']}",
            'platform' => $parsed['platform'],
            'browser' => $parsed['browser'],
            'ip_address' => $ip,
            'is_trusted' => true,
            'last_active_at' => now(),
        ]);

        // Attach 5-year persistent cookie to client browser
        Cookie::queue(Cookie::make('lodis_device_key', $deviceKey, 60 * 24 * 365 * 5, null, null, false, false));

        if ($isNewDevice) {
            $user->notify(new UnrecognizedDeviceLoginNotification([
                'platform' => $parsed['platform'],
                'browser' => $parsed['browser'],
                'ip_address' => $ip,
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
}