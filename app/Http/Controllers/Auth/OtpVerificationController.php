<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\OtpVerification;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class OtpVerificationController extends Controller
{
    /**
     * Display OTP verification audit logs for Admins.
     */
    public function index(Request $request)
    {
        if (! in_array(auth()->user()->role, ['Admin', 'Superadmin'])) {
            abort(403, 'Unauthorized access to security audit logs.');
        }

        $query = OtpVerification::query();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('email', 'like', "%{$search}%")
                  ->orWhere('employee_id', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $status = $request->input('status');
            if ($status === 'used') {
                $query->where('is_used', true);
            } elseif ($status === 'expired') {
                $query->where('is_used', false)->where('expires_at', '<=', now());
            } elseif ($status === 'active') {
                $query->where('is_used', false)->where('expires_at', '>', now());
            }
        }

        $logs = $query->latest()->paginate(15)->withQueryString();

        $formattedLogs = collect($logs->items())->map(function ($log) {
            $isExpired = ! $log->is_used && $log->expires_at->isPast();

            return [
                'id'              => $log->id,
                'email'           => $log->email,
                'employee_id'     => $log->employee_id ?? '—',
                'otp_code'        => $log->decrypted_otp, // 👈 Safe accessor with try-catch fallback
                'resend_attempts' => $log->resend_attempts,
                'is_used'         => (bool) $log->is_used,
                'is_expired'      => $isExpired,
                'expires_at'      => $log->expires_at?->format('M d, Y g:i A') ?? '—',
                'created_at'      => $log->created_at?->format('M d, Y g:i A') ?? '—',
                'destroy_url'     => route('users.otp-logs.destroy', $log->id),
            ];
        });

        $filters = [
            'search' => $request->input('search', ''),
            'status' => $request->input('status', ''),
        ];

        $routes = [
            'index'      => route('users.otp-logs'),
            'usersIndex' => route('users.index'),
        ];

        return view('users.otp-logs', compact('logs', 'formattedLogs', 'filters', 'routes'));
    }

    /**
     * Process and verify submitted OTP code.
     */
    public function verify(Request $request)
    {
        $validated = $request->validate([
            'email'       => 'required|email',
            'employee_id' => 'nullable|string',
            'otp'         => 'required|string|size:6',
        ]);

        $otpRecord = OtpVerification::where('email', $validated['email'])
            ->where('is_used', false)
            ->where('expires_at', '>', now())
            ->latest()
            ->first();

        // Compare plain submitted code directly with decrypted $otpRecord->otp
        if (! $otpRecord || $otpRecord->otp !== $validated['otp']) {
            throw ValidationException::withMessages([
                'otp' => ['The provided verification code is invalid or has expired.'],
            ]);
        }

        $otpRecord->update(['is_used' => true]);

        session()->forget(['otp_email', 'otp_employee_id']);

        return redirect()->route('dashboard')->with('status', 'Two-factor authentication verified successfully.');
    }

    /**
     * Resend a new OTP code.
     */
    public function resend(Request $request)
    {
        $validated = $request->validate([
            'email'       => 'required|email',
            'employee_id' => 'nullable|string',
        ]);

        $latestRecord = OtpVerification::where('email', $validated['email'])
            ->where('is_used', false)
            ->latest()
            ->first();

        $currentAttempts = $latestRecord ? $latestRecord->resend_attempts : 0;

        if ($currentAttempts >= 3) {
            return redirect()->back()->withErrors(['otp' => 'Maximum OTP resend limit reached. Please contact support.']);
        }

        $newOtpCode = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        OtpVerification::create([
            'email'           => $validated['email'],
            'employee_id'     => $validated['employee_id'] ?? null,
            'otp'             => $newOtpCode, // 👈 Eloquent automatically encrypts before saving
            'payload'         => ['ip' => $request->ip(), 'user_agent' => $request->userAgent()],
            'resend_attempts' => $currentAttempts + 1,
            'is_used'         => false,
            'expires_at'      => now()->addMinutes(10),
        ]);

        return redirect()->back()->with('status', 'A new verification code has been sent to your email.');
    }

    /**
     * Delete an OTP record.
     */
    public function destroy($id)
    {
        if (! in_array(auth()->user()->role, ['Admin', 'Superadmin'])) {
            abort(403);
        }

        OtpVerification::where('id', $id)->delete();

        return redirect()->back()->with('status', 'OTP log entry deleted successfully.');
    }
}