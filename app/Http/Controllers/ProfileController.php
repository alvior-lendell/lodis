<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\ProfileUpdateRequest as ProfileUpdateRequestModel;
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
    
        // 1. Handle Instant Profile Avatar Upload (not subject to HR approval)
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
    
        // 2. Personal info changes require HR approval — never write to employees directly
        if (! $employee) {
            return Redirect::route('profile.edit')->withErrors([
                'profile' => 'No employee record is linked to your account. Contact HR to set this up first.',
            ]);
        }
    
        // Block duplicate submissions while a request is still pending
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
    
        // Only include fields that actually changed, compared to the current employee record
        $payload = [];
        foreach ($fields as $field) {
            if (! array_key_exists($field, $validated)) {
                continue;
            }
    
            $newValue = $validated[$field];
            $currentValue = $field === 'birthdate' && $employee->birthdate
                ? $employee->birthdate->format('Y-m-d')
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
            'employee_id' => $employee->id,
            'payload'     => $payload,
            'status'      => 'pending',
        ]);
    
        return Redirect::route('profile.edit')->with('status', 'Your profile change request has been submitted and is awaiting HR approval.');
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