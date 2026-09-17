<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\SecuritySetting;
use App\Models\UserDevice;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        $user = $request->user()->load([
            'employee',
            'systems',
            'authenticationLogs' => fn ($query) => $query->latest()->take(15),
            'profileUpdateRequests' => fn ($query) => $query->where('status', 'pending')->latest(),
        ]);
    
        $statusColors = [
            'Regular'      => 'bg-emerald-50 text-emerald-700 border-emerald-200/80',
            'Probationary' => 'bg-amber-50 text-amber-700 border-amber-200/80',
            'Contractual'  => 'bg-blue-50 text-blue-700 border-blue-200/80',
            'Resigned'     => 'bg-slate-100 text-slate-600 border-slate-200/80',
            'Terminated'   => 'bg-rose-50 text-rose-700 border-rose-200/80',
        ];
    
        // Fetch active database SSO sessions
        $sessions = DB::table('sessions')
            ->where('user_id', $user->id)
            ->orderBy('last_activity', 'desc')
            ->get();
    
        return view('profile.edit', [
            'user'                  => $user,
            'employee'              => $user->employee,
            'securitySettings'      => SecuritySetting::instance(),
            'statusColors'          => $statusColors,
            'auditLogs'             => $user->authenticationLogs,
            'pendingProfileRequest' => $user->profileUpdateRequests->first(),
            'sessions'              => $sessions,
        ]);
    }

    /**
     * Update the user's profile information or avatar.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $employee = $user->employee;

        // 1. Handle Instant Profile Avatar Upload
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

        // 2. Handle Profile Information Updates
        $validated = $request->validated();

        if ($employee) {
            $employee->fill([
                'first_name'     => $validated['first_name'] ?? $employee->first_name,
                'middle_name'    => array_key_exists('middle_name', $validated) ? $validated['middle_name'] : $employee->middle_name,
                'last_name'      => $validated['last_name'] ?? $employee->last_name,
                'suffix'         => array_key_exists('suffix', $validated) ? $validated['suffix'] : $employee->suffix,
                'contact_number' => array_key_exists('contact_number', $validated) ? $validated['contact_number'] : $employee->contact_number,
                'gender'         => $validated['gender'] ?? $employee->gender,
                'birthdate'      => $validated['birthdate'] ?? $employee->birthdate,
                'address'        => array_key_exists('address', $validated) ? $validated['address'] : $employee->address,
            ])->save();
        }

        // Synchronize User.name with modified name components
        $firstName = $validated['first_name'] ?? $employee?->first_name ?? '';
        $lastName  = $validated['last_name'] ?? $employee?->last_name ?? '';

        if ($firstName || $lastName) {
            $user->name = trim("{$firstName} {$lastName}");
            $user->save();
        }

        return Redirect::route('profile.edit')->with('status', 'Profile details updated successfully.');
    }

    /**
     * Revoke all active sessions for the current user except the current device.
     */
    public function revokeOtherSessions(Request $request): RedirectResponse
    {
        $currentSessionId = $request->session()->getId();

        DB::table('sessions')
            ->where('user_id', Auth::id())
            ->where('id', '!=', $currentSessionId)
            ->delete();

        return Redirect::route('profile.edit')->with('status', 'Other device sessions have been successfully revoked.');
    }

    /**
     * Revoke a specific session by session ID.
     */
    public function revokeSession(Request $request, string $sessionId): RedirectResponse
    {
        $currentSessionId = $request->session()->getId();

        if ($sessionId === $currentSessionId) {
            return Redirect::back()->withErrors(['session' => 'Cannot revoke your current session here. Use sign out instead.']);
        }

        DB::table('sessions')
            ->where('user_id', Auth::id())
            ->where('id', $sessionId)
            ->delete();

        return Redirect::route('profile.edit')->with('status', 'Device session revoked successfully.');
    }

    /**
     * Delete the user's account.
     */
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
    
    public function revokeDevice(UserDevice $device): RedirectResponse
    {
        if ($device->user_id !== auth()->id()) {
            abort(403);
        }
    
        $device->delete();
    
        return back()->with('status', 'Trusted device removed successfully.');
    }
}