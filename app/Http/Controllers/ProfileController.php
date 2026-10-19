<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\ProfileUpdateRequest as ProfileUpdateRequestModel;
use App\Models\SecuritySetting;
use App\Models\UserDevice;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        $user = $request->user()->load([
            'employee',
            'systems',
            'authenticationLogs' => fn ($query) => $query->latest()->take(15),
            'profileUpdateRequests' => fn ($query) => $query->where('status', 'pending')->latest(),
        ]);

        $employee = $user->employee;

        // 1. Format Employee Data
        $employeeData = $employee ? [
            'id'                  => $employee->id,
            'full_name'           => trim("{$employee->first_name} {$employee->last_name}"),
            'first_name'          => $employee->first_name,
            'middle_name'         => $employee->middle_name,
            'last_name'           => $employee->last_name,
            'suffix'              => $employee->suffix,
            'email'               => $employee->email ?? $user->email,
            'position'            => $employee->position ?? 'Staff Member',
            'department'          => $employee->department ?? 'General Operations',
            'employment_status'   => $employee->employment_status ?? 'Probationary',
            'contact_number'      => $employee->contact_number,
            'gender'              => $employee->gender,
            'birthdate_formatted' => $employee->birthdate ? Carbon::parse($employee->birthdate)->format('Y-m-d') : null,
            'address'             => $employee->address,
            'avatar_url'          => $employee->profile_photo_path 
                ? asset('storage/' . $employee->profile_photo_path) 
                : ($user->profile_photo_path ? asset('storage/' . $user->profile_photo_path) : null),
        ] : null;

        // 2. Format Pending Request Data
        $pendingRequest = $user->profileUpdateRequests->first();
        $pendingRequestData = $pendingRequest ? [
            'id'                   => $pendingRequest->id,
            'created_at_formatted' => $pendingRequest->created_at?->format('F j, Y g:i A'),
            'payload'              => $pendingRequest->payload,
        ] : null;

        // 3. Status Colors
        $statusColors = [
            'Regular'      => 'bg-emerald-50 text-emerald-700 border-emerald-200/80',
            'Probationary' => 'bg-amber-50 text-amber-700 border-amber-200/80',
            'Contractual'  => 'bg-blue-50 text-blue-700 border-blue-200/80',
            'Resigned'     => 'bg-slate-100 text-slate-600 border-slate-200/80',
            'Terminated'   => 'bg-rose-50 text-rose-700 border-rose-200/80',
        ];

        // 4. Format Trusted Devices (appending #sessions anchor to URL)
        $rawDevices = UserDevice::where('user_id', $user->id)->get();
        $trustedDevicesFormatted = $rawDevices->map(function ($device) {
            return [
                'id'                => $device->id,
                'device_name'       => $device->device_name ?? 'Recognized Device',
                'device_key'        => $device->device_key,
                'ip_address'        => $device->ip_address,
                'last_active_human' => $device->updated_at?->diffForHumans(),
                'revoke_url'        => Route::has('profile.devices.revoke') 
                    ? route('profile.devices.revoke', $device->id) . '#sessions' 
                    : '#sessions',
            ];
        });

        // 5. Format Subsystem Entitlements
        $userSystemsFormatted = $user->systems->map(function ($system) {
            $logo = $system->logo_path ?? $system->logo;

            return [
                'id'           => $system->id,
                'code'         => $system->code ?? $system->name,
                'logo_url'     => $logo ? asset('images/systems/' . ltrim($logo, '/')) : null,
                'name'         => $system->name,
                'has_access'   => (bool) ($system->pivot->has_access ?? false),
                'custom_order' => $system->pivot->custom_order ?? null,
                'role'         => $system->pivot->role ?? 'None',
            ];
        });

        // 6. Format Audit Logs
        $auditLogsFormatted = $user->authenticationLogs->map(function ($log) {
            // Determine failure based on the actual 'event' column value
            $isFailed = str_contains(strtolower($log->event), 'fail');

            return [
                'id'                   => $log->id,
                'event_type'           => $log->event, // Send raw event key to frontend
                'eventTitle'           => match ($log->event) {
                    'login_success' => 'Successful Authentication',
                    'login_failed'  => 'Failed Authentication Attempt',
                    'password_change' => 'Password Changed',
                    default         => ucwords(str_replace('_', ' ', $log->event)),
                },
                'status'               => $isFailed ? 'Failed' : 'Success',
                'is_failed'            => $isFailed,
                'ip_address'           => $log->ip_address ?? 'Unknown',
                'created_at_formatted' => $log->created_at?->format('M j, Y g:i A'),
                'created_at_human'     => $log->created_at?->diffForHumans(),
            ];
        });

        // 7. Format Password Change History
        $passwordHistories = $user->passwordHistories()
            ->latest()
            ->get()
            ->map(function ($history) {
                return [
                    'id'                   => $history->id,
                    'headline_event'       => 'Password Rotation',
                    'ip_address'           => $history->ip_address ?? request()->ip(),
                    'created_at_formatted' => $history->created_at?->format('M d, Y h:i A') ?? '',
                    'created_at_human'     => $history->created_at?->diffForHumans() ?? '',
                ];
            });

        // 8. Format Route Endpoints
        $routes = [
            'updateProfile'      => route('profile.update'),
            'updatePassword'     => Route::has('password.update') ? route('password.update') : '#',
            'revokeOtherDevices' => Route::has('profile.devices.revokeOthers') ? route('profile.devices.revokeOthers') : '#',
            'bulkRevokeDevices'  => Route::has('profile.devices.bulkRevoke') ? route('profile.devices.bulkRevoke') : '#',
        ];

        return view('profile.edit', [
            'user'                    => $user,
            'employeeData'            => $employeeData,
            'pendingRequestData'      => $pendingRequestData,
            'statusColors'            => $statusColors,
            'securitySettings'        => SecuritySetting::instance(),
            'passwordHistories'       => $passwordHistories,
            'trustedDevicesFormatted' => $trustedDevicesFormatted,
            'userSystemsFormatted'    => $userSystemsFormatted,
            'auditLogsFormatted'      => $auditLogsFormatted,
            'routes'                  => $routes,
        ]);
    }

    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $employee = $user->employee;

        if ($request->hasFile('profile_photo')) {
            $path = $request->file('profile_photo')->store('profile-photos', 'public');
            $target = $employee ?? $user;

            if ($target->profile_photo_path) {
                Storage::disk('public')->delete($target->profile_photo_path);
            }

            $target->profile_photo_path = $path;
            $target->save();

            return Redirect::route('profile.edit')->with('status', 'Profile photo updated successfully.');
        }

        if (! $employee) {
            return Redirect::route('profile.edit')->withErrors([
                'profile' => 'No employee record is linked to your account. Contact HR to set this up first.',
            ]);
        }

        $existingPending = $user->profileUpdateRequests()->where('status', 'pending')->first();

        if ($existingPending) {
            return Redirect::route('profile.edit')->withErrors([
                'profile' => 'You already have a profile change request awaiting HR approval. Please wait for it to be reviewed before submitting another.',
            ]);
        }

        $validated = $request->validated();

        $fields = [
            'first_name',
            'middle_name',
            'last_name',
            'suffix',
            'contact_number',
            'gender',
            'birthdate',
            'address',
        ];

        $payload = [];
        foreach ($fields as $field) {
            if (! array_key_exists($field, $validated)) {
                continue;
            }

            $newValue = $validated[$field];
            $currentValue = $field === 'birthdate' && $employee->birthdate
                ? Carbon::parse($employee->birthdate)->format('Y-m-d')
                : $employee->{$field};

            if ((string) $newValue !== (string) $currentValue) {
                $payload[$field] = $newValue;
            }
        }

        if (empty($payload)) {
            return Redirect::route('profile.edit')->with('status', 'No changes detected — nothing was submitted.');
        }

        ProfileUpdateRequestModel::create([
            'user_id'     => $user->id,
            'employee_id' => $employee->employee_id,
            'payload'     => $payload,
            'status'      => 'pending',
        ]);

        return Redirect::route('profile.edit')->with('status', 'Your profile change request has been submitted and is awaiting HR approval.');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    /**
     * Revoke a single trusted device registration.
     */
    public function revokeDevice(UserDevice $device): RedirectResponse
    {
        if ($device->user_id !== auth()->id()) {
            abort(403);
        }

        $device->delete();

        return redirect()->to(route('profile.edit') . '#sessions')
            ->with('status', 'Trusted device removed successfully.');
    }

    /**
     * Revoke all trusted devices except the current active device cookie.
     */
    public function revokeOtherDevices(Request $request): RedirectResponse
    {
        $currentDeviceKey = $request->cookie('lodis_device_key');

        UserDevice::where('user_id', auth()->id())
            ->when($currentDeviceKey, fn ($query) => $query->where('device_key', '!=', $currentDeviceKey))
            ->delete();

        return redirect()->to(route('profile.edit') . '#sessions')
            ->with('status', 'All other trusted devices removed successfully.');
    }

    /**
     * Bulk revoke selected trusted devices.
     */
    public function bulkRevokeDevices(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'device_ids'   => ['required', 'array'],
            'device_ids.*' => ['integer', 'exists:user_devices,id'],
        ]);

        UserDevice::where('user_id', auth()->id())
            ->whereIn('id', $validated['device_ids'])
            ->delete();

        return redirect()->to(route('profile.edit') . '#sessions')
            ->with('status', 'Selected trusted devices removed successfully.');
    }
}