<?php

namespace App\Http\Controllers;

use App\Events\UserUpdated;
use App\Models\Employee;
use App\Models\System;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    /**
     * Display a listing of registered users.
     */
    public function index(Request $request)
    {
        $query = User::query();

        // Hide Superadmin accounts from non-Superadmin users
        if (! auth()->user()->isSuperadmin()) {
            $query->where('role', '!=', 'Superadmin');
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('employee_id', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role')) {
            // Prevent non-Superadmins from querying Superadmin roles directly via URL parameter
            if ($request->role === 'Superadmin' && ! auth()->user()->isSuperadmin()) {
                abort(403);
            }
            $query->where('role', $request->role);
        }

        $users = $query->latest()->paginate(15)->withQueryString();

        // Format data expected by resources/views/users/index.blade.php & Vue component
        $usersFormatted = collect($users->items())->map(function ($user) {
            return [
                'id'                            => $user->id,
                'employee_id'                   => $user->employee_id,
                'name'                          => $user->name,
                'email'                         => $user->email,
                'role'                          => $user->role,
                'is_active'                     => (bool) $user->is_active,
                'created_at'                    => $user->created_at?->toIso8601String(),
                'password_changed_at_formatted' => $user->password_changed_at 
                    ? $user->password_changed_at->format('M d, Y g:i A') 
                    : 'Never',
                'edit_url'                      => route('users.edit', $user),
                'destroy_url'                   => route('users.destroy', $user),
            ];
        });

        $filters = [
            'search' => $request->input('search', ''),
            'role'   => $request->input('role', ''),
        ];

        $routes = [
            'index'        => route('users.index'),
            'create'       => route('users.create'),
            'store'        => route('users.store'),
            'systemAccess' => route('users.system-access'),
            'otpLogs'      => route('users.otp-logs'),
        ];

        return view('users.index', compact('users', 'usersFormatted', 'filters', 'routes'));
    }

    /**
     * Show the form for creating a new user account.
     */
    public function create()
    {
        // Get all employee IDs already associated with a user account
        $existingUserEmployeeIds = User::whereNotNull('employee_id')
            ->pluck('employee_id')
            ->filter()
            ->toArray();

        // Fetch active, eligible employees sorted by employee_id
        $employeesFormatted = Employee::query()
            ->whereNotIn('employee_id', $existingUserEmployeeIds)
            ->whereNotIn('employment_status', ['Resigned', 'Terminated', 'Exempt'])
            ->orderBy('employee_id', 'asc')
            ->get()
            ->map(function ($emp) {
                return [
                    'id'                => $emp->id,
                    'employee_id'       => $emp->employee_id,
                    'full_name'         => trim("{$emp->first_name} {$emp->last_name}"),
                    'first_name'        => $emp->first_name,
                    'last_name'         => $emp->last_name,
                    'email'             => $emp->email,
                    'employment_status' => $emp->employment_status,
                    'department'        => $emp->department ?? 'General Operations',
                    'position'          => $emp->position ?? 'Staff',
                ];
            });

        // Format active systems
        $systemsFormatted = System::where('is_active', true)
            ->orderBy('default_sort_order', 'asc')
            ->get()
            ->map(function ($system) {
                return [
                    'id'   => $system->id,
                    'code' => $system->code ?? $system->name,
                    'name' => $system->name,
                ];
            });

        $routes = [
            'index'  => route('users.index'),
            'cancel' => route('users.index'),
            'store'  => route('users.store'),
        ];

        $oldValues = session()->getOldInput();

        return view('users.create', compact('employeesFormatted', 'oldValues', 'systemsFormatted', 'routes'));
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(Request $request)
    {
        // Restrict role assignment options based on creator role
        $allowedRoles = auth()->user()->isSuperadmin()
            ? ['Superadmin', 'Admin', 'Employee'] 
            : ['Admin', 'Employee'];

        $validated = $request->validate([
            'employee_id'          => 'nullable|string|max:255|unique:users,employee_id',
            'name'                 => 'required|string|max:255',
            'email'                => 'required|string|email|max:255|unique:users,email',
            'password'             => 'required|string|min:8|confirmed',
            'role'                 => ['required', Rule::in($allowedRoles)],
            'is_active'            => 'nullable|boolean',
            'systems'              => 'nullable|array',
            'systems.*.has_access' => 'nullable|boolean',
            'systems.*.role'       => ['nullable', Rule::in(['Superadmin', 'Admin', 'Employee', 'None'])],
        ]);

        $createdUser = null;

        DB::transaction(function () use ($request, $validated, &$createdUser) {
            $createdUser = User::create([
                'employee_id'         => $validated['employee_id'] ?? null,
                'name'                => $validated['name'],
                'email'               => $validated['email'],
                'password'            => Hash::make($validated['password']),
                'role'                => $validated['role'],
                'is_active'           => $request->has('is_active') ? $request->boolean('is_active') : true,
                'password_changed_at' => now(),
                'email_verified_at'   => now(),
            ]);

            // Seed initial password into password_histories table
            $createdUser->recordPasswordHistory();

            // Sync system access grants
            if ($request->has('systems') && is_array($request->input('systems'))) {
                $syncData = [];
                foreach ($request->input('systems') as $systemId => $pivot) {
                    $hasAccess = filter_var($pivot['has_access'] ?? false, FILTER_VALIDATE_BOOLEAN);
                    $role = $pivot['role'] ?? 'None';

                    $syncData[$systemId] = [
                        'has_access' => $hasAccess ? 1 : 0,
                        'role'       => $role,
                    ];
                }
                $createdUser->systems()->sync($syncData);
            }
        });

        // Broadcast Reverb Event after DB transaction commits
        if ($createdUser) {
            UserUpdated::dispatch($createdUser->id, 'created');
        }

        return redirect()->route('users.index')->with('status', 'User account created successfully with system grants.');
    }

    /**
     * Show the form for editing the specified user account.
     */
    public function edit(User $user)
    {
        // Prevent non-Superadmins from accessing Superadmin account edit forms
        if ($user->role === 'Superadmin' && ! auth()->user()->isSuperadmin()) {
            abort(403, 'Unauthorized access to Superadmin configuration.');
        }

        $user->load('systems');

        // Format user data with mapped system access pivot properties
        $userFormatted = [
            'id'          => $user->id,
            'employee_id' => $user->employee_id,
            'name'        => $user->name,
            'email'       => $user->email,
            'role'        => $user->role,
            'is_active'   => (bool) $user->is_active,
            'systems'     => $user->systems->mapWithKeys(function ($system) {
                return [
                    $system->id => [
                        'has_access' => (bool) ($system->pivot->has_access ?? false),
                        'role'       => $system->pivot->role ?? 'None',
                    ],
                ];
            }),
        ];

        // Format active systems collection
        $systemsFormatted = System::where('is_active', true)
            ->orderBy('default_sort_order', 'asc')
            ->get()
            ->map(function ($system) {
                return [
                    'id'   => $system->id,
                    'code' => $system->code ?? $system->name,
                    'name' => $system->name,
                ];
            });

        $routes = [
            'index'  => route('users.index'),
            'update' => route('users.update', $user),
        ];

        return view('users.edit', compact('userFormatted', 'systemsFormatted', 'routes'));
    }

    /**
     * Update the specified user account in storage.
     */
    public function update(Request $request, User $user)
    {
        // Prevent non-Superadmins from modifying Superadmin accounts
        if ($user->role === 'Superadmin' && ! auth()->user()->isSuperadmin()) {
            abort(403, 'Unauthorized action on Superadmin configuration.');
        }

        // Restrict role options based on modifier role
        $allowedRoles = auth()->user()->isSuperadmin()
            ? ['Superadmin', 'Admin', 'Employee'] 
            : ['Admin', 'Employee'];

        $validated = $request->validate([
            'employee_id'          => ['nullable', 'string', 'max:255', Rule::unique('users', 'employee_id')->ignore($user->id)],
            'name'                 => 'required|string|max:255',
            'email'                => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password'             => 'nullable|string|min:8|confirmed',
            'role'                 => ['required', Rule::in($allowedRoles)],
            'is_active'            => 'nullable|boolean',
            'systems'              => 'nullable|array',
            'systems.*.has_access' => 'nullable|boolean',
            'systems.*.role'       => ['nullable', Rule::in(['Superadmin', 'Admin', 'Employee', 'None'])],
        ]);

        DB::transaction(function () use ($request, $user, $validated) {
            $updateData = [
                'employee_id' => $validated['employee_id'] ?? null,
                'name'        => $validated['name'],
                'email'       => $validated['email'],
                'role'        => $validated['role'],
                'is_active'   => $request->boolean('is_active'),
            ];

            // Handle password updates with historical checks and audit logging
            if ($request->filled('password')) {
                $newPassword = $request->input('password');

                if ($user->isPasswordReused($newPassword)) {
                    throw ValidationException::withMessages([
                        'password' => ['The new password cannot be one of the recent passwords used by this account.'],
                    ]);
                }

                // Archive the existing active password hash before replacing
                $user->recordPasswordHistory();

                $updateData['password'] = Hash::make($newPassword);
                $updateData['password_changed_at'] = now();
            }

            $user->update($updateData);

            // Sync system access grants
            if ($request->has('systems') && is_array($request->input('systems'))) {
                $syncData = [];
                foreach ($request->input('systems') as $systemId => $pivot) {
                    $hasAccess = filter_var($pivot['has_access'] ?? false, FILTER_VALIDATE_BOOLEAN);
                    $role = $pivot['role'] ?? 'None';

                    $syncData[$systemId] = [
                        'has_access' => $hasAccess ? 1 : 0,
                        'role'       => $role,
                    ];
                }
                $user->systems()->sync($syncData);
            } else {
                $user->systems()->detach();
            }
        });

        // Broadcast Reverb Event after DB transaction commits
        UserUpdated::dispatch($user->id, 'updated');

        return redirect()->route('users.index')->with('status', 'User account and system grants updated.');
    }

    /**
     * Remove the specified user from storage (Superadmin only).
     */
    public function destroy(User $user)
    {
        if (! auth()->user()->isSuperadmin()) {
            abort(403, 'Unauthorized action. Only Superadmins can delete user accounts.');
        }

        if (auth()->id() === $user->id) {
            return redirect()->route('users.index')->with('status', 'You cannot delete your own active account.');
        }

        $userId = $user->id;

        DB::transaction(function () use ($user) {
            $user->systems()->detach();
            $user->delete();
        });

        // Broadcast Reverb Event after DB transaction commits
        UserUpdated::dispatch($userId, 'deleted');

        return redirect()->route('users.index')->with('status', 'User account removed.');
    }

    /**
     * Display the User System Access privileges page.
     */
    public function systemAccess()
    {
        $users = User::with('systems')->orderBy('name', 'asc')->get()->map(function ($user) {
            return [
                'id'          => $user->id,
                'name'        => $user->name,
                'email'       => $user->email,
                'employee_id' => $user->employee_id,
                'role'        => $user->role,
                'systems'     => $user->systems->keyBy('id')->map(function ($sys) {
                    return [
                        'has_access' => (bool) $sys->pivot->has_access,
                        'role'       => $sys->pivot->role,
                    ];
                }),
                'update_access_url' => route('users.system-access.update', $user),
            ];
        });

        // Query available fields without explicitly selecting missing 'code' column
        $systems = System::where('is_active', true)
            ->orderBy('default_sort_order', 'asc')
            ->get()
            ->map(function ($system) {
                return [
                    'id'       => $system->id,
                    'name'     => $system->name,
                    'code'     => $system->slug ?? $system->tag ?? $system->name,
                    'logo_url' => $system->logo_url,
                ];
            });

        $routes = [
            'index' => route('users.index'),
        ];

        return view('users.system-access', compact('users', 'systems', 'routes'));
    }

    /**
     * Update system access grants and subsystem roles for a specific user.
     */
    public function updateSystemAccess(Request $request, User $user)
    {
        $validated = $request->validate([
            'systems'              => 'required|array',
            'systems.*.has_access' => 'nullable|boolean',
            'systems.*.role'       => 'required|string|in:Superadmin,Admin,Employee,None',
        ]);

        $activeSystems = System::pluck('id')->toArray();
        $syncData = [];

        foreach ($activeSystems as $systemId) {
            $hasAccess = isset($validated['systems'][$systemId]['has_access']) 
                ? (bool) $validated['systems'][$systemId]['has_access'] 
                : false;

            $role = $validated['systems'][$systemId]['role'] ?? 'None';

            $syncData[$systemId] = [
                'has_access' => $hasAccess ? 1 : 0,
                'role'       => $role,
            ];
        }

        // Sync entries into system_user table
        $user->systems()->sync($syncData);

        // Broadcast Reverb Event
        UserUpdated::dispatch($user->id, 'access_updated');

        return redirect()->back()->with('status', "System access permissions updated successfully for {$user->name}.");
    }
}