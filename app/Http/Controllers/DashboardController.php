<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\System;
use App\Services\DeviceVerificationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the dynamic application launcher dashboard.
     */
    public function __invoke(Request $request): View
    {
        $user = auth()->user();
        $userId = $user->id;
        $userRole = $user->role ?? 'Employee';

        // Resolve employee names & avatar
        $employee = $user->employee;
        $firstName = $employee?->first_name ?? $user->first_name ?? '';
        $lastName = $employee?->last_name ?? $user->last_name ?? '';

        $fullName = trim("{$firstName} {$lastName}") ?: $user->name;
        $greetingName = $lastName ?: $fullName;

        $userPhoto = $employee?->profile_photo_path 
            ? asset('storage/' . $employee->profile_photo_path) 
            : ($user->profile_photo_path ? asset('storage/' . $user->profile_photo_path) : null);

        // Resolve MMDB GeoIP Location for Weather
        $locDetails = DeviceVerificationService::resolveLocationDetails($request->ip(), $request);
        $weatherLocation = [
            'city'      => (!empty($locDetails['location']) && !in_array($locDetails['location'], ['Local Network / Intranet', 'Unknown Location']))
                             ? $locDetails['location']
                             : 'Mandaluyong City',
            'latitude'  => $locDetails['latitude'] ?? 14.5792,
            'longitude' => $locDetails['longitude'] ?? 121.0359,
        ];

        // Resolve notification header payloads
        $unreadNotifications = $user->unreadNotifications()->take(5)->get();
        $unreadCount = $user->unreadNotifications()->count();

        $notificationsFormatted = $unreadNotifications->map(function ($notif) {
            return [
                'id'               => $notif->id,
                'title'            => $notif->data['title'] ?? 'Notification',
                'message'          => $notif->data['message'] ?? $notif->data['body'] ?? '',
                'read_url'         => Route::has('notifications.readAndRedirect') ? route('notifications.readAndRedirect', $notif->id) : '#',
                'created_at_human' => $notif->created_at?->diffForHumans() ?? 'Just now',
                'is_new'           => true,
            ];
        });

        // Resolve global navigation routes
        $routes = [
            'dashboard'          => Route::has('dashboard') ? route('dashboard') : '/dashboard',
            'systemsIndex'       => Route::has('systems.index') ? route('systems.index') : null,
            'usersIndex'         => Route::has('users.index') ? route('users.index') : null,
            'holidaysIndex'      => Route::has('holidays.index') ? route('holidays.index') : null,
            'markAllRead'        => Route::has('notifications.markAllRead') ? route('notifications.markAllRead') : null,
            'notificationsIndex' => Route::has('notifications.index') ? route('notifications.index') : null,
            'profileEdit'        => Route::has('profile.edit') ? route('profile.edit') : '/profile',
            'logout'             => Route::has('logout') ? route('logout') : '/logout',
        ];

        // Schedule & Event logic
        $today = now()->startOfDay();
        $todayStr = $today->toDateString();

        $nearestDate = Event::where('is_active', true)
            ->whereDate('event_date', '>=', $todayStr)
            ->min('event_date');

        $eventGroup = $nearestDate
            ? Event::where('is_active', true)->whereDate('event_date', $nearestDate)->get()
            : collect();

        $eventCount = $eventGroup->count();
        $combinedTitle = $eventGroup->pluck('title')->join(' & ');
        $eventType = $eventCount > 1 ? 'Multiple Events' : ($eventGroup->first()?->type ?? 'Schedule Notice');

        $eventDate = $nearestDate ? Carbon::parse($nearestDate)->startOfDay() : null;
        $diffInDays = $eventDate ? (int) $today->diffInDays($eventDate) : null;
        $isToday = $diffInDays === 0;

        $distanceLabel = match (true) {
            $diffInDays === 0 => 'Today',
            $diffInDays === 1 => 'Tomorrow',
            $diffInDays > 1 => "In {$diffInDays} days",
            default => null,
        };

        $scheduleData = [
            'nearestDate'          => $nearestDate,
            'nearestDateFormatted' => $nearestDate ? Carbon::parse($nearestDate)->format('F j, Y') : null,
            'combinedTitle'        => $combinedTitle,
            'eventType'            => $eventType,
            'isToday'              => $isToday,
            'distanceLabel'        => $distanceLabel,
            'eventCount'           => $eventCount,
        ];

        // Fetch Systems
        $systems = System::query()
            ->leftJoin('system_user', function ($join) use ($userId) {
                $join->on('systems.id', '=', 'system_user.system_id')
                     ->where('system_user.user_id', '=', $userId);
            })
            ->select([
                'systems.*',
                DB::raw("COALESCE(system_user.role, 'None') AS user_role"),
                DB::raw("COALESCE(system_user.has_access, 0) AS employee_has_access"),
                DB::raw('COALESCE(system_user.custom_order, systems.default_sort_order) AS display_order')
            ])
            ->orderByDesc('systems.is_active')
            ->orderByDesc('employee_has_access')
            ->orderBy('display_order', 'asc')
            ->get();

        $formattedSystems = $systems->map(function ($system) {
            $rawPath = $system->logo_url ?? $system->logo_path ?? $system->logo ?? null;
            $logoUrl = null;

            if (!empty($rawPath)) {
                if (filter_var($rawPath, FILTER_VALIDATE_URL)) {
                    $logoUrl = $rawPath;
                } else {
                    $relativePath = str_replace('/www/wwwroot/lodis.lendell-local.xyz/public/', '', $rawPath);
                    $cleanPath = preg_replace('/^\/?public\//', '', $relativePath);

                    if (!str_contains($cleanPath, '/') && !str_starts_with($cleanPath, 'images/')) {
                        $cleanPath = 'images/systems/' . $cleanPath;
                    }

                    $logoUrl = asset(ltrim($cleanPath, '/'));
                }
            }

            return [
                'id'                  => $system->id,
                'name'                => $system->name,
                'description'         => $system->description,
                'logo_url'            => $logoUrl,
                'url'                 => $system->url,
                'is_active'           => (bool) $system->is_active,
                'is_online'           => $system->is_active ? (bool) ($system->is_online ?? true) : false,
                'employee_has_access' => (bool) $system->employee_has_access,
                'user_role'           => $system->user_role,
                'display_order'       => (int) $system->display_order,
            ];
        });

        return view('dashboard', compact(
            'systems',
            'formattedSystems',
            'userRole',
            'fullName',
            'firstName',
            'greetingName',
            'userPhoto',
            'unreadCount',
            'notificationsFormatted',
            'scheduleData',
            'weatherLocation',
            'routes'
        ));
    }
}