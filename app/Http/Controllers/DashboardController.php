<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\System;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

        // Resolve first and last name directly from employee relation
        $employee = $user->employee;
        $firstName = $employee?->first_name ?? $user->first_name ?? '';
        $lastName = $employee?->last_name ?? $user->last_name ?? '';

        $fullName = trim("{$firstName} {$lastName}") ?: $user->name;
        $greetingName = $lastName ?: $fullName;

        // Grouping logic for nearest upcoming date events
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

        // Authorized systems launcher grid query - strictly checks pivot has_access
        $systems = System::query()
            ->where('systems.is_active', true)
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
            ->orderByDesc('employee_has_access')
            ->orderBy('display_order', 'asc')
            ->get();

        return view('dashboard', compact(
            'systems',
            'userRole',
            'greetingName',
            'nearestDate',
            'combinedTitle',
            'eventType',
            'isToday',
            'distanceLabel'
        ));
    }
}