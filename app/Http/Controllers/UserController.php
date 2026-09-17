<?php

namespace App\Http\Controllers;

use App\Models\System;
use App\Models\User;
use Illuminate\Http\Request;
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
    
        return view('users.index', compact('users'));
    }

    /**
     * Show the form for creating a new user account.
     */
    public function create()
    {
        $systems = System::where('is_active', true)
            ->orderBy('default_sort_order', 'asc')
            ->get();

        return view('users.create', compact('systems'));
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(Request $request)
    {
        // Restrict role assignment options based on creator role
        $allowedRoles = auth()->user()->role === 'Superadmin' 
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

        $user = User::create([
            'employee_id'         => $validated['employee_id'],
            'name'                => $validated['name'],
            'email'               => $validated['email'],
            'password'            => Hash::make($validated['password']),
            'role'                => $validated['role'],
            'is_active'           => $request->has('is_active'),
            'password_changed_at' => now(),
            'email_verified_at'   => now(),
        ]);

        // Seed initial password into password_histories table
        $user->recordPasswordHistory();

        // Sync system access grants
        if ($request->has('systems') && is_array($request->input('systems'))) {
            $syncData = [];
            foreach ($request->input('systems') as $systemId => $pivot) {
                $hasAccess = isset($pivot['has_access']) && $pivot['has_access'] == '1';
                $role = $pivot['role'] ?? 'None';

                $syncData[$systemId] = [
                    'has_access' => $hasAccess ? 1 : 0,
                    'role'       => $role,
                ];
            }
            $user->systems()->sync($syncData);
        }

        return redirect()->route('users.index')->with('status', 'User account created successfully with system grants.');
    }

    /**
     * Show the form for editing the specified user account.
     */
    public function edit(User $user)
    {
        // Prevent Admin from accessing Superadmin account edit forms
        if (auth()->user()->role === 'Admin' && $user->role === 'Superadmin') {
            abort(403, 'Unauthorized access to Superadmin configuration.');
        }

        $user->load('systems');

        $systems = System::where('is_active', true)
            ->orderBy('default_sort_order', 'asc')
            ->get();

        return view('users.edit', compact('user', 'systems'));
    }

    /**
     * Update the specified user account in storage.
     */
    public function update(Request $request, User $user)
    {
        // Prevent Admin from modifying Superadmin accounts
        if (auth()->user()->role === 'Admin' && $user->role === 'Superadmin') {
            abort(403, 'Unauthorized action on Superadmin configuration.');
        }

        // Restrict role options based on modifier role
        $allowedRoles = auth()->user()->role === 'Superadmin' 
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

        $updateData = [
            'employee_id' => $validated['employee_id'],
            'name'        => $validated['name'],
            'email'       => $validated['email'],
            'role'        => $validated['role'],
            'is_active'   => $request->has('is_active'),
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
                $hasAccess = isset($pivot['has_access']) && $pivot['has_access'] == '1';
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

        return redirect()->route('users.index')->with('status', 'User account and system grants updated.');
    }

    /**
     * Remove the specified user from storage (Superadmin only).
     */
    public function destroy(User $user)
    {
        if (auth()->user()->role !== 'Superadmin') {
            abort(403, 'Unauthorized action. Only Superadmins can delete user accounts.');
        }

        if (auth()->id() === $user->id) {
            return redirect()->route('users.index')->with('status', 'You cannot delete your own active account.');
        }

        $user->systems()->detach();
        $user->delete();

        return redirect()->route('users.index')->with('status', 'User account removed.');
    }
}