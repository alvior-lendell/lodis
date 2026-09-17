<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $loginInput = trim($this->input('login'));
        $password = $this->input('password');

        $fieldType = filter_var($loginInput, FILTER_VALIDATE_EMAIL) ? 'email' : 'employee_id';

        $credentials = [
            $fieldType => $loginInput,
            'password' => $password,
            'is_active' => true,
        ];

        // Attempt standard authentication
        if (! Auth::attempt($credentials, $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey());

            // 1. Locate user record regardless of active status or input type
            $user = User::where(function ($query) use ($loginInput) {
                $query->where('email', $loginInput)
                      ->orWhere('employee_id', $loginInput);
            })->first();

            if ($user) {
                // 2. Query password_histories table directly to avoid Eloquent relationship name issues
                $historyRecords = DB::table('password_histories')
                    ->where('user_id', $user->id)
                    ->latest('created_at')
                    ->get();

                foreach ($historyRecords as $history) {
                    // Check both 'password' and 'password_hash' column variations
                    $hashedPassword = $history->password ?? $history->password_hash ?? null;

                    if ($hashedPassword && Hash::check($password, $hashedPassword)) {
                        $changedAt = Carbon::parse($history->created_at)
                            ->timezone('Asia/Manila')
                            ->format('F j, Y \a\t g:i A');

                        throw ValidationException::withMessages([
                            'login' => ["Security Notice: You entered an old password that was changed on {$changedAt}. Please sign in using your current password."],
                        ]);
                    }
                }
            }

            // Fallback default message if no match in history
            throw ValidationException::withMessages([
                'login' => trans('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'login' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('login')).'|'.$this->ip());
    }
}